# Kiểm duyệt đánh giá

Vào **Đánh giá** trong menu quản trị. Admin và manager được duyệt, ẩn hoặc xóa. Danh sách mặc định là Chờ duyệt, phân trang 20 mục. Thao tác dùng POST/CSRF.

Đánh giá mới luôn lưu `pending`. Website chỉ đọc `approved`. Các đánh giá cũ giữ trạng thái hiện tại sau migration, có thể chuyển sang Ẩn trong dashboard.

Giới hạn hiện tại: tối thiểu 60 giây giữa các lần gửi có token hợp lệ, tối đa 5 lần trong một giờ theo phiên trình duyệt; chặn cùng tên/nội dung trên cùng sản phẩm trong 24 giờ. Đổi phiên có thể vượt giới hạn theo phiên, nên đây là lớp giảm spam cơ bản, không phải biện pháp chống bot phân tán. Trước khi công khai rộng rãi, cân nhắc rate limit tại máy chủ/reverse proxy; mọi bài mới vẫn phải qua duyệt.

- Schema mới: `database/create_product_reviews_table.sql`.
- Nâng cấp database đã có: chạy `database/moderate_product_reviews.sql` trước triển khai code.
- Model: `app/Models/ProductReview.php`.
- Nhận đánh giá: `public/includes/review-submit.php`.
- Quản trị: `admin/reviews/reviews.php`.
- Kiểm tra model: `php scripts/check-product-reviews.php` (fixture được rollback).
- Kiểm tra giới hạn: `php scripts/check-review-limits.php` (không kết nối database).
