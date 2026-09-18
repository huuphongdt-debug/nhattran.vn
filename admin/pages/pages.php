<?php
/*
 * File khởi tạo danh sách trang nội dung: dependency và kiểm tra quyền quản lý.
 * Hiện chưa có truy vấn danh sách hoặc giao diện quản lý trang.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ProductCategoryController.php";

requireManager();