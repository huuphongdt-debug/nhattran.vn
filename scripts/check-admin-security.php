<?php
// Tests run in separate PHP processes and never connect to the database.
$root = dirname(__DIR__);
$cases = [
    ['GET', 'manager', 'valid', 405],
    ['POST', 'manager', 'missing', 403],
    ['POST', 'manager', 'wrong', 403],
    ['POST', 'manager', 'array', 403],
    ['POST', 'viewer', 'valid', 403],
    ['POST', 'manager', 'valid', 200],
    ['POST', 'admin', 'valid', 200],
];
foreach ($cases as [$method, $role, $token, $expected]) {
    $code = 'require ' . var_export($root . '/app/Helpers/AuthHelper.php', true) . ';'
        . 'require ' . var_export($root . '/app/Helpers/CsrfHelper.php', true) . ';'
        . '$_SERVER["REQUEST_METHOD"]=' . var_export($method, true) . ';'
        . '$_SESSION=["admin_user"=>["role"=>' . var_export($role, true) . '],"admin_csrf"=>str_repeat("a",64)];'
        . '$_POST=' . var_export(match ($token) {
            'valid' => ['csrf' => str_repeat('a', 64)],
            'wrong' => ['csrf' => 'wrong'],
            'array' => ['csrf' => []],
            default => [],
        }, true) . ';'
        . 'register_shutdown_function(function(){echo "STATUS:".(http_response_code() ?: 200);});'
        . 'requireManager(); requireAdminPost();';
    $process = proc_open([PHP_BINARY, '-r', $code], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Cannot start security check');
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    $exit = proc_close($process);
    if ($exit !== 0 || $errors !== '' || !str_ends_with($output, 'STATUS:' . $expected)) {
        throw new RuntimeException("Failed: $method/$role/$token: $output $errors");
    }
}
echo count($cases) . " administrative permission/CSRF checks passed. No database writes.\n";
