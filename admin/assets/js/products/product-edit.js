/*
 * Thao tác với ảnh đã lưu trên trang sửa sản phẩm.
 * setMainImage(imageId) và deleteImage(imageId) được gọi từ nút thao tác của trang.
 * imageId là ID bản ghi ảnh, không phải số thứ tự ảnh hoặc ID sản phẩm.
 * URL nhận POST lấy từ data-product-edit-url của phần tử có thuộc tính này.
 * Máy chủ xử lý action và image_id; script gửi biểu mẫu thông thường, không dùng AJAX.
 */

/* =====================================================
       ĐẶT ẢNH CHÍNH
    ===================================================== */

    function setMainImage(imageId) {

        // Hủy xác nhận thì kết thúc, không tạo hoặc gửi biểu mẫu.
        if (!confirm('Bạn có chắc muốn đặt ảnh này làm ảnh chính?')) {
            return;
        }


        // Tạo biểu mẫu riêng cho thao tác ảnh, không gửi các trường đang sửa trong form sản phẩm.
        const form = document.createElement('form');

        form.method = 'POST';

        form.action =
            document.querySelector('[data-product-edit-url]').dataset.productEditUrl;


        const actionInput =
            document.createElement('input');

        actionInput.type = 'hidden';

        actionInput.name = 'action';

        // Tên action phải khớp nhánh xử lý đặt ảnh chính trong PHP.
        actionInput.value = 'set_main_image';


        const imageInput =
            document.createElement('input');

        imageInput.type = 'hidden';

        imageInput.name = 'image_id';

        imageInput.value = imageId;


        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = 'csrf';
        csrf.value = document.querySelector('[name="csrf"]').value;
        form.appendChild(csrf);
        form.appendChild(actionInput);

        form.appendChild(imageInput);


        document.body.appendChild(form);

        // Gửi POST và điều hướng theo phản hồi máy chủ; không chỉ đổi preview trên trình duyệt.
        form.submit();
    }



    /* =====================================================
       XÓA ẢNH
    ===================================================== */

    function deleteImage(imageId) {

        // Chỉ gửi yêu cầu xóa khi người dùng đồng ý trong hộp xác nhận.
        if (!confirm(
            'Bạn có chắc muốn xóa hình ảnh này?\n\nHành động này không thể hoàn tác.'
        )) {
            return;
        }


        const form = document.createElement('form');

        form.method = 'POST';

        form.action =
            document.querySelector('[data-product-edit-url]').dataset.productEditUrl;


        const actionInput =
            document.createElement('input');

        actionInput.type = 'hidden';

        actionInput.name = 'action';

        // Cùng cấu trúc POST như đặt ảnh chính, nhưng dùng action xóa ảnh.
        actionInput.value = 'delete_image';


        const imageInput =
            document.createElement('input');

        imageInput.type = 'hidden';

        imageInput.name = 'image_id';

        imageInput.value = imageId;


        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = 'csrf';
        csrf.value = document.querySelector('[name="csrf"]').value;
        form.appendChild(csrf);
        form.appendChild(actionInput);

        form.appendChild(imageInput);


        document.body.appendChild(form);

        form.submit();
    }
