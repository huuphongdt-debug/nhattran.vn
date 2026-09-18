<?php
/** CLI: endpoint tests on a fresh schema-only database. Never copies live upload paths. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/database.php';
$source = $pdo;
$root = dirname(__DIR__);
$testName = 'nhattran_test_' . bin2hex(random_bytes(8));
$created = false;
$failures = [];
function verifyWorkflow(bool $ok, string $label): void {
    global $failures;
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$ok) $failures[] = $label;
}
try {
    $source->exec("CREATE DATABASE `$testName` CHARACTER SET utf8mb4");
    $created = true;
    $test = new PDO("mysql:host=$host;port=$port;dbname=$testName;charset=utf8mb4", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $test->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($source->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
        $schema = $source->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`')->fetch(PDO::FETCH_NUM)[1];
        $test->exec($schema);
    }
    $test->exec('SET FOREIGN_KEY_CHECKS=1');
    $request = function (string $file, array $post = [], array $get = [], string $role = 'admin') use ($root, $testName): string {
        // Preload config once, then replace PDO before requiring the real endpoint.
        $code = 'require_once ' . var_export($root . '/config/database.php', true) . ';'
            . '$pdo=new PDO("mysql:host=$host;port=$port;dbname=' . $testName . ';charset=utf8mb4",$username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);'
            . 'if($pdo->query("SELECT DATABASE()")->fetchColumn()!==' . var_export($testName, true) . ')exit(90);'
            . 'session_set_save_handler(new class implements SessionHandlerInterface {public function open(string $p,string $n):bool{return true;}public function close():bool{return true;}public function read(string $id):string{return "";}public function write(string $id,string $data):bool{return true;}public function destroy(string $id):bool{return true;}public function gc(int $max):int|false{return 0;}},true);'
            . 'session_start();$_SESSION["admin_user"]=["role"=>' . var_export($role, true) . ',"full_name"=>"Test"];$_SESSION["admin_csrf"]="workflow";'
            . '$_SERVER["REQUEST_METHOD"]="POST";$_POST=' . var_export($post + ['csrf' => 'workflow'], true) . ';$_GET=' . var_export($get, true) . ';'
            . 'register_shutdown_function(function(){echo "STATUS:".(http_response_code()?:200);});require ' . var_export($root . '/' . $file, true) . ';';
        $process = proc_open([PHP_BINARY, '-r', $code], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $html = stream_get_contents($pipes[1]); $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        if (proc_close($process) || $errors) throw new RuntimeException($errors ?: 'Endpoint process failed');
        return $html;
    };
    $fields = ['name' => 'Workflow fixture', 'sku' => 'TEST-ONLY', 'price' => '125000', 'show_price' => '1', 'status' => 'draft'];
    $request('admin/products/product-create.php', $fields);
    $id = (int) $test->query('SELECT id FROM products')->fetchColumn();
    verifyWorkflow($id > 0, 'create draft product');
    if (!$id) throw new RuntimeException('Cannot continue without fixture');
    $request('admin/products/product-create.php', $fields);
    verifyWorkflow((int) $test->query('SELECT COUNT(*) FROM products')->fetchColumn() === 1, 'reject duplicate SKU');
    $request('admin/products/product-edit.php', array_replace($fields, ['status' => 'active', 'price' => '250000']), ['id' => $id], 'manager');
    $row = $test->query("SELECT * FROM products WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
    verifyWorkflow($row['status'] === 'active' && (int) $row['price'] === 250000, 'manager edits price and publishes');
    // Deliberately nonexistent paths ensure delete-image cannot touch real files.
    $prefix = 'uploads/products/' . $testName;
    $stmt = $test->prepare('INSERT INTO product_images(product_id,image,sort_order) VALUES(?,?,?)');
    $stmt->execute([$id, $prefix . '-a.png', 1]); $first = (int) $test->lastInsertId();
    $stmt->execute([$id, $prefix . '-b.png', 2]); $second = (int) $test->lastInsertId();
    $test->prepare('UPDATE products SET image=? WHERE id=?')->execute([$prefix . '-a.png', $id]);
    $request('admin/products/product-edit.php', ['action' => 'set_main_image', 'image_id' => $second], ['id' => $id]);
    verifyWorkflow($test->query("SELECT image FROM products WHERE id=$id")->fetchColumn() === $prefix . '-b.png', 'main image synced to product card');
    $request('admin/products/product-edit.php', ['action' => 'delete_image', 'image_id' => $second], ['id' => $id]);
    verifyWorkflow($test->query("SELECT image FROM products WHERE id=$id")->fetchColumn() === $prefix . '-a.png', 'deleted main image replaced');
    $request('admin/products/product-edit.php', ['action' => 'delete_image', 'image_id' => $first], ['id' => $id]);
    verifyWorkflow($test->query("SELECT image FROM products WHERE id=$id")->fetchColumn() === null, 'last image removal clears product card');
    $request('admin/products/product-delete.php', ['id' => $id]);
    verifyWorkflow((int) $test->query('SELECT COUNT(*) FROM products')->fetchColumn() === 0, 'delete product');
} finally {
    if ($created && preg_match('/^nhattran_test_[a-f0-9]{16}$/', $testName)) $source->exec("DROP DATABASE `$testName`");
}
exit($failures ? 1 : 0);
