/*
 * Điều khiển menu công khai và class bóng header khi cuộn.
 * Đồng bộ aria-expanded với trạng thái menu; bỏ qua phần tử không có trên trang.
 */
// Hanh vi menu dung chung cho cac trang.
document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.site-header');
    const menuButton = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    /**
     * Mở / đóng menu trên điện thoại.
     */
    if (menuButton && mainNav) {
        menuButton.addEventListener('click', () => {
            const isOpen = mainNav.classList.toggle('open');

            menuButton.setAttribute('aria-expanded', String(isOpen));
        });

        mainNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                mainNav.classList.remove('open');
                menuButton.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /**
     * Tạo bóng nhẹ cho header khi người dùng cuộn trang.
     */
    const updateHeaderState = () => {
        if (!header) {
            return;
        }

        header.classList.toggle('is-scrolled', window.scrollY > 10);
    };

    updateHeaderState();
    window.addEventListener('scroll', updateHeaderState, { passive: true });


});
