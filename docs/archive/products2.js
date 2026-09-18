/* =========================================================
   PRODUCTS PAGE - JAVASCRIPT
   ---------------------------------------------------------
   Chức năng:
   1. Thu gọn / mở rộng danh mục cha
   2. Tự động mở danh mục đang được chọn
   3. Xử lý mũi tên danh mục
   4. Xử lý ảnh sản phẩm lỗi
   5. Hỗ trợ mobile / touch
========================================================= */


document.addEventListener("DOMContentLoaded", function () {


        /* =====================================================
           1. CATEGORY COLLAPSE / EXPAND
        ===================================================== */

        const categoryParents =
            document.querySelectorAll(".category-parent");


        categoryParents.forEach(function (parentLink) {


                /*
                -------------------------------------------------
                LẤY CATEGORY ITEM CHA
                -------------------------------------------------
                */

                const parentItem =
                    parentLink.closest(".category-item");


                if (!parentItem) {
                    return;
                }


                /*
                -------------------------------------------------
                LẤY DANH MỤC CON
                -------------------------------------------------
                */

                const children =
                    parentItem.querySelector(
                        ":scope > .children"
                    );


                /*
                Nếu không có danh mục con
                thì không cần xử lý collapse.
                */

                if (!children) {
                    return;
                }


                /*
                -------------------------------------------------
                MŨI TÊN
                -------------------------------------------------
                */

                const arrow =
                    parentLink.querySelector(
                        ":scope > .category-arrow"
                    );


                if (!arrow) {
                    return;
                }


                /*
                -------------------------------------------------
                HÀM OPEN
                -------------------------------------------------
                */

                function openCategory() {

                    children.hidden = false;

                    parentItem.classList.add(
                        "is-open"
                    );

                    arrow.textContent = "▾";

                }


                /*
                -------------------------------------------------
                HÀM CLOSE
                -------------------------------------------------
                */

                function closeCategory() {

                    children.hidden = true;

                    parentItem.classList.remove(
                        "is-open"
                    );

                    arrow.textContent = "▸";

                }


                /*
                -------------------------------------------------
                TRẠNG THÁI BAN ĐẦU
                -------------------------------------------------

                PHP đã xác định sẵn danh mục
                nào cần mở bằng class is-open.
                */

                if (
                    parentItem.classList.contains(
                        "is-open"
                    )
                ) {

                    openCategory();

                } else {

                    closeCategory();

                }


                /*
                -------------------------------------------------
                CLICK MŨI TÊN
                -------------------------------------------------

                Click tên:
                → đi tới danh mục

                Click mũi tên:
                → mở / đóng
                -------------------------------------------------
                */

                arrow.addEventListener(
                    "click",
                    function (event) {

                        event.preventDefault();

                        event.stopPropagation();


                        const isOpen =
                            parentItem.classList.contains(
                                "is-open"
                            );


                        if (isOpen) {

                            closeCategory();

                        } else {

                            openCategory();

                        }

                    }
                );


                /*
                -------------------------------------------------
                KEYBOARD
                -------------------------------------------------

                Enter / Space
                → mở / đóng
                -------------------------------------------------
                */

                arrow.addEventListener(
                    "keydown",
                    function (event) {


                        if (
                            event.key === "Enter"
                            ||
                            event.key === " "
                        ) {

                            event.preventDefault();

                            event.stopPropagation();


                            const isOpen =
                                parentItem.classList.contains(
                                    "is-open"
                                );


                            if (isOpen) {

                                closeCategory();

                            } else {

                                openCategory();

                            }

                        }

                    }
                );


            }
        );


        /* =====================================================
           2. XỬ LÝ ẢNH BỊ LỖI
        ===================================================== */

        const productImages =
            document.querySelectorAll(
                ".product-image img"
            );


        productImages.forEach(
            function (image) {


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
                        -------------------------------------------------
                        TRÁNH XỬ LÝ NHIỀU LẦN
                        -------------------------------------------------
                        */

                        if (
                            container.dataset
                                .imageError === "true"
                        ) {

                            return;

                        }


                        container.dataset.imageError =
                            "true";


                        /*
                        -------------------------------------------------
                        XÓA ẢNH LỖI
                        -------------------------------------------------
                        */

                        image.remove();


                        /*
                        -------------------------------------------------
                        TẠO PLACEHOLDER
                        -------------------------------------------------
                        */

                        const placeholder =
                            document.createElement(
                                "div"
                            );


                        placeholder.className =
                            "product-image-empty";


                        placeholder.textContent =
                            "📦";


                        container.appendChild(
                            placeholder
                        );


                    }
                );

            }
        );


        /* =====================================================
           3. DEBUG
        ===================================================== */

        console.log(
            "Products JS loaded successfully."
        );


    }
);
/* =========================================================
   PRODUCTS PAGE
   CATEGORY COLLAPSE / EXPAND
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const categoryItems =
        document.querySelectorAll(
            ".category-item.has-children"
        );


    categoryItems.forEach(function (item) {

        const toggle =
            item.querySelector(
                ":scope > .category-row .category-toggle"
            );

        const children =
            item.querySelector(
                ":scope > .category-children"
            );


        if (!toggle || !children) {
            return;
        }


        /*
        -----------------------------------------------------
        MẶC ĐỊNH THU GỌN
        -----------------------------------------------------
        */

        children.hidden = true;

        item.classList.remove(
            "is-open"
        );

        toggle.setAttribute(
            "aria-expanded",
            "false"
        );


        /*
        -----------------------------------------------------
        CLICK MŨI TÊN
        -----------------------------------------------------
        */

        toggle.addEventListener(
            "click",
            function (event) {

                event.preventDefault();
                event.stopPropagation();


                const isOpen =
                    item.classList.contains(
                        "is-open"
                    );


                if (isOpen) {

                    /*
                    ĐÓNG
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
                    MỞ
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
    =========================================================
    TỰ ĐỘNG MỞ DANH MỤC ĐANG CHỨA SẢN PHẨM ĐƯỢC CHỌN
    =========================================================
    */

    const selectedChild =
        document.querySelector(
            ".category-child.selected"
        );


    if (selectedChild) {

        const parentItem =
            selectedChild.closest(
                ".category-item.has-children"
            );


        if (parentItem) {

            const children =
                parentItem.querySelector(
                    ":scope > .category-children"
                );

            const toggle =
                parentItem.querySelector(
                    ":scope > .category-row .category-toggle"
                );


            if (children) {

                children.hidden = false;

            }


            parentItem.classList.add(
                "is-open"
            );


            if (toggle) {

                toggle.setAttribute(
                    "aria-expanded",
                    "true"
                );

            }

        }

    }


    /*
    =========================================================
    CLICK VÀO DANH MỤC CHA
    VẪN ĐI ĐẾN TRANG DANH MỤC
    =========================================================

    Chỉ mũi tên mới đóng/mở.
    */


    console.log(
        "Products category system loaded."
    );

});