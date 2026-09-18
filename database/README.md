# SQL dự án

Các file hiện tại là những thay đổi bổ sung, **chưa phải bản SQL đầy đủ để tạo toàn bộ hệ thống từ đầu**. Khi chuyển máy cần xuất/nhập database đang dùng, rồi áp dụng thay đổi còn thiếu.

## Schema trước khi triển khai

Các trang không còn tự tạo bảng/sửa cột. Sau khi nhập database nền, chạy:

```text
php scripts/migrate-runtime-schema.php --check
php scripts/migrate-runtime-schema.php --apply
php scripts/migrate-runtime-schema.php --check
```

`--check` chỉ đọc schema, trả mã 2 nếu thiếu thành phần, mã 1 nếu lỗi và mã 0 khi sẵn sàng. `--apply` thêm cột giá còn thiếu và tạo bảng settings nếu chưa có; chạy lại không lặp thay đổi đã hoàn thành. Công cụ chỉ chạy CLI, không chạy qua URL.

Sao lưu database trước khi dùng `--apply`. MySQL DDL không rollback như transaction dữ liệu; công cụ khóa migration khi áp dụng để tránh hai lần chạy đồng thời. Nếu kiểu cột price hiện có khác `DECIMAL(15,0) NULL`, công cụ dừng để người vận hành kiểm tra thay vì tự chuyển đổi dữ liệu. Đây không phải bộ migration đầy đủ cho mọi module. Bảng đánh giá và nâng cấp trạng thái dùng các file review SQL riêng.

Khi chuyển hosting, dùng tài khoản triển khai có quyền DDL cho bước này; tài khoản chạy website chỉ cần quyền dữ liệu phù hợp. Không tự thay đổi quyền tài khoản database hiện tại.

| File | Mục đích |
| --- | --- |
| `create_auth_throttle.sql` | Bộ đếm đăng nhập/đổi mật khẩu; chạy trước triển khai chức năng bảo vệ tài khoản |
| `moderate_product_reviews.sql` | Chuyển schema đánh giá cũ sang hỗ trợ chờ duyệt; giữ trạng thái bản ghi hiện có |
| `create_banners_table.sql` | Tạo bảng banner |
| `create_projects_table.sql` | Tạo bảng dự án |
| `add_product_featured.sql` | Bổ sung đánh dấu sản phẩm nổi bật |
| `add_manufacturer_image.sql` | Bổ sung ảnh hãng |
| `create_manufacturer_groups.sql` | Tạo danh mục hãng, bảng liên kết nhiều danh mục và ba nhóm ban đầu; chạy trước khi dùng quản lý danh mục hãng |
| `seed_banners.sql`, `seed_company_banners.sql` | Dữ liệu banner ban đầu |
| `seed_services.sql` | Dữ liệu dịch vụ ban đầu |

Đọc từng file và kiểm tra schema hiện tại trước khi chạy. Không chạy lại seed tự động trên database có dữ liệu thật. Tài liệu cấu trúc dự kiến nằm ở `docs/planning/`.
