<?php
/*
 * Tiện ích kiểm tra kết nối database cũ; không thuộc giao diện khách hàng.
 * Kiểm tra cú pháp không cần thực thi kết nối hoặc xuất kết quả của tiện ích.
 */


session_start();

require_once __DIR__ . "/../../config/database.php";

echo "<h2>Kiểm tra kết nối Database</h2>";

echo "<p style='color:green;'>
Database kết nối thành công.
</p>";

echo "<hr>";

try {

    $stmt = $pdo->query("SELECT 1");

    echo "<p style='color:green;'>
    MySQL hoạt động bình thường.
    </p>";

} catch (PDOException $e) {

    echo "<p style='color:red;'>
    Lỗi MySQL:
    </p>";

    echo "<pre>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";
}