<?php
// Regression guard: application requests must not execute schema-changing SQL.
$root = dirname(__DIR__);
$failures = [];
foreach (['app', 'admin', 'public'] as $directory) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory, FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') continue;
        foreach (token_get_all(file_get_contents($file->getPathname())) as $token) {
            if (!is_array($token) || !in_array($token[0], [T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)) continue;
            if (preg_match('/\b(?:CREATE|ALTER|DROP|TRUNCATE)\s+TABLE\b/i', $token[1])) {
                $failures[] = $file->getPathname() . ':' . $token[2];
            }
        }
    }
}
foreach ($failures as $failure) fwrite(STDERR, "Runtime DDL found: $failure\n");
echo $failures ? "Runtime schema check failed.\n" : "No schema-changing SQL in app/admin/public.\n";
exit($failures ? 1 : 0);
