/*
 * Xem trước ảnh trên trang thêm sản phẩm.
 * Phụ thuộc DOM: ô chọn file #images và vùng chứa #imagePreview.
 * Nạp script sau các phần tử này. JavaScript chỉ hỗ trợ chọn/xem trước;
 * việc kiểm tra file và lưu ảnh cần được xử lý ở phía máy chủ.
 */
const imageInput =
    document.getElementById('images');

const imagePreview =
    document.getElementById('imagePreview');


// Mỗi lần đổi lựa chọn sẽ xóa preview cũ và đọc lại danh sách file vừa chọn.
imageInput.addEventListener(
    'change',
    function () {

        imagePreview.innerHTML = '';


        // Chuyển FileList thành mảng để duyệt từng file bằng forEach.
        const files =
            Array.from(this.files);


        /*
        |--------------------------------------------------------------------------
        | KIỂM TRA TỐI ĐA 5 ẢNH
        |--------------------------------------------------------------------------
        */

        // Giới hạn tính trên toàn bộ file được chọn, trước khi lọc loại ảnh.
        // Vượt giới hạn: xóa lựa chọn và dừng; người dùng cần chọn lại.
        if (files.length > 5) {

            alert(
                'Bạn chỉ được chọn tối đa 5 ảnh.'
            );

            this.value = '';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | HIỂN THỊ PREVIEW
        |--------------------------------------------------------------------------
        */

        files.forEach(
            function (file, index) {

                // Chỉ tạo preview cho MIME type bắt đầu bằng image/.
                // File bị bỏ qua ở đây vẫn nằm trong ô chọn file, không bị loại khỏi dữ liệu gửi.
                if (
                    !file.type.startsWith(
                        'image/'
                    )
                ) {

                    return;
                }


                // Mỗi file có một FileReader riêng và được đọc bất đồng bộ.
                const reader =
                    new FileReader();


                // Tạo thẻ ảnh khi đọc xong. Số hiển thị dựa trên vị trí file đã chọn;
                // thứ tự thẻ xuất hiện phụ thuộc thời điểm từng FileReader hoàn thành.
                reader.onload =
                    function (event) {

                        const item =
                            document.createElement(
                                'div'
                            );

                        item.className =
                            'image-preview-item';


                        item.innerHTML = `

                            <img
                                src="${event.target.result}"
                                alt="Ảnh sản phẩm ${index + 1}"
                            >

                            <span
                                class="image-preview-number"
                            >
                                ${index + 1}
                            </span>

                        `;


                        imagePreview.appendChild(
                            item
                        );
                    };


                // Đọc dữ liệu cục bộ thành data URL để xem trước, chưa tải file lên máy chủ.
                reader.readAsDataURL(file);

            }
        );

    }
);
