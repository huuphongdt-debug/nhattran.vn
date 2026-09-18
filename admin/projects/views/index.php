<?php /*
 * View điều phối dự án; cần biến từ projects-data.php và helper của shell.
 * $editing quyết định nạp form.php hay list.php; thông báo được escape trước khi xuất.
 */
?><main class="project-admin">
    <div class="project-heading">
        <div><h1><?= $editing ? ($id ? 'Chỉnh sửa dự án' : 'Thêm dự án') : 'Quản lý dự án' ?></h1><p>Quản lý hồ sơ và các dự án hiển thị trên website.</p></div>
        <div class="page-actions">
            <a class="btn btn-back" href="<?= $editing ? 'projects.php' : '../dashboard.php' ?>"><?= adminIcon('back') ?> <?= $editing ? 'Danh sách dự án' : 'Dashboard' ?></a>
            <?php if (!$editing): ?><a class="project-button" href="projects.php?new=1"><?= adminIcon('plus') ?> Thêm dự án</a><?php endif; ?>
        </div>
    </div>
    <?php if ($error): ?><p class="project-alert error" role="alert"><?= projectEscape($error) ?></p><?php endif; ?>
    <?php if ($message): ?><p class="project-alert" role="status"><?= projectEscape($message) ?></p><?php endif; ?>
    <?php if ($editing): ?>
    <?php require __DIR__ . '/form.php'; ?>
    <?php else: ?>
    <?php require __DIR__ . '/list.php'; ?>
    <?php endif; ?>
</main>
