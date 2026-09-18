<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function backupDirectory(string $path): string
{
    if (!is_dir($path) && !mkdir($path, 0700, true)) throw new RuntimeException('Cannot create backup directory');
    $resolved = realpath($path);
    $root = str_replace('\\', '/', realpath(dirname(__DIR__)));
    $normalized = str_replace('\\', '/', $resolved);
    if (strcasecmp($normalized, $root) === 0 || str_starts_with(strtolower($normalized), strtolower($root) . '/')) {
        throw new RuntimeException('Backups must be outside the website directory.');
    }
    return $resolved;
}

function backupProcess(array $command, string $password, string $output, ?string $input = null): void
{
    $env = getenv();
    $env['MYSQL_PWD'] = $password;
    $process = proc_open($command, [0 => ['file', $input ?? (PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null'), 'r'], 1 => ['file', $output, 'w'], 2 => ['pipe', 'w']], $pipes, null, $env, ['bypass_shell' => true]);
    if (!is_resource($process)) throw new RuntimeException('Cannot start MySQL tool');
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exit = proc_close($process);
    if ($exit !== 0) throw new RuntimeException('MySQL tool failed with exit code ' . $exit . '. Check tool version and database permissions.');
}

function backupVerify(string $directory): array
{
    $manifest = json_decode(file_get_contents($directory . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    if (($manifest['version'] ?? null) !== 1 || !isset($manifest['files']['database.sql'])) throw new RuntimeException('Invalid manifest');
    foreach ($manifest['files'] as $relative => $info) {
        if (str_contains($relative, '..') || str_contains($relative, '\\') || !preg_match('#^(database\.sql|uploads/.+)$#', $relative)) throw new RuntimeException('Unsafe manifest path');
        $path = realpath($directory . '/' . $relative);
        $base = realpath($directory) . DIRECTORY_SEPARATOR;
        if (!$path || !str_starts_with(strtolower($path), strtolower($base)) || !is_file($path)) throw new RuntimeException('Missing/unsafe backup file: ' . $relative);
        if (filesize($path) !== $info['bytes'] || hash_file('sha256', $path) !== $info['sha256']) throw new RuntimeException('Checksum failed: ' . $relative);
    }
    return $manifest;
}
