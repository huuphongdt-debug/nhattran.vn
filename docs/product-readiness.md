# Rà nội dung sản phẩm trước khi vận hành

## Cách sử dụng

Mở **Quản trị → Sản phẩm → Nội dung cần bổ sung**. Danh sách rà toàn bộ sản phẩm, không phụ thuộc bộ lọc hoặc trang hiện tại. Nhấn **Bổ sung** để sửa; kết quả được tính lại khi tải trang.

Kiểm tra này chỉ đọc dữ liệu, không tự đổi trạng thái công bố. Nó phát hiện ảnh đại diện trống, tệp ảnh đại diện/thư viện không tồn tại trong uploads, SKU trống, danh mục/hãng không hợp lệ và mô tả/nội dung trống. Không kiểm tra URL ảnh bên ngoài, chất lượng ảnh hoặc tính chính xác của thông số.

## Kết quả ngày 14/09/2026

Có 9 sản phẩm đang công khai. Tệp ảnh đang được tham chiếu đều tồn tại. Bốn sản phẩm chưa có ảnh đại diện:

| ID | Sản phẩm/mã | Cần bổ sung |
| --- | --- | --- |
| 9 | ABB ACS550-01-031A-4 | Ảnh đúng model |
| 10 | ABB ACS800-U1-0025-5+P901 | Ảnh đúng model |
| 11 | DELIXI CDI-E102G011/P015T4BL | Ảnh và hãng sản xuất |
| 12 | NICE 100DP | Ảnh và hãng sản xuất |

Ba sản phẩm ID 3, 5, 6 cùng tên PLC Mitsubishi FX5U nhưng khác SKU. Cần đối chiếu model thực tế, tên biến thể và thông số; không tự coi là bản ghi trùng để xóa. Nội dung chi tiết của ID 3, 5, 6, 7, 8 hiện chỉ dài 9–27 ký tự, cần duyệt nội dung trước khi công bố. Độ dài không chứng minh thông tin sai nên không dùng làm điều kiện cảnh báo tự động.

Ảnh cần lấy từ ảnh chụp thực tế hoặc tài liệu hãng được phép sử dụng. Không dùng ảnh sinh tự động để đại diện model thiết bị. Giá để liên hệ là lựa chọn hợp lệ, không tính là thiếu dữ liệu.

## Vị trí bảo trì

- `app/Models/ProductReadiness.php`: truy vấn và quy tắc kiểm tra chỉ đọc.
- `admin/products/products-data.php`: gọi kiểm tra sau khi xác thực quyền.
- `admin/products/views/readiness.php`: danh sách cảnh báo và liên kết sửa.
- `admin/assets/css/products/products.css`: kiểu hiển thị desktop/mobile.

Khi dữ liệu tăng lớn, cân nhắc chuyển việc rà tệp ảnh sang báo cáo riêng chạy theo lịch; hiện tại kiểm tra trực tiếp mỗi lần mở danh sách quản trị.
