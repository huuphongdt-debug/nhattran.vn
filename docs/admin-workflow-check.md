# Kiểm tra luồng quản trị — 14/09/2026

## Ngày 18/09/2026 — ảnh dùng chung và giao diện

- Bộ HTTP hiện đạt 36 tình huống, gồm xóa riêng ảnh thư viện vẫn giữ tệp đang được banner dùng chung; thay ảnh dự án/banner dọn ảnh cũ không còn tham chiếu. Bộ sản phẩm CLI 7 trường hợp tiếp tục đạt.
- Đã chuyển xóa riêng ảnh thư viện sang helper kiểm tra tham chiếu sau khi đồng bộ ảnh đại diện; bổ sung dọn ảnh cũ sau lưu dự án/banner thành công.
- Edge headless kiểm tra 11 trang ở viewport 390, 768 và 1440: dashboard, sản phẩm/dự án/dịch vụ/bài viết/banner quản trị, đổi mật khẩu, trang chủ/sản phẩm/giới thiệu/liên hệ phía khách. Kiểm tra mở menu quản trị và đóng bằng Escape ở hai kích thước nhỏ; chụp ảnh và đo chiều rộng thực.
- Phát hiện trang Giới thiệu tràn ngang khi ảnh không tải được. Sửa grid chi tiết dùng `minmax(0, 1fr)` và giới hạn chiều rộng phần tử con để chữ thay thế ảnh không đẩy rộng cột.
- Đã xem ảnh dashboard ở 390 và 1440 px, trang đổi mật khẩu ở 390 px. Kiểm tra này dùng database thử đã dọn fixture, chủ yếu xác nhận bố cục trống/form và menu; chưa chứng minh bảng nhiều hàng, nội dung dài, mọi ảnh thật hoặc trải nghiệm trên Safari/iPhone thật. Bản thử không sao chép uploads thật, nên ảnh tĩnh có thể thiếu trong ảnh chụp.

Các ghi nhận cũ bên dưới là lịch sử từng đợt, không phải toàn bộ trạng thái hiện tại.

## Bổ sung mới nhất: dọn ảnh sau khi xóa bản ghi

Đã bổ sung `UploadCleanupHelper.php` cho xóa sản phẩm, dự án và banner. Sau khi xóa bản ghi thành công, chỉ dọn ảnh mang tên do ứng dụng sinh, thuộc uploads và không còn tên tệp được tham chiếu trong các cột dữ liệu dạng chuỗi của database. Nếu truy vấn kiểm tra lỗi, giữ ảnh và ghi log. Không dọn hàng loạt tệp tồn đọng từ trước.

Bộ HTTP đạt 33 trường hợp, gồm giữ ảnh khi banner khác còn dùng ảnh sản phẩm vừa xóa và dọn ảnh sau khi tham chiếu cuối bị xóa. Bộ sản phẩm CLI 7 trường hợp và kiểm tra PHP/JS/assets/encoding đều đạt. Dữ liệu thật không bị thay đổi.

Giới hạn: chưa kiểm thử ghi dữ liệu đồng thời trong khoảng kiểm tra tham chiếu–xóa file; không phát hiện ảnh được viết cố định trong mã nguồn hoặc liên kết từ hệ thống bên ngoài. Chỉ dùng đường dẫn upload động cho nội dung quản trị. Việc dò các cột dạng chuỗi có thể tốn thời gian khi database lớn; cần đo lại hoặc chuyển sang quản lý tham chiếu tập trung khi mở rộng. Xóa riêng ảnh thư viện và thay ảnh cũ là luồng khác, chưa được chuyển sang helper này trong đợt này.

## Bổ sung: nhóm hãng và ảnh còn lại sau khi xóa bản ghi

Bộ HTTP hiện đạt 31 kiểm tra chức năng, thêm bốn tình huống: gán hãng vào hai nhóm, hai nhóm xuất hiện trên trang chủ, bỏ nhóm xóa liên kết, hãng đang có sản phẩm không bị xóa. Tất cả chạy trong bản sao riêng.

Kiểm tra tệp sau khi xóa bản ghi xác nhận **ảnh sản phẩm, dự án và banner vẫn còn trên ổ đĩa**. Dòng `AUDIT retained images after record deletion` là phát hiện tồn đọng, không phải kiểm tra dọn ảnh đã đạt. Chưa thay đổi chính sách xóa tệp: cần kiểm tra tham chiếu dùng chung, giới hạn đường dẫn trong uploads và kiểm thử trước khi bổ sung dọn ảnh an toàn. Không chạy dọn ảnh trên dữ liệu thật.

Đã thử chụp trang sản phẩm bằng Edge headless. Ảnh chụp ở kích thước cửa sổ nhỏ bị cắt bên phải; chưa xác minh viewport thực tế của trình duyệt headless nên chưa kết luận đó là lỗi responsive của website. Giao diện mobile, thao tác menu và dashboard vẫn chưa được nghiệm thu đầy đủ bằng trình duyệt.

## Bổ sung lần tiếp theo: hãng, dự án, banner và dung lượng ảnh

Bộ `check-upload-http.php` hiện đạt 27 trường hợp (chạy lại cả các kiểm tra cũ):

