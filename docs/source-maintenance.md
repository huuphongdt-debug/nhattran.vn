# Bảo trì mã nguồn từ app đến thư mục gốc

Các ghi chú trong mã mô tả luồng hiện tại. Việc thêm ghi chú và xuống dòng không đồng nghĩa đã sửa lỗi chức năng hay hoàn thiện các module còn trống.

| Vị trí | Mở khi cần bảo trì |
| --- | --- |
| `app/Controllers/` | Điều phối model, xử lý dữ liệu đầu vào và kết quả trả về cho trang |
| `app/Models/` | Truy vấn PDO, bộ lọc, phân trang, dữ liệu quan hệ |
| `app/Helpers/` | Phân quyền, logo hãng, cài đặt và schema giá sản phẩm |
| `config/database.php` | Kết nối PDO; giữ thông tin kết nối riêng của môi trường |
| `database/` | Migration và seed thủ công; đọc điều kiện từng file trước khi dùng |
| `public/*.php` | Dữ liệu và template trang khách hàng |
| `public/includes/` | Khung trang, menu, footer, hero và danh sách CSS/JS dùng chung |
| `public/css/`, `public/js/` | Kiểu trình bày và tương tác phía trình duyệt |
| `scripts/` | Kiểm tra cú pháp, mã hóa, tài nguyên và kiểm tra tích hợp được gọi riêng |
| `index.php` | Chuyển từ thư mục gốc sang trang chủ công khai |
| `create-admin.php`, `test-db.php` | Tiện ích cũ; đọc mã trước khi chạy, không dùng để kiểm tra cú pháp |
| `.editorconfig` | Quy ước UTF-8, xuống dòng và thụt lề |

## Các thư mục không phải mã chạy cần định dạng

- `docs/planning/`: tài liệu thiết kế ban đầu; dùng tham khảo và đối chiếu với mã hiện tại.
- `docs/archive/`: bản lưu mã cũ; giữ nguyên để tra cứu lịch sử.
- `khóa/`, `VIDEO/`: tư liệu hình ảnh/video, không sửa như mã nguồn.
- `uploads/`: tài nguyên có thể đang được database tham chiếu; không đổi tên/xóa chỉ để sắp xếp mã.
- `tmp/`: kết quả và tiện ích tạm, không dùng làm nguồn triển khai chính.
- `views/`: hiện chưa dùng; các partial đang dùng nằm trong `public/includes/` và các module admin.

## Kiểm tra sau khi chỉ thêm ghi chú hoặc định dạng

Chạy `scripts/check.ps1` với đường dẫn PHP và Node phù hợp máy. PHP được kiểm tra bằng `-l`, JavaScript bằng `--check`; không thực thi tiện ích tạo tài khoản hoặc migration. Các kiểm tra tích hợp kết nối database phải được gọi riêng.

Với PHP, đối chiếu token sau khi bỏ comment/khoảng trắng và đối chiếu HTML xuất ra. Với CSS/JavaScript/SQL, giữ nguyên chuỗi, selector, thứ tự câu lệnh và dữ liệu. Không xuống dòng bên trong chuỗi SQL, SVG hay nội dung hiển thị chỉ để rút ngắn dòng.
