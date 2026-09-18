# Kiểm tra phát triển: PHP lint, JavaScript, tham chiếu tài nguyên và mã hóa.
# PhpPath/NodePath cho phép dùng executable trong Laragon khi chưa có trong PATH.
# Không chạy các trang PHP, migration SQL hoặc tiện ích tạo tài khoản.
param(
    [string]$PhpPath = 'php',
    [string]$NodePath = 'node'
)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$failureCount = 0
$phpFiles = @(Get-ChildItem -LiteralPath $projectRoot -Filter *.php -File)
foreach ($folder in @('app', 'admin', 'public', 'config', 'scripts')) {
    $phpFiles += @(Get-ChildItem -LiteralPath (Join-Path $projectRoot $folder) -Filter *.php -Recurse -File)
}
foreach ($file in $phpFiles) {
    $result = & $PhpPath -l $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0) { Write-Output $result; $failureCount++ }
}
Write-Output "PHP checked: $($phpFiles.Count)"
$jsFiles = @(Get-ChildItem -LiteralPath (Join-Path $projectRoot 'public/js'), (Join-Path $projectRoot 'admin/assets/js') -Filter *.js -Recurse -File)
foreach ($file in $jsFiles) {
    $result = & $NodePath --check $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0) { Write-Output $result; $failureCount++ }
}
Write-Output "JavaScript checked: $($jsFiles.Count)"
& $PhpPath (Join-Path $PSScriptRoot 'check-assets.php')
if ($LASTEXITCODE -ne 0) { $failureCount++ }
& $PhpPath (Join-Path $PSScriptRoot 'check-encoding.php')
if ($LASTEXITCODE -ne 0) { $failureCount++ }
& $PhpPath (Join-Path $PSScriptRoot 'check-runtime-schema.php')
if ($LASTEXITCODE -ne 0) { $failureCount++ }
if ($failureCount) { throw "$failureCount checks failed." }
Write-Output 'All checks passed.'