- Hãng sản xuất: thêm, sửa tên, xóa.
- Dự án: thêm kèm ảnh được chuyển đổi, sửa tên, xóa.
- Banner: thêm kèm ảnh, sửa tiêu đề, đảo trạng thái, xóa.
- Sản phẩm: từ chối ảnh trên 5 MB ở cả tạo mới và thêm ảnh tại trang sửa.

Phát hiện trang tạo sản phẩm thiếu giới hạn 5 MB vốn đã có ở trang sửa. Đã bổ sung kiểm tra phía máy chủ; thử HTTP với giới hạn PHP 16 MB xác nhận ứng dụng tự từ chối ảnh quá 5 MB. Không thay đổi cấu hình PHP của website thật.

Phạm vi này kiểm tra CRUD cơ bản, chưa xác nhận mọi trường dữ liệu, liên kết nhóm hãng, dọn ảnh khi xóa toàn bộ bản ghi, thao tác đồng thời hoặc giao diện trực quan. Kiểm tra upload dùng PNG hợp lệ, tệp giả PNG và tệp PNG thêm dữ liệu để vượt dung lượng; chưa bao phủ mọi định dạng/kích thước pixel. Dữ liệu và uploads thật không được dùng làm mục tiêu ghi/xóa.

## Bổ sung: nghiệm thu HTTP và tệp ảnh thật

`php scripts/check-upload-http.php` đã chạy đạt 15 trường hợp:

- Gửi multipart PNG để tạo sản phẩm, xác nhận tệp được lưu trong uploads của bản thử.
- Sản phẩm active trả 200 và có tên trong trang chi tiết.
- Tệp văn bản giả PNG bị từ chối, bản ghi sản phẩm được rollback.
- Upload thêm ảnh thư viện và xóa ảnh có kiểm tra tệp vật lý.
- Chuyển sang draft thì trang chi tiết trả 404.
- Thêm/sửa/xóa qua HTTP cho dịch vụ, bài viết và danh mục sản phẩm (9 trường hợp).

Môi trường thử sao chép mã sang thư mục Temp riêng, tạo database chỉ có schema, dùng server PHP chỉ lắng nghe localhost và token ngẫu nhiên cho request thử. Phiên admin được giả lập, không sử dụng tài khoản thật. Sau kiểm tra server dừng, database bị xóa, config chứa thông tin kết nối và router xác thực thử được gỡ. Các tệp thử còn lại nằm trong Temp để đối chiếu.

Đợt HTTP này không phát hiện lỗi ở 15 tình huống trên. Chưa kiểm tra giao diện trực quan/mobile, đăng nhập qua trình duyệt, giới hạn dung lượng ảnh, mọi định dạng ảnh, CRUD hãng/dự án/banner hoặc tình huống đồng thời. Những giới hạn CLI bên dưới mô tả đợt kiểm tra trước; upload PNG và xóa tệp vật lý đã được kiểm tra bổ sung bằng HTTP trong đợt này.

## Kiểm thử sản phẩm trên database riêng

Chạy `php scripts/check-product-workflow.php`. Công cụ tạo database ngẫu nhiên chỉ sao chép schema, không sao chép dữ liệu khách hàng hoặc đường dẫn ảnh đang dùng. Endpoint thật chạy bằng PHP CLI, PDO được chuyển sang database thử trước khi nạp endpoint. Phiên đăng nhập giả lập lưu trong bộ nhớ. Database thử được xóa trong `finally` sau khi chạy.

Các trường hợp đã đạt:

- Tạo sản phẩm nháp.
- Từ chối SKU trùng.
- Manager cập nhật giá và chuyển sản phẩm sang active.
- Đổi ảnh chính đồng bộ `products.image`.
- Xóa ảnh chính chọn lại ảnh đầu còn lại.
- Xóa ảnh cuối đặt ảnh đại diện về NULL.
- Xóa sản phẩm thử.

Đã phát hiện và sửa hai lỗi: đặt ảnh chính chỉ cập nhật thứ tự thư viện; xóa ảnh cuối vẫn giữ đường dẫn ảnh cũ. `ProductImageHelper.php` dùng chung để đồng bộ ảnh đại diện sau thêm/đổi/xóa ảnh trong trang sửa sản phẩm. Không chỉnh dữ liệu sản phẩm thật trong đợt này.

## Giới hạn nghiệm thu

Đây là kiểm thử endpoint và database, không phải tự động thao tác trình duyệt. Ảnh thử là đường dẫn ngẫu nhiên không có tệp: đã kiểm tra liên kết/thứ tự ảnh, chưa kiểm tra upload multipart, xử lý kích thước/định dạng hoặc xóa tệp vật lý. Chưa thử toàn bộ CRUD thành công của danh mục, hãng, dịch vụ, bài viết và dự án trên staging. Các kiểm tra endpoint chung chỉ xác nhận yêu cầu sai bị chặn và trang danh sách dựng được.

Cần tiếp tục nghiệm thu upload thật trên bản triển khai cô lập cả thư mục uploads, giao diện mobile/desktop và việc sản phẩm active/draft xuất hiện đúng ngoài website. Không coi kết quả CLI là đã nghiệm thu toàn bộ website.
