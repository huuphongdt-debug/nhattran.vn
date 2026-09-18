<?php
// Isolated sessions; only rejected requests and read-only list pages are exercised.
$root = dirname(__DIR__);
$bootstrap = <<<'PHP'
class AuditSession implements SessionHandlerInterface {
    public function open(string $path,string $name):bool{return true;}
    public function close():bool{return true;}
    public function read(string $id):string{return 'admin_user|'.serialize(['role'=>'admin','full_name'=>'Audit']);}
    public function write(string $id,string $data):bool{return true;}
    public function destroy(string $id):bool{return true;}
    public function gc(int $max):int|false{return 0;}
}
session_set_save_handler(new AuditSession(),true);
PHP;
$deletes = ['products/product-delete.php','categories/category-delete.php','categories/category-move.php','posts/post-delete.php','services/services-delete.php','manufacturers/manufacturers-delete.php'];
$forms = ['reviews/reviews.php','products/product-create.php','products/product-edit.php','categories/category-create.php','categories/category-edit.php','posts/post-create.php','posts/post-edit.php','services/services-create.php','services/services-edit.php','banners/banners.php','banners/banner-edit.php','settings/settings.php'];
function runAudit(string $code): string {
    $process=proc_open([PHP_BINARY,'-r',$code],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
    $out=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);
    fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($process);
    if($exit || $error) throw new RuntimeException($error ?: $out);
    return $out;
}
$count=0;
foreach (array_merge($deletes,$forms) as $file) {
    foreach (in_array($file,$deletes,true) ? ['GET'=>405,'POST'=>403] : ['POST'=>403] as $method=>$expected) {
        $code=$bootstrap.'$_SERVER["REQUEST_METHOD"]='.var_export($method,true).';'
            .'register_shutdown_function(function(){echo "STATUS:".(http_response_code() ?: 200);});'
            .'require '.var_export($root.'/admin/'.$file,true).';';
        $out=runAudit($code);
        if(!str_ends_with($out,'STATUS:'.$expected))throw new RuntimeException("Unexpected response: $file $method $out");
        $count++;
    }
}
foreach (['reviews/reviews.php','products/products.php','categories/categories.php','posts/posts.php','services/services.php','banners/banners.php','manufacturers/manufacturers.php'] as $file) {
    $code=$bootstrap.'$_SERVER["REQUEST_METHOD"]="GET";$_SERVER["SCRIPT_NAME"]='.var_export('/NHATTRAN/admin/'.$file,true).';require '.var_export($root.'/admin/'.$file,true).';';
    $html=runAudit($code);
    $dom=new DOMDocument();@$dom->loadHTML($html);
    $xpath=new DOMXPath($dom);
    foreach($xpath->query('//form[translate(@method,"POST","post")="post"]') as $form) {
        if(!$xpath->query('.//input[@name="csrf" or @name="groups_csrf"]',$form)->length)throw new RuntimeException('Missing token: '.$file);
    }
    foreach($xpath->query('//a[@href]') as $a) {
        if(preg_match('/(?:-delete\.php|category-move\.php|[?](?:toggle|delete)=)/',$a->getAttribute('href')))throw new RuntimeException('Unsafe action link: '.$file);
    }
}
echo "$count rejected endpoint requests and 7 rendered list pages passed. No data mutations submitted.\n";
