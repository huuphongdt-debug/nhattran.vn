<?php
/*
 * Mở khung quản trị: nạp layout, liên kết bỏ qua menu, topbar, backdrop và sidebar.
 * Thẻ admin-workspace mở tại đây được đóng bởi shell-end.php.
 * ID/class menu phối hợp với assets/js/layout.js và assets/css/layout.css.
 */
 require_once __DIR__ . '/layout.php'; ?>
<a class="admin-skip" href="#admin-content">Đến nội dung chính</a>
<?php require __DIR__ . '/topbar.php'; ?>
<button class="admin-backdrop" type="button" aria-label="Đóng menu" tabindex="-1"></button>
<?php require __DIR__ . '/sidebar.php'; ?>
<div class="admin-workspace" id="admin-content" tabindex="-1">
