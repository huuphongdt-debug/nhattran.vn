<?php
/*
 * File khởi tạo sửa trang nội dung: dependency và kiểm tra quyền quản lý.
 * Hiện chưa có tải bản ghi, xử lý lưu hoặc biểu mẫu chỉnh sửa.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ProductCategoryController.php";

requireManager();