/*
 * Xác nhận xóa trên trang danh sách dự án admin/projects/projects.php.
 * Nạp script sau các biểu mẫu có thuộc tính data-delete-project.
 * Chỉ gắn sự kiện vào biểu mẫu đang có trong DOM khi script chạy;
 * biểu mẫu được thêm động sau đó không tự nhận trình xử lý này.
 *
 * Khi gửi biểu mẫu: Hủy → preventDefault() để chặn gửi;
 * Đồng ý → để trình duyệt gửi biểu mẫu theo action/method đã khai báo trong HTML.
 * Script không tự xóa dữ liệu; phía máy chủ chịu trách nhiệm kiểm tra và xử lý xóa.
 * Nếu trang không có biểu mẫu phù hợp, không có sự kiện nào được gắn.
 */
document.querySelectorAll('[data-delete-project]').forEach(form => form.addEventListener('submit', event => { if (!confirm('Xóa dự án này? Dự án sẽ không còn trên website.')) event.preventDefault(); }));
