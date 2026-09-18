/*
 * Tương tác riêng trang chủ; khởi tạo sau DOMContentLoaded.
 * Các nút data-* và phần tử dialog/chat cần giữ đồng bộ với HTML trang chủ.
 */
/**
 * JavaScript dành riêng cho trang chủ.
 * Không phụ thuộc thư viện bên ngoài.
 */

document.addEventListener('DOMContentLoaded', () => {
    /**
     * Hiển thị lựa chọn gọi điện hoặc nhắn Zalo.
     */
    const callDialog = document.querySelector('#call-options');
    const callDialogButtons = document.querySelectorAll('[data-call-dialog]');
    const closeCallDialogButton = document.querySelector('[data-close-call-dialog]');

    if (callDialog && callDialogButtons.length) {
        const openCallDialog = () => {
            const currentScrollY = window.scrollY;

            callDialog.showModal();

            /* Giữ nguyên vị trí cuộn khi hộp lựa chọn được mở. */
            window.requestAnimationFrame(() => {
                window.scrollTo({ top: currentScrollY, behavior: 'instant' });
            });
        };

        callDialogButtons.forEach((button) => {
            button.addEventListener('click', openCallDialog);
        });

        closeCallDialogButton?.addEventListener('click', () => callDialog.close());

        callDialog.addEventListener('click', (event) => {
            if (event.target === callDialog) {
                callDialog.close();
            }
        });
    }

    /**
     * Mở / đóng hộp chat nhanh.
     */
    const chatToggleButton = document.querySelector('[data-chat-toggle]');
    const chatbox = document.querySelector('#quick-chatbox');
    const closeChatboxButton = document.querySelector('[data-close-chatbox]');

    const closeChatbox = () => {
        if (!chatbox || !chatToggleButton) {
            return;
        }

        chatbox.hidden = true;
        chatToggleButton.setAttribute('aria-expanded', 'false');
    };

    if (chatToggleButton && chatbox) {
        chatToggleButton.addEventListener('click', () => {
            const isOpening = chatbox.hidden;

            chatbox.hidden = !isOpening;
            chatToggleButton.setAttribute('aria-expanded', String(isOpening));
        });

        closeChatboxButton?.addEventListener('click', closeChatbox);

        document.addEventListener('click', (event) => {
            if (!chatbox.hidden && !chatbox.contains(event.target) && !chatToggleButton.contains(event.target)) {
                closeChatbox();
            }
        });
    }

    /**
     * Hiệu ứng xuất hiện nhẹ cho các card khi đi vào vùng nhìn thấy.
     */
    const revealItems = document.querySelectorAll(
        '.category-card, .feature-card, .product-card, .brand-card, .service-card, .news-card, .why-grid article'
    );

    revealItems.forEach((item) => item.classList.add('will-reveal'));

    if (!('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, activeObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                activeObserver.unobserve(entry.target);
            });
        },
        {
            threshold: 0.12,
        }
    );

    revealItems.forEach((item) => observer.observe(item));

    /**
     * Tự chuyển banner, đồng thời cho phép chọn bằng các chấm điều hướng.
     */
    const slides = Array.from(document.querySelectorAll('.hero-slide'));
    const dots = Array.from(document.querySelectorAll('.hero-dot'));

    if (slides.length > 1) {
        let currentSlide = 0;

        const showSlide = (index) => {
            currentSlide = (index + slides.length) % slides.length;

            slides.forEach((slide, slideIndex) => {
                slide.classList.toggle('active', slideIndex === currentSlide);
            });

            dots.forEach((dot, dotIndex) => {
                dot.classList.toggle('active', dotIndex === currentSlide);
            });
        };

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showSlide(index));
        });

        window.setInterval(() => showSlide(currentSlide + 1), 5000);
    }
});
