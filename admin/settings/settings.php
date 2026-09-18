<?php
/*
 * Cài đặt website dành cho admin; schema chuẩn bị bằng migration trước triển khai.
 * $fields ánh xạ khóa cài đặt sang [nhãn, giá trị mặc định]; chỉ các khóa này được lưu từ POST.
 * INSERT ... ON DUPLICATE KEY UPDATE thêm hoặc cập nhật giá trị theo setting_key.
 * Giá trị thiếu dùng mặc định; settingsEscape() dùng khi xuất dữ liệu ra HTML.
 */

require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();
require_once __DIR__ . '/../../app/Helpers/AuthHelper.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') requireAdminPost();


$fields = require __DIR__ . '/../../config/company.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($fields as $key => $field) {
            if (!is_string($_POST[$key] ?? null)) throw new InvalidArgumentException('Dữ liệu cài đặt không hợp lệ.');
            $value = trim($_POST[$key]);
            if (mb_strlen($value) > 2000) throw new InvalidArgumentException('Mỗi trường tối đa 2.000 ký tự.');
            if ($key === 'company_email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Email chưa hợp lệ.');
            if (in_array($key, ['company_phone', 'company_zalo'], true) && !preg_match('/^\+?[0-9 () .-]{8,30}$/', $value)) throw new InvalidArgumentException('Điện thoại/Zalo cần là số liên hệ hợp lệ.');
            if (in_array($key, ['company_facebook', 'company_website'], true) && $value !== '' && (!filter_var($value, FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true))) throw new InvalidArgumentException('Liên kết cần bắt đầu bằng http:// hoặc https://.');
        }
        $pdo->beginTransaction();
	$statement = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
	foreach ($fields as $key => $field)
        $statement->execute([
            ':key' => $key,
            ':value' => trim((string) ($_POST[$key] ?? ''))
        ]);
    $pdo->commit();
	header('Location: settings.php?saved=1');
	exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e instanceof InvalidArgumentException ? $e->getMessage() : 'Chưa lưu được cài đặt. Vui lòng thử lại.';
    }
}
$values = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
foreach ($fields as $key => $field) $values[$key] ??= $field[1];
if ($error) foreach ($fields as $key => $field) $values[$key] = is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
function settingsEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html><html lang="vi"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cài đặt website | NHAT TRAN</title>
<link rel="stylesheet" href="../assets/css/admin-common.css"><link rel="stylesheet" href="../assets/css/layout.css">
<style>
.settings-page{max-width:960px;margin:0 auto;padding:30px}.settings-heading{display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:24px}.settings-heading h1{margin:0;color:#162a45}.settings-card{padding:24px;border:1px solid #e2e8f0;border-radius:12px;background:#fff}.settings-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.settings-field{display:grid;gap:7px;color:#334155;font-size:14px;font-weight:600}.settings-field.full{grid-column:1/-1}.settings-field input,.settings-field textarea{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font:inherit;font-weight:400}.settings-field textarea{min-height:90px;resize:vertical}.settings-actions{display:flex;justify-content:flex-end;margin-top:24px}.settings-alert{margin-bottom:18px;padding:12px 15px;border-radius:8px;background:#dcfce7;color:#166534}@media(max-width:700px){.settings-page{padding:20px 14px}.settings-grid{grid-template-columns:1fr}.settings-field.full{grid-column:auto}}
</style></head><body class="admin-shell">
<?php require __DIR__ . '/../includes/shell-start.php'; ?>
<main class="settings-page"><div class="settings-heading"><div><h1>Cài đặt website</h1><p>Quản lý thông tin công ty và các kênh liên hệ trên website.</p></div><a class="btn btn-back" href="../dashboard.php"><?= adminIcon('back') ?> Dashboard</a></div>
<?php if (isset($_GET['saved'])): ?><div class="settings-alert" role="status">Đã lưu cài đặt thành công.</div><?php endif; ?>
<form class="settings-card" method="post">
<?php if ($error): ?><p role="alert"><?= settingsEscape($error) ?></p><?php endif; ?>
            <?php echo adminCsrfField(); ?><div class="settings-grid">
<?php foreach ($fields as $key => [$label, $default]): ?><label class="settings-field <?= in_array($key, ['company_description', 'company_address', 'google_map'], true) ? 'full' : '' ?>"><?= settingsEscape($label) ?><?php if (in_array($key, ['company_description', 'company_address', 'google_map'], true)): ?><textarea name="<?= settingsEscape($key) ?>"><?= settingsEscape($values[$key]) ?></textarea><?php else: ?><input name="<?= settingsEscape($key) ?>" value="<?= settingsEscape($values[$key]) ?>"><?php endif; ?></label><?php endforeach; ?>
</div><div class="settings-actions"><button class="btn btn-primary" type="submit"><?= adminIcon('check') ?> Lưu cài đặt</button></div></form></main>
<?php require __DIR__ . '/../includes/shell-end.php'; ?></body></html>
