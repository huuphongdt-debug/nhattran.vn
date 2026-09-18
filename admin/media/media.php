<?php
/*
 * File khởi tạo module media: mở phiên, nạp dependency và kiểm tra quyền quản lý.
 * Hiện chưa có xử lý tải lên, danh sách media hoặc giao diện trong file này.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ProductCategoryController.php";

requireManager();