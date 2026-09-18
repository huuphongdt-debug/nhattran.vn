<?php
/** CLI-only HTTP multipart checks against a disposable application copy and schema. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../config/database.php';
$source = $pdo;
$name = 'nhattran_http_' . bin2hex(random_bytes(8));
$directory = sys_get_temp_dir() . '/' . $name;
$created = false;
$server = null;
$failures = [];
function uploadCheck(bool $ok, string $label): void {
    global $failures;
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$ok) $failures[] = $label;
}
try {
    mkdir($directory, 0700);
    foreach (['app', 'admin', 'public', 'config'] as $folder) {
        $origin = dirname(__DIR__) . '/' . $folder;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($origin, FilesystemIterator::SKIP_DOTS)) as $file) {
            $target = $directory . '/' . $folder . '/' . substr($file->getPathname(), strlen($origin) + 1);
            if (!is_dir(dirname($target))) mkdir(dirname($target), 0700, true);
            copy($file->getPathname(), $target);
        }
    }
    mkdir($directory . '/uploads', 0700);
    $source->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4");
    $created = true;
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($source->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
        $pdo->exec($source->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`')->fetch(PDO::FETCH_NUM)[1]);
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    // Rewrite only the copied config; the live application's configuration is untouched.
    file_put_contents($directory . '/config/database.php', '<?php $pdo=new PDO(' . var_export("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", true) . ',' . var_export($username, true) . ',' . var_export($password, true) . ',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);');
    $secret = bin2hex(random_bytes(24));
    $router = <<<'PHP'
<?php
if (!hash_equals(__TEST_TOKEN__, $_SERVER['HTTP_X_TEST_SECRET'] ?? $_COOKIE['layout_test'] ?? '')) { http_response_code(403); exit; }
session_start();
$_SESSION['admin_user'] = ['role' => 'admin', 'full_name' => 'HTTP fixture'];
$_SESSION['admin_csrf'] = 'http-fixture';
$_SESSION['projects_csrf'] = 'http-fixture';
$_SESSION['manufacturer_groups_csrf'] = 'http-fixture';
return false;
PHP;
    file_put_contents($directory . '/router.php', str_replace('__TEST_TOKEN__', var_export($secret, true), $router));
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
    $address = stream_socket_get_name($socket, false); fclose($socket);
    $server = proc_open([PHP_BINARY, '-d', 'upload_max_filesize=16M', '-d', 'post_max_size=32M', '-d', 'session.save_path=' . $directory, '-d', 'upload_tmp_dir=' . $directory, '-S', $address, '-t', $directory, $directory . '/router.php'], [0 => ['pipe','r'], 1 => ['file',$directory.'/server.log','a'], 2 => ['file',$directory.'/server.log','a']], $pipes);
    fclose($pipes[0]);
    $request = function (string $path, ?array $fields = null) use ($address, $secret): array {
        $curl = curl_init('http://' . $address . '/' . $path);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_HTTPHEADER => ['X-Test-Secret: ' . $secret]]);
        if ($fields !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $fields + ['csrf' => 'http-fixture']);
        $html = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl); curl_close($curl);
        if ($html === false) throw new RuntimeException($error);
        if (preg_match('/(?:Fatal error|Warning):/', $html)) throw new RuntimeException('PHP error in HTTP response');
        return [$status, $html];
    };
    for ($i=0; $i<30; $i++) {
        $ready = @stream_socket_client('tcp://' . $address, $errno, $errstr, 0.1);
        if ($ready) { fclose($ready); break; }
        usleep(100000);
    }
    $bitmap = imagecreatetruecolor(32,32); imagepng($bitmap, $directory.'/fixture.png'); imagedestroy($bitmap);
    file_put_contents($directory.'/fake.png', 'This is not an image');
    $fields = ['name'=>'HTTP upload fixture','sku'=>'HTTP-ONE','status'=>'active'];
    [$status,$createHtml] = $request('admin/products/product-create.php', $fields + ['images[0]'=>new CURLFile($directory.'/fixture.png','image/png','fixture.png')]);
    $product = $pdo->query('SELECT * FROM products')->fetch(PDO::FETCH_ASSOC);
    uploadCheck($status === 302 && $product && is_file($directory.'/'.$product['image']), 'multipart PNG creates product and physical image');
    if (!$product) { file_put_contents($directory.'/response.html', $createHtml); throw new RuntimeException('Missing product fixture: HTTP '.$status.'; '.$directory); }
    $id = (int)$product['id'];
    [$status,$html] = $request('public/product-detail.php?id='.$id);
    uploadCheck($status===200 && str_contains($html,'HTTP upload fixture'), 'active product visible over HTTP');
    $request('admin/products/product-create.php', ['name'=>'Invalid image','sku'=>'HTTP-BAD','images[0]'=>new CURLFile($directory.'/fake.png','image/png','fake.png')]);
    uploadCheck((int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn()===1, 'fake PNG rejected and product insertion rolled back');
    $request('admin/products/product-edit.php?id='.$id, ['action'=>'upload_images','product_images[0]'=>new CURLFile($directory.'/fixture.png','image/png','second.png')]);
    uploadCheck((int)$pdo->query('SELECT COUNT(*) FROM product_images')->fetchColumn()===2, 'multipart upload adds gallery image');
    $image = $pdo->query('SELECT * FROM product_images ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    $pdo->prepare('INSERT INTO banners(title,image) VALUES (?,?)')->execute(['Gallery shared', '/NHATTRAN/'.$image['image']]);
    $galleryBanner = (int)$pdo->lastInsertId();
    $request('admin/products/product-edit.php?id='.$id, ['action'=>'delete_image','image_id'=>$image['id']]);
    clearstatcache();
    uploadCheck(is_file($directory.'/'.$image['image']), 'gallery deletion preserves image used by banner');
    $request('admin/banners/banners.php', ['delete'=>$galleryBanner]);
    clearstatcache();
    uploadCheck(!is_file($directory.'/'.$image['image']), 'delete image removes physical file');
    $request('admin/products/product-edit.php?id='.$id, ['action'=>'update_product','name'=>'HTTP upload fixture','sku'=>'HTTP-ONE','status'=>'draft']);
    [$status] = $request('public/product-detail.php?id='.$id);
    uploadCheck($status===404, 'draft product hidden over HTTP');
    // Basic CRUD without media for the remaining content forms below.
    foreach ([
        ['services', 'services/services', 'name', 'active', 'inactive'],
        ['posts', 'posts/post', 'title', 'draft', 'published'],
        ['product_categories', 'categories/category', 'name', 'active', 'inactive'],
    ] as [$table, $route, $field, $initial, $updated]) {
        $data = [$field => 'HTTP content fixture', 'slug' => 'http-content-fixture', 'content' => 'Fixture content', 'status' => $initial];
        $request('admin/' . $route . '-create.php', $data);
        $itemId = (int) $pdo->query("SELECT id FROM `$table` LIMIT 1")->fetchColumn();
        uploadCheck($itemId > 0, "$table create via HTTP");
        if (!$itemId) continue;
        $request('admin/' . $route . '-edit.php?id=' . $itemId, array_replace($data, [$field => 'Edited fixture', 'status' => $updated]));
        $item = $pdo->query("SELECT * FROM `$table` WHERE id=$itemId")->fetch(PDO::FETCH_ASSOC);
        uploadCheck($item[$field] === 'Edited fixture' && $item['status'] === $updated, "$table edit via HTTP");
        $request('admin/' . $route . '-delete.php', ['id' => $itemId]);
        uploadCheck((int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() === 0, "$table delete via HTTP");
    }
    $request('admin/manufacturers/manufacturers-create.php', ['name'=>'Fixture brand','groups_csrf'=>'http-fixture']);
    $brandId = (int)$pdo->query('SELECT id FROM manufacturers LIMIT 1')->fetchColumn();
    uploadCheck($brandId>0, 'manufacturer create');
    if ($brandId) {
        $pdo->exec("INSERT INTO manufacturer_groups(name) VALUES ('Fixture group A'), ('Fixture group B')");
        $groupIds = $pdo->query('SELECT id FROM manufacturer_groups ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
        $request('admin/manufacturers/manufacturers-edit.php?id='.$brandId, ['name'=>'Fixture brand','groups_csrf'=>'http-fixture','groups[0]'=>$groupIds[0],'groups[1]'=>$groupIds[1]]);
        uploadCheck((int)$pdo->query("SELECT COUNT(*) FROM manufacturer_group_members WHERE manufacturer_id=$brandId")->fetchColumn()===2, 'manufacturer assigned to two groups');
        [$groupStatus, $groupHtml] = $request('public/index.php');
        uploadCheck($groupStatus===200 && str_contains($groupHtml,'Fixture group A') && str_contains($groupHtml,'Fixture group B'), 'manufacturer groups visible on homepage');
        $request('admin/manufacturers/manufacturers-edit.php?id='.$brandId, ['name'=>'Edited brand','groups_csrf'=>'http-fixture']);
        uploadCheck($pdo->query("SELECT name FROM manufacturers WHERE id=$brandId")->fetchColumn()==='Edited brand', 'manufacturer edit');
        uploadCheck((int)$pdo->query("SELECT COUNT(*) FROM manufacturer_group_members WHERE manufacturer_id=$brandId")->fetchColumn()===0, 'clearing groups removes memberships');
        $pdo->exec("UPDATE products SET manufacturer_id=$brandId WHERE id=$id");
        $request('admin/manufacturers/manufacturers-delete.php', ['id'=>$brandId]);
        uploadCheck((int)$pdo->query("SELECT COUNT(*) FROM manufacturers WHERE id=$brandId")->fetchColumn()===1, 'manufacturer in use cannot be deleted');
        $pdo->exec("UPDATE products SET manufacturer_id=NULL WHERE id=$id");
        $request('admin/manufacturers/manufacturers-delete.php', ['id'=>$brandId]);
        uploadCheck((int)$pdo->query('SELECT COUNT(*) FROM manufacturers')->fetchColumn()===0, 'manufacturer delete');
    }
    $request('admin/projects/projects.php?new=1', ['action'=>'save','name'=>'Fixture project','status'=>'1','image'=>new CURLFile($directory.'/fixture.png','image/png','fixture.png')]);
    $project = $pdo->query('SELECT * FROM projects LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    uploadCheck($project && is_file($directory.'/'.$project['main_image']), 'project create with converted image');
    if ($project) {
        $projectId = (int)$project['id'];
        $oldProjectImage = $project['main_image'];
        $request('admin/projects/projects.php?id='.$projectId, ['action'=>'save','name'=>'Replaced project','image'=>new CURLFile($directory.'/fixture.png','image/png','new.png')]);
        clearstatcache();
        $project = $pdo->query("SELECT * FROM projects WHERE id=$projectId")->fetch(PDO::FETCH_ASSOC);
        uploadCheck($project['main_image']!==$oldProjectImage && !is_file($directory.'/'.$oldProjectImage) && is_file($directory.'/'.$project['main_image']), 'project replacement cleans old image');
        $request('admin/projects/projects.php?id='.$projectId, ['action'=>'save','name'=>'Edited project','status'=>'0']);
        uploadCheck($pdo->query("SELECT name FROM projects WHERE id=$projectId")->fetchColumn()==='Edited project', 'project edit');
        $request('admin/projects/projects.php', ['action'=>'delete','id'=>$projectId]);
        uploadCheck((int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn()===0, 'project delete');
    }
    $request('admin/banners/banners.php', ['title'=>'Fixture banner','image'=>new CURLFile($directory.'/fixture.png','image/png','fixture.png')]);
    $banner = $pdo->query('SELECT * FROM banners LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    uploadCheck((bool)$banner, 'banner create with image');
    if ($banner) {
        $bannerId = (int)$banner['id'];
        $oldBannerImage = $banner['image'];
        $request('admin/banners/banner-edit.php?id='.$bannerId, ['title'=>'Replaced banner','image'=>new CURLFile($directory.'/fixture.png','image/png','new.png')]);
        clearstatcache();
        $banner = $pdo->query("SELECT * FROM banners WHERE id=$bannerId")->fetch(PDO::FETCH_ASSOC);
        uploadCheck($banner['image']!==$oldBannerImage && !is_file($directory.'/'.preg_replace('~^/NHATTRAN/~','',$oldBannerImage)), 'banner replacement cleans old image');
        $request('admin/banners/banner-edit.php?id='.$bannerId, ['title'=>'Edited banner','status'=>'active']);
        uploadCheck($pdo->query("SELECT title FROM banners WHERE id=$bannerId")->fetchColumn()==='Edited banner', 'banner edit');
        $before = $pdo->query("SELECT status FROM banners WHERE id=$bannerId")->fetchColumn();
        $request('admin/banners/banners.php', ['toggle'=>$bannerId]);
        uploadCheck($pdo->query("SELECT status FROM banners WHERE id=$bannerId")->fetchColumn()!==$before, 'banner toggle');
        $request('admin/banners/banners.php', ['delete'=>$bannerId]);
        uploadCheck((int)$pdo->query('SELECT COUNT(*) FROM banners')->fetchColumn()===0, 'banner delete');
    }
    file_put_contents($directory.'/large.png', file_get_contents($directory.'/fixture.png') . str_repeat('0', 5*1024*1024));
    $beforeCount = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $request('admin/products/product-create.php', ['name'=>'Oversized fixture','sku'=>'HTTP-LARGE','images[0]'=>new CURLFile($directory.'/large.png','image/png','large.png')]);
    uploadCheck((int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn()===$beforeCount, 'create rejects image over 5 MB');
    $beforeImages = (int)$pdo->query('SELECT COUNT(*) FROM product_images')->fetchColumn();
    $request('admin/products/product-edit.php?id='.$id, ['action'=>'upload_images','product_images[0]'=>new CURLFile($directory.'/large.png','image/png','large.png')]);
    uploadCheck((int)$pdo->query('SELECT COUNT(*) FROM product_images')->fetchColumn()===$beforeImages, 'edit rejects image over 5 MB');
    // A second record keeps the same physical product image alive.
    $pdo->prepare('INSERT INTO banners(title,image) VALUES (?,?)')->execute(['Shared fixture', '/NHATTRAN/'.$product['image']]);
    $sharedBannerId = (int)$pdo->lastInsertId();
    $request('admin/products/product-delete.php', ['id'=>$id]);
    uploadCheck(is_file($directory.'/'.$product['image']), 'shared image survives product deletion');
    $request('admin/banners/banners.php', ['delete'=>$sharedBannerId]);
    clearstatcache(); // HTTP child process changed files checked earlier by this CLI process.
    $retained = [];
    foreach ([
        'product' => $product['image'],
        'project' => $project['main_image'] ?? '',
        'banner' => preg_replace('~^/NHATTRAN/~', '', $banner['image'] ?? ''),
    ] as $kind => $path) {
        if ($path && is_file($directory.'/'.$path)) $retained[] = $kind;
    }
    echo 'AUDIT retained images after record deletion: ' . implode(', ', $retained) . PHP_EOL;
    uploadCheck($retained === [], 'record deletion cleans generated unreferenced images');
    echo 'Isolated artifacts: ' . $directory . PHP_EOL;
    if (in_array('--browser', $argv, true)) {
        $browser = proc_open(['C:/laragon/bin/nodejs/node-v22/node.exe', __DIR__.'/check-browser-layout.mjs', 'http://'.$address, $secret, $directory], [0=>['pipe','r'],1=>STDOUT,2=>STDERR], $browserPipes);
        fclose($browserPipes[0]);
        if (proc_close($browser)!==0) throw new RuntimeException('Browser checks failed');
    }
} finally {
    if (is_resource($server)) { proc_terminate($server); proc_close($server); }
    if ($created) $source->exec("DROP DATABASE `$name`");
    // Remove test authentication and copied DB credentials even if a check fails.
    foreach (['router.php','config/database.php'] as $file) if (is_file($directory.'/'.$file)) unlink($directory.'/'.$file);
}
exit($failures ? 1 : 0);
