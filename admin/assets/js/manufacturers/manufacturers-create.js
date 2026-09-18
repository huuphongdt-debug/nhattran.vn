/*
 * Tự động tạo slug từ tên hãng trên trang thêm hãng sản xuất.
 * Trang sử dụng: admin/manufacturers/manufacturers-create.php.
 * Script cần được nạp sau các ô nhập có id="name" và id="slug".
 * Đây là hỗ trợ nhập liệu phía trình duyệt; việc kiểm tra và lưu dữ liệu do PHP xử lý.
 */

// Lấy hai ô nhập để đọc tên hãng và điền slug tương ứng.
const nameInput =
    document.getElementById('name');

const slugInput =
    document.getElementById('slug');


/* Chuyển chuỗi tên thành slug gồm chữ thường ASCII, chữ số và dấu gạch ngang.
 * Ví dụ: "Điện Máy ABB" → "dien-may-abb".
 * Chuỗi chỉ có dấu cách hoặc ký tự bị loại bỏ có thể tạo slug rỗng.
 */
function createSlug(text) {

    return text

        // Chuyển chữ hoa thành chữ thường trước khi xử lý dấu tiếng Việt.
        .toLowerCase()

        // Tách ký tự có dấu thành ký tự gốc và dấu kết hợp.
        .normalize('NFD')

        // Loại bỏ dấu kết hợp trong khoảng Unicode U+0300–U+036F.
        .replace(
            /[\u0300-\u036f]/g,
            ''
        )

        .replace(
            // Chữ đ không được NFD tách thành d nên cần thay riêng.
            /đ/g,
            'd'
        )

        .replace(
            // Gom mỗi cụm ký tự ngoài a–z và 0–9 thành một dấu gạch ngang.
            /[^a-z0-9]+/g,
            '-'
        )

        .replace(
            // Bỏ dấu gạch ngang ở đầu và cuối slug.
            /^-+|-+$/g,
            '');
}


// Cập nhật khi người dùng nhập, dán hoặc xóa nội dung tên hãng.
// Mỗi lần tên thay đổi sẽ ghi đè ô slug, kể cả slug đã được sửa thủ công.
// Script không tự tạo lại slug khi trang vừa tải nếu chưa có sự kiện input.
nameInput.addEventListener(
    'input',
    function () {

        slugInput.value =
            createSlug(
                nameInput.value
            );

    }
);
