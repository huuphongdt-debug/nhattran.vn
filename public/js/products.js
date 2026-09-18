/*
 * Tương tác danh sách sản phẩm: cây danh mục, ảnh và hộp liên hệ.
 * Khởi tạo sau DOMContentLoaded; các selector cần khớp public/products.php.
 * Đây là hỗ trợ giao diện phía khách, không thay thế kiểm tra dữ liệu trên máy chủ.
 */
/* =========================================================
   PRODUCTS.JS
   TRANG SẢN PHẨM - NHAT TRAN
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | 1. CATEGORY COLLAPSE / EXPAND
    |--------------------------------------------------------------------------
    |
    | Click vào tên:
    | → đi tới danh mục.
    |
    | Click vào mũi tên:
    | → chỉ mở / đóng danh mục con.
    |
    |--------------------------------------------------------------------------
    */

    const toggles =
        document.querySelectorAll(
            ".category-toggle"
        );


    toggles.forEach(function (toggle) {

        toggle.addEventListener(
            "click",
            function (event) {

                event.preventDefault();
                event.stopPropagation();


                const item =
                    toggle.closest(
                        ".category-item"
                    );


                if (!item) {
                    return;
                }


                const children =
                    item.querySelector(
                        ":scope > .category-children"
                    );


                if (!children) {
                    return;
                }


                const isOpen =
                    item.classList.contains(
                        "is-open"
                    );


                if (isOpen) {

                    /*
                    --------------------------------------------------
                    ĐÓNG
                    --------------------------------------------------
                    */

                    children.hidden = true;

                    item.classList.remove(
                        "is-open"
                    );

                    toggle.setAttribute(
                        "aria-expanded",
                        "false"
                    );

                } else {

                    /*
                    --------------------------------------------------
                    MỞ
                    --------------------------------------------------
                    */

                    children.hidden = false;

                    item.classList.add(
                        "is-open"
                    );

                    toggle.setAttribute(
                        "aria-expanded",
                        "true"
                    );

                }

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | 2. ĐỒNG BỘ TRẠNG THÁI BAN ĐẦU
    |--------------------------------------------------------------------------
    |
    | PHP đã tự mở category cha nếu category con đang được chọn.
    | JS chỉ cần đồng bộ aria-expanded.
    |
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            ".category-item.has-children"
        )
        .forEach(function (item) {

            const toggle =
                item.querySelector(
                    ":scope > .category-row > .category-toggle"
                );

            const children =
                item.querySelector(
                    ":scope > .category-children"
                );


            if (!toggle || !children) {
                return;
            }


            const isOpen =
                item.classList.contains(
                    "is-open"
                );


            toggle.setAttribute(
                "aria-expanded",
                isOpen ? "true" : "false"
            );


            children.hidden =
                !isOpen;

        });


    /*
    |--------------------------------------------------------------------------
    | 3. XỬ LÝ ẢNH SẢN PHẨM BỊ LỖI
    |--------------------------------------------------------------------------
    |
    | Nếu ảnh trong database không tồn tại:
    | → xóa ảnh lỗi
    | → hiện icon placeholder.
    |
    |--------------------------------------------------------------------------
    */

    const productImages =
        document.querySelectorAll(
            ".product-image img"
        );


    productImages.forEach(function (image) {

        image.addEventListener(
            "error",
            function () {

                const container =
                    image.closest(
                        ".product-image"
                    );


                if (!container) {
                    return;
                }


                /*
                Tránh xử lý nhiều lần.
                */

                if (
                    container.dataset.imageError ===
                    "true"
                ) {
                    return;
                }


                container.dataset.imageError =
                    "true";


                /*
                Xóa ảnh lỗi.
                */

                image.remove();


                /*
                Tạo placeholder.
                */

                const placeholder =
                    document.createElement(
                        "div"
                    );


                placeholder.className =
                    "product-image-empty";


                placeholder.innerHTML =
                    '<i class="fa-solid fa-box-open"></i>';


                container.appendChild(
                    placeholder
                );

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | 4. KEYBOARD ACCESSIBILITY
    |--------------------------------------------------------------------------
    |
    | Enter / Space trên nút mũi tên
    | được trình duyệt xử lý tự nhiên.
    |
    | Thêm tabindex để chắc chắn có thể focus.
    |
    |--------------------------------------------------------------------------
    */

    toggles.forEach(function (toggle) {

        toggle.setAttribute(
            "tabindex",
            "0"
        );

    });


    /*
    |--------------------------------------------------------------------------
    | 5. CONTACT MODAL
    |--------------------------------------------------------------------------
    */

    const contactModal =
        document.getElementById(
            "contactModal"
        );


    const contactModalClose =
        document.getElementById(
            "contactModalClose"
        );


    const contactProductName =
        document.getElementById(
            "contactProductName"
        );


    const contactButtons =
        document.querySelectorAll(
            ".product-contact-button"
        );


    let lastFocusedElement = null;


    /*
    |--------------------------------------------------------------------------
    | MỞ CONTACT MODAL
    |--------------------------------------------------------------------------
    */

    function openContactModal(productName) {

        if (!contactModal) {
            return;
        }


        /*
        Lưu phần tử đang được focus
        để sau khi đóng modal có thể
        quay lại đúng vị trí.
        */

        lastFocusedElement =
            document.activeElement;


        /*
        Hiển thị tên sản phẩm.
        */

        if (contactProductName) {

            contactProductName.textContent =
                productName || "Sản phẩm";

        }


        /*
        Hiển thị modal.
        */

        contactModal.hidden = false;


        contactModal.setAttribute(
            "aria-hidden",
            "false"
        );


        /*
        Không cho body cuộn phía sau modal.
        */

        document.body.classList.add(
            "contact-modal-open"
        );


        /*
        Focus vào nút đóng.
        */

        if (contactModalClose) {

            contactModalClose.focus();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | ĐÓNG CONTACT MODAL
    |--------------------------------------------------------------------------
    */

    function closeContactModal() {

        if (!contactModal) {
            return;
        }


        /*
        Ẩn modal.
        */

        contactModal.hidden = true;


        contactModal.setAttribute(
            "aria-hidden",
            "true"
        );


        /*
        Cho phép body cuộn trở lại.
        */

        document.body.classList.remove(
            "contact-modal-open"
        );


        /*
        Trả focus về nút Liên hệ
        trước đó.
        */

        if (
            lastFocusedElement &&
            typeof lastFocusedElement.focus ===
                "function"
        ) {

            lastFocusedElement.focus();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CLICK NÚT "LIÊN HỆ"
    |--------------------------------------------------------------------------
    */

    contactButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                const productName =
                    button.getAttribute(
                        "data-product-name"
                    ) || "Sản phẩm";


                openContactModal(
                    productName
                );

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | CLICK NÚT X ĐÓNG MODAL
    |--------------------------------------------------------------------------
    */

    if (contactModalClose) {

        contactModalClose.addEventListener(
            "click",
            closeContactModal
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CLICK RA NGOÀI MODAL
    |--------------------------------------------------------------------------
    |
    | Các phần tử có:
    | data-contact-close
    |
    | sẽ đóng modal.
    |--------------------------------------------------------------------------
    */

    if (contactModal) {

        contactModal
            .querySelectorAll(
                "[data-contact-close]"
            )
            .forEach(function (element) {

                element.addEventListener(
                    "click",
                    closeContactModal
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | NHẤN ESC ĐỂ ĐÓNG MODAL
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape" &&
                contactModal &&
                !contactModal.hidden
            ) {

                closeContactModal();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | 6. DEBUG
    |--------------------------------------------------------------------------
    */

    console.log(
        "Products JS loaded successfully."
    );

});