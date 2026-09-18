<?php
/*
 * Quản lý danh mục hãng và gán nhiều hãng trong một lần lưu.
 * group-bootstrap.php cung cấp quyền, CSRF, model và danh sách nhóm.
 * Nhánh save/delete chạy trong transaction; thay liên kết chỉ trong nhóm đang chỉnh sửa.
 * Form phía dưới dùng danh sách hãng và trạng thái chọn để thêm hoặc sửa nhóm.
 */

require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/group-bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id && !in_array($id, array_map('intval', array_column($manufacturerGroups, 'id')), true)) {
            throw new InvalidArgumentException('Danh mục không còn tồn tại. Vui lòng tải lại trang.');
        }
    $action = $_POST['action'] ?? '';
    try {
        $pdo->beginTransaction();
        if ($action === 'delete') {
            $pdo->prepare('DELETE FROM manufacturer_groups WHERE id=?')->execute([$id]);
        } elseif ($action === 'save') {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '' || mb_strlen($name) > 150)
                throw new InvalidArgumentException('Tên danh mục cần từ 1 đến 150 ký tự.');
            if ($id)
                $pdo->prepare('UPDATE manufacturer_groups SET name=? WHERE id=?')->execute([$name, $id]);
            else {
                $pdo->prepare('INSERT INTO manufacturer_groups (name) VALUES (?)')->execute([$name]);
                $id = (int) $pdo->lastInsertId();
            }
            $members = array_unique(array_map('intval', (array) ($_POST['members'] ?? [])));
            $valid = array_map('intval', $pdo->query('SELECT id FROM manufacturers')->fetchAll(PDO::FETCH_COLUMN));
            if (array_diff($members, $valid)) throw new InvalidArgumentException('Hãng đã thay đổi. Vui lòng tải lại trang.');
            $pdo->prepare('DELETE FROM manufacturer_group_members WHERE group_id=?')->execute([$id]);
            $insert = $pdo->prepare('INSERT INTO manufacturer_group_members (manufacturer_id,group_id) VALUES (?,?)');
            foreach ($members as $member) $insert->execute([$member, $id]);
        } else throw new InvalidArgumentException('Thao tác không hợp lệ.');
        $pdo->commit();
        header('Location: groups.php?saved=1');
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e instanceof InvalidArgumentException
            ? $e->getMessage()
            : 'Không lưu được. Hãy kiểm tra tên danh mục có bị trùng không.';
    }
}
$editId = (int) ($_GET['id'] ?? 0);
$editing = null;
foreach ($manufacturerGroups as $group) if ((int) $group['id'] === $editId) $editing = $group;
if ($editId && !$editing) { http_response_code(404); exit('Không tìm thấy danh mục.'); }
$members = $pdo->prepare('SELECT manufacturer_id FROM manufacturer_group_members WHERE group_id=?');
$members->execute([$editId]);
$memberIds = $error
    ? array_map('intval', (array) ($_POST['members'] ?? []))
    : array_map('intval', $members->fetchAll(PDO::FETCH_COLUMN));
$manufacturers = $pdo->query('SELECT id,name FROM manufacturers ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Danh mục hãng | NHAT TRAN</title><link rel="stylesheet" href="../assets/css/layout.css"><link rel="stylesheet" href="../assets/css/manufacturers/groups.css"></head><body class="admin-shell">
<?php require __DIR__ . '/../includes/shell-start.php'; ?>
<main><div class="page-header"><h1>Danh mục hãng sản xuất</h1><a class="btn btn-back" href="manufacturers.php"><?= adminIcon('back') ?> Danh sách hãng</a></div>
<?php if ($error): ?><p role="alert"><?= adminEscape($error) ?></p><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><p role="status">Đã cập nhật danh mục hãng.</p><?php endif; ?>
<div class="manufacturer-groups-layout"><section class="card"><h2><?= $editing ? 'Sửa danh mục' : 'Thêm danh mục' ?></h2>
<form method="post"><input type="hidden" name="groups_csrf" value="<?= adminEscape($_SESSION['manufacturer_groups_csrf']) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $editId ?>">
<label for="group-name">Tên danh mục *</label><input id="group-name" name="name" required maxlength="150" value="<?= adminEscape($error ? ($_POST['name'] ?? '') : ($editing['name'] ?? '')) ?>">
<h3>Các hãng thuộc danh mục</h3><p>Chọn nhiều hãng để phân nhóm cùng lúc. Các danh mục khác của hãng được giữ nguyên.</p><div class="manufacturer-member-list">
<?php foreach ($manufacturers as $manufacturer): ?><label><input type="checkbox" name="members[]" value="<?= (int) $manufacturer['id'] ?>" <?= in_array((int) $manufacturer['id'], $memberIds, true) ? 'checked' : '' ?>> <?= adminEscape($manufacturer['name']) ?></label><?php endforeach; ?>
</div><div class="page-actions"><button class="btn btn-primary" type="submit">Lưu danh mục</button><?php if ($editing): ?><a class="btn btn-back" href="groups.php">Hủy</a><?php endif; ?></div></form></section>
<section class="card"><h2>Danh mục hiện có</h2><?php foreach ($manufacturerGroups as $group): ?><div class="manufacturer-group-row"><a href="groups.php?id=<?= (int) $group['id'] ?>"><strong><?= adminEscape($group['name']) ?></strong><small><?= (int) $group['total'] ?> hãng</small></a><div class="actions"><a class="btn btn-edit" href="groups.php?id=<?= (int) $group['id'] ?>"><?= adminIcon('edit') ?> Sửa</a><form method="post" onsubmit="return confirm('Xóa danh mục này? Các hãng vẫn được giữ lại.');"><input type="hidden" name="groups_csrf" value="<?= adminEscape($_SESSION['manufacturer_groups_csrf']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $group['id'] ?>"><button class="btn btn-delete" type="submit"><?= adminIcon('trash') ?> Xóa</button></form></div></div><?php endforeach; ?></section></div></main>
<?php require __DIR__ . '/../includes/shell-end.php'; ?></body></html>
