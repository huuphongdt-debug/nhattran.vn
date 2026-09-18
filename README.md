# Website NHAT TRAN

Website PHP 8 + MySQL, gồm phần công khai và CMS dành cho admin/manager.

## Mở website trên Laragon

- Website: `http://localhost/NHATTRAN/`
- Quản trị: `http://localhost/NHATTRAN/admin/login.php`
- Kết nối database: `config/database.php`.
- SQL tạo/cập nhật bảng và dữ liệu ban đầu: `database/`. Đọc [hướng dẫn database](database/README.md) trước khi chạy.

## Cấu trúc thư mục

| Vị trí | Trách nhiệm |
| --- | --- |
| `public/*.php` | Các trang khách hàng truy cập; giữ URL hiện tại |
| `public/includes/` | HTML dùng chung, helper và danh sách tài nguyên CSS/JS |
| `public/css/` | CSS website, chia theo phần dùng chung và trang |
| `public/js/` | JavaScript dùng chung và từng trang |
| `admin/<module>/` | Màn hình và xử lý form của từng module quản trị |
| `admin/assets/css/<module>/` | CSS riêng của màn hình admin cùng tên |
| `admin/assets/js/<module>/` | JavaScript riêng của màn hình admin cùng tên |
| `app/Controllers/` | Các controller hiện có |
| `app/Models/` | Truy vấn dữ liệu |
| `app/Helpers/` | Phân quyền và xử lý hỗ trợ |
| `config/` | Cấu hình kết nối |
| `database/` | SQL tạo bảng, cập nhật bảng và seed |
| `uploads/` | Ảnh đang được website/database tham chiếu |
| `docs/planning/` | Tài liệu thiết kế ban đầu, chuyển từ `cấu trúc nhattran.vn` |
| `docs/archive/` | Mã cũ không còn được tải bởi website |
| `scripts/` | Công cụ kiểm tra dành cho phát triển |

`VIDEO/` và `khóa/` là tư liệu có sẵn, không thuộc mã chạy của website. `views/` hiện chưa dùng. `create-admin.php` và `test-db.php` là tiện ích cũ ở thư mục gốc, không thuộc luồng truy cập của khách hàng.

Xem [hướng dẫn bảo trì](docs/maintenance.md) để biết cần mở file nào khi sửa menu, giao diện hoặc từng chức năng.

Xem thêm [bản đồ mã nguồn và cách kiểm tra](docs/source-maintenance.md) cho phần `app`, trang công khai, SQL, công cụ và các thư mục tư liệu.
