/*
 * Điều khiển menu quản trị dùng chung, được nạp bởi admin/includes/shell-end.php.
 * Chờ DOMContentLoaded để các phần tử của khung quản trị sẵn sàng.
 * Các class trạng thái trên body phối hợp với admin/assets/css/layout.css;
 * nếu đổi breakpoint hoặc tên class, cần cập nhật đồng thời CSS và JavaScript.
 */
document.addEventListener('DOMContentLoaded', () => {
    // Nút mở/thu menu và sidebar là bắt buộc; lớp nền backdrop có thể không tồn tại.
    const button = document.querySelector('.admin-menu-button');
    const sidebar = document.querySelector('.admin-sidebar');
    const backdrop = document.querySelector('.admin-backdrop');
    // Chế độ menu phủ áp dụng cho viewport rộng tối đa 1000px, bao gồm 1000px.
    const mobile = window.matchMedia('(max-width: 1000px)');
    if (!button || !sidebar) return;
    // Đồng bộ trạng thái trợ năng với class trên body:
    // mobile mở khi có admin-nav-open; desktop mở khi không có admin-nav-collapsed.
    const sync = () => {
        const open = mobile.matches ? document.body.classList.contains('admin-nav-open') : !document.body.classList.contains('admin-nav-collapsed');
        // aria-expanded thông báo trạng thái nút; inert ngăn tương tác/focus trong menu đóng.
        button.setAttribute('aria-expanded', String(open));
        sidebar.inert = !open;
    };
    // Chỉ xóa trạng thái mở mobile, giữ trạng thái thu menu desktop rồi đồng bộ lại.
    const close = () => { document.body.classList.remove('admin-nav-open'); sync(); };
    // Nút menu đổi class tương ứng với chế độ viewport hiện tại.
    button.addEventListener('click', () => { document.body.classList.toggle(mobile.matches ? 'admin-nav-open' : 'admin-nav-collapsed'); sync(); });
    // Bấm lớp nền để đóng menu; optional chaining bỏ qua khi không có backdrop.
    backdrop?.addEventListener('click', close);
    // Escape trong chế độ mobile đóng menu và đưa focus về nút điều khiển.
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && mobile.matches) { close(); button.focus(); } });
    // Khi vượt qua breakpoint, xóa trạng thái mở mobile để tránh lưu trạng thái cũ.
    mobile.addEventListener('change', close);
    // Khởi tạo aria-expanded và inert từ các class hiện có, không lưu trạng thái vào storage.
    sync();
});
