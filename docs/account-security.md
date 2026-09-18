# Bảo vệ tài khoản quản trị

## Đổi mật khẩu

Trong menu quản trị chọn **Đổi mật khẩu**. Nhập mật khẩu hiện tại và mật khẩu mới hai lần. Mật khẩu mới ít nhất 12 ký tự, tối đa 72 byte, khác mật khẩu hiện tại. Mật khẩu không được điền lại vào HTML khi lỗi. Form có POST/CSRF; đổi thành công tạo lại session ID và token quản trị.

Mật khẩu của các tài khoản đang có không được thay đổi trong đợt triển khai. Các phiên đăng nhập ở thiết bị khác chưa bị thu hồi tự động khi đổi mật khẩu; không xem chức năng này là đăng xuất tất cả thiết bị.

## Giới hạn đăng nhập

Chạy `database/create_auth_throttle.sql` trước triển khai. Bộ đếm lưu trong database, độc lập cookie: 5 lần thử/tên đăng nhập và 30 lần thử/IP trong cửa sổ 15 phút. Tính cả lần đăng nhập đúng để ngăn nhiều yêu cầu đồng thời vượt giới hạn; lần bị chặn không kéo dài cửa sổ. Đổi mật khẩu có bộ đếm riêng cùng ngưỡng. IP lấy từ REMOTE_ADDR, không tin trực tiếp X-Forwarded-For; khi dùng proxy cần cấu hình địa chỉ khách ở web server. Người dùng chung IP có thể cùng chịu hạn mức IP.

Tên/IP được băm làm khóa, không lưu mật khẩu vào bộ đếm. Hash IP không được coi là ẩn danh tuyệt đối. Bộ đếm hết hạn quá một ngày được dọn từng đợt nhỏ khi có yêu cầu. Chưa thay thế WAF hoặc chống bot phân tán.

## Phiên đăng nhập

`app/Helpers/SessionHelper.php` bật strict mode, chỉ dùng cookie, HttpOnly và SameSite=Lax. Secure tự bật khi web server báo HTTPS. Nếu HTTPS kết thúc tại proxy, cấu hình `NHATTRAN_HTTPS_ONLY=1` trên môi trường production và chuyển HTTP sang HTTPS ở web server. Không bật tùy chọn này trên localhost chỉ có HTTP. Không tin header HTTPS do khách tự gửi.

Đăng xuất xóa cookie và hủy phiên hiện tại. Đăng nhập mới không lưu hash mật khẩu vào session.

## Tạo tài khoản bằng CLI

Các file `create-admin.php`, `admin/create-manager.php` và `scripts/create-account.php` đều chặn HTTP. Không còn mật khẩu mặc định hoặc in mật khẩu ra kết quả.

Cấp biến môi trường `NHATTRAN_ACCOUNT_USERNAME`, `NHATTRAN_ACCOUNT_EMAIL`, `NHATTRAN_ACCOUNT_PASSWORD` qua môi trường riêng an toàn, sau đó chạy `php scripts/create-account.php admin` hoặc `php scripts/create-account.php manager`. Không đặt mật khẩu trực tiếp trong lệnh/shell history, không commit vào repository; xóa biến khỏi môi trường sau khi sử dụng. Công cụ chỉ tạo mới, không ghi đè tài khoản trùng.

Kiểm tra: `php scripts/check-account-security.php` tạo fixture trong transaction và rollback; không đổi mật khẩu tài khoản thật.
