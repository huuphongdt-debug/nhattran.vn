<?php
/*
 * Điểm vào thư mục gốc website; giữ nguyên cơ chế chuyển đến phần public.
 * Đường dẫn tại đây phụ thuộc cách triển khai thư mục dự án.
 */

header('Location: public/index.php');
exit;