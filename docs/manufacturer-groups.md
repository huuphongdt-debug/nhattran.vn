# Danh mục hãng sản xuất

Vào **Hãng sản xuất → Danh mục hãng** để thêm/sửa tên nhóm và chọn nhiều hãng cùng lúc. Một hãng có thể thuộc nhiều nhóm. Xóa nhóm chỉ xóa liên kết phân nhóm, không xóa hãng hoặc sản phẩm.

Danh sách hãng hỗ trợ tìm theo tên/slug kết hợp lọc danh mục, bao gồm **Chưa phân nhóm**. Form thêm/sửa hãng có ô chọn nhiều danh mục. Danh mục hãng độc lập với danh mục sản phẩm.

Trang chủ hiển thị toàn bộ hãng theo các nhóm này qua `ManufacturerGroup::directory()` và `public/includes/brand-directory.php`. Hãng chưa có nhóm nằm trong “Thương hiệu khác”; hãng chưa có logo vẫn hiển thị tên. Nhóm rỗng không hiển thị. Một hãng thuộc nhiều nhóm được hiển thị trong từng nhóm tương ứng.

Các hãng có sẵn chưa được tự động phân nhóm. Ba nhóm mặc định: Thiết bị tự động hóa, Thiết bị hàn cắt công nghiệp, Phụ kiện khí nén.

## Bảo trì

- `database/create_manufacturer_groups.sql`: schema và nhóm mặc định. Áp dụng khi triển khai sang database khác.
- `app/Models/ManufacturerGroup.php`: đọc và đồng bộ liên kết hãng–nhóm.
- `admin/manufacturers/group-bootstrap.php`: phân quyền, CSRF và kiểm tra danh mục được chọn.
- `admin/manufacturers/groups.php`: quản lý nhóm và gán hãng hàng loạt.
- `admin/manufacturers/views/group-fields.php`: phần chọn nhóm dùng chung cho thêm/sửa hãng.
- `admin/assets/css/manufacturers/groups.css`: giao diện nhóm.
- `scripts/check-manufacturer-groups.php`: kiểm tra gán nhiều nhóm, đổi nhóm, dữ liệu không hợp lệ và xóa nhóm; dữ liệu thử nghiệm nằm trong transaction và được rollback.

Chạy kiểm tra với PHP CLI: `php scripts/check-manufacturer-groups.php`.
