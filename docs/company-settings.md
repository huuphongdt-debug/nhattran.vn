# Thông tin liên hệ dùng chung

Admin vào **Cài đặt** để chỉnh thông tin công ty, điện thoại, email, Zalo, Facebook, website, địa chỉ bản đồ, mã số thuế, số tài khoản và tên ngân hàng. Các trường pháp lý/ngân hàng là nội dung công bố, cần kiểm tra trước khi lưu.

- `config/company.php`: nhãn và giá trị mặc định tập trung; chỉ dùng khi chưa có khóa trong database.
- `app/Helpers/SettingsHelper.php`: `companyValue()` đọc cấu hình; `companyLink()` tạo URL liên hệ và chỉ cho phép HTTP/HTTPS với website/mạng xã hội.
- `public/includes/contact-section.php`: phần thông tin liên hệ dùng chung cho trang chủ và trang Liên hệ.
- Popup gọi/chat, footer và CTA các trang sản phẩm/dịch vụ/hỗ trợ cùng đọc Settings.

Zalo nhập số điện thoại, không nhập URL. Google Maps nhập địa chỉ/từ khóa vị trí, không nhập mã iframe. Khi lưu, hệ thống kiểm tra email, định dạng điện thoại và giao thức URL; toàn bộ trường được lưu trong một transaction. Đây là kiểm tra định dạng, không xác minh quyền sở hữu số điện thoại hay tài khoản ngân hàng.

Không sửa dữ liệu cài đặt hiện có khi triển khai bước này. Các trường mới dùng giá trị mặc định trước đây cho đến khi admin lưu. Kiểm tra tích hợp: `php scripts/check-company-settings.php` dùng dữ liệu tạm trong transaction và rollback.
