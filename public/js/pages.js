/*
 * Đổi ảnh chi tiết sản phẩm từ các nút data-photo-src.
 * Ảnh chính dùng data-product-photo; aria-pressed đánh dấu nút được chọn.
 */
document.addEventListener('DOMContentLoaded', () => {
    const photo = document.querySelector('[data-product-photo]');
    const buttons = document.querySelectorAll('[data-photo-src]');
    buttons.forEach(button => button.addEventListener(
        'click',
        () => {
            if (!photo) return;
            photo.src = button.dataset.photoSrc;
            buttons.forEach(item => item.setAttribute(
                'aria-pressed',
                String(item === button)
            ));
        }
    ));
});
