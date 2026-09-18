<?php
// Requests must be rejected without a PDO connection or any insert.
$path = dirname(__DIR__) . '/public/includes/review-submit.php';
foreach (['csrf' => 403, 'minute' => 429, 'hour' => 429] as $case => $expected) {
    $code = '$id=1;$product=true;$_SERVER["REQUEST_METHOD"]="POST";'
        . '$_SESSION=["review_csrf"=>"token"];$_POST=["submit_review"=>1,"csrf"=>"token"];'
        . match ($case) {
            'csrf' => '$_POST["csrf"]=[];',
            'minute' => '$_SESSION["review_attempts"]=[time()-1];',
            'hour' => '$_SESSION["review_attempts"]=[time()-500,time()-400,time()-300,time()-200,time()-100];',
        }
        . 'require ' . var_export($path, true) . ';echo "STATUS:".http_response_code();';
    $process = proc_open([PHP_BINARY, '-r', $code], [1 => ['pipe','w'], 2 => ['pipe','w']], $pipes);
    $out=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);
    fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($process);
    if ($exit || $error || $out !== 'STATUS:' . $expected) throw new RuntimeException("Failed $case: $out $error");
}
echo "Review CSRF, minute and hourly limits passed without database access.\n";
