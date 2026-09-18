<?php
/*
 * Khởi tạo kết nối PDO dùng chung cho các model và trang PHP.
 * Giữ nguyên thông số kết nối và tùy chọn hiện tại; các file gọi sử dụng biến $pdo.
 * Không xuất thông tin kết nối vào HTML hoặc ghi chú công khai.
 */


$host = "127.0.0.1";
$port = 3306;

$dbname = "nhattran_local";
$username = "root";
$password = "";

try {

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    die("Lỗi kết nối Database: " . $e->getMessage());

}