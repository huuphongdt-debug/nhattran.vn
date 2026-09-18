# Rà soát website NHAT TRAN — 12/09/2026

## Cập nhật ngày 14/09/2026 — thông tin liên hệ

- Đã đồng bộ CTA gọi/Zalo/email, popup, footer và phần liên hệ trang chủ với Settings.
- Thêm mã số thuế, số tài khoản, ngân hàng; tập trung nhãn/default tại config/company.php.
- Có kiểm tra định dạng và transaction khi lưu; không thay đổi cài đặt thật trong lúc triển khai.
- Kiểm tra cấu hình thử và 6 trang HTTP đều đạt; xem company-settings.md.

## Tiến độ xử lý ngày 13/09/2026

### Bước 6 — sao lưu và khôi phục thử

- Đã có công cụ CLI sao lưu database + uploads ngoài web root, manifest SHA-256 và công cụ restore vào database riêng.
- Đã tạo snapshot 60 file và thử phục hồi thành công: số bản ghi khớp, uploads khớp hash. Database thử được xóa sau kiểm tra; dữ liệu website không bị ghi đè.
- Bản thử nằm trong Temp, cần chuyển sang lưu trữ lâu dài và ngoài máy. Chưa cấu hình lịch tự động/mã hóa. Chi tiết và đường dẫn trong backup-restore.md.

### Bước 5 — tài khoản quản trị

- Đã bỏ thông tin mật khẩu mặc định khỏi tiện ích tạo tài khoản; CLI nhận thông tin từ môi trường và không in mật khẩu.
- Đã thêm trang Đổi mật khẩu, kiểm tra mật khẩu hiện tại, xác nhận mật khẩu mới và CSRF; thay session ID khi thành công.
- Bộ đếm database giới hạn tên đăng nhập/IP theo cửa sổ 15 phút, dùng riêng cho đăng nhập và đổi mật khẩu. Bảng mới đã tạo; tài khoản thật giữ nguyên.
- Chuẩn hóa session HttpOnly/SameSite/strict mode và Secure khi HTTPS, xóa cookie khi đăng xuất.
- Kiểm tra fixture rollback: đổi mật khẩu, giới hạn tài khoản/IP và hết hạn đều đạt. Chưa có thu hồi toàn bộ phiên trên thiết bị khác hoặc MFA. Chi tiết vận hành tại account-security.md.

### Bước 4 — tách schema khỏi request

- Đã bỏ gọi helper ALTER TABLE khỏi thêm/sửa sản phẩm và bỏ CREATE TABLE khỏi Settings; helper cũ đã được loại bỏ.
- `scripts/migrate-runtime-schema.php` chỉ chạy CLI, mặc định check; apply thêm cột giá thiếu và bảng settings, không chuyển đổi kiểu giá hiện có một cách tự động.
- Kiểm tra schema local: đã sẵn sàng, không cần thay đổi. Thêm kiểm tra hồi quy DDL trong app/admin/public vào check.ps1.
- Migration/schema nền đầy đủ, tài khoản database production và backup vẫn cần chuẩn bị khi triển khai hosting.

### Bước 3 — kiểm duyệt đánh giá

- Đã bổ sung menu Đánh giá, lọc Chờ duyệt/Đã duyệt/Đã ẩn, phân trang, duyệt/ẩn/xóa bằng POST/CSRF.
- Đánh giá mới lưu pending; bản ghi cũ giữ nguyên. Migration mở rộng enum/default đã áp dụng trên database local.
- Đã bỏ CREATE TABLE khỏi trang sản phẩm; schema chuẩn bị riêng lúc triển khai.
- Giới hạn theo phiên: 60 giây giữa các lần gửi, tối đa 5 lần/giờ; chặn nội dung trùng cùng tên/sản phẩm trong 24 giờ. Đây là hạn chế cơ bản, chưa thay thế chống bot ở lớp máy chủ.
- Kiểm tra model dùng transaction rollback; kiểm tra CSRF/rate limit không ghi dữ liệu. Trang sản phẩm ID 3 trả HTTP 200 không có cảnh báo PHP.
- Xem `docs/review-moderation.md` để vận hành và triển khai sang database khác.

### Bước 2 — POST và CSRF cho các module còn lại

- Các form thêm/sửa sản phẩm, danh mục, dịch vụ, bài viết, banner và Settings kiểm tra CSRF trước xử lý.
- Xóa sản phẩm/danh mục/dịch vụ/bài viết và di chuyển danh mục chỉ chấp nhận POST; tham số lấy từ POST.
- Nút thao tác trên cả bảng và giao diện mobile dùng form POST thật; giữ các tham số bộ lọc khi di chuyển danh mục.
- Banner đổi trạng thái/xóa dùng POST, không còn xử lý các tham số GET để ghi dữ liệu.
- JavaScript đặt ảnh chính/xóa ảnh sản phẩm gửi kèm CSRF.
- Kiểm tra: 7 trường hợp helper, 23 yêu cầu endpoint bị từ chối và 6 trang danh sách có form/token đều đạt. Không gửi yêu cầu xóa hoặc cập nhật dữ liệu thật. Chưa nghiệm thu trực quan trên trình duyệt hoặc thử CRUD thành công trên database staging.
- Các vấn đề đánh giá công khai, migration, mật khẩu mặc định và cấu hình production vẫn còn trong danh sách cần xử lý.

- Đã bảo vệ endpoint xóa hãng bằng requireManager(), POST và CSRF; chuyển nút xóa hãng sang form có token.
- Đã giới hạn create-admin.php và admin/create-manager.php chỉ chạy CLI; truy cập HTTP trả 404 trước khi kết nối database.
- Đã thêm CsrfHelper.php và scripts/check-admin-security.php: 7 trường hợp quyền/phương thức/token đều đạt, không ghi database.
- HTTP khách chưa đăng nhập vào xóa hãng chuyển về login; chưa thử xóa hãng thật.
- File manufacturers-delete-2.css hiện đã có; kiểm tra tài nguyên không còn báo thiếu.
- Kiểm tra hiện tại: 100 PHP, 9 JavaScript, 70 tham chiếu tài nguyên và mã hóa đều đạt.
- Các mục POST/CSRF ở module khác, đánh giá, migration, cấu hình triển khai và nội dung bên dưới vẫn còn phải xử lý. Mật khẩu của các tài khoản hiện có chưa được thay đổi.

## Kết luận

Website hiện là hệ thống giới thiệu doanh nghiệp và danh mục thiết bị, có CMS quản lý phần lớn nội dung. Có thể tiếp tục nhập dữ liệu và nghiệm thu trên môi trường nội bộ. Chưa nên đưa nguyên trạng lên Internet trước khi xử lý các mục ưu tiên P0 bên dưới. Chưa phải hệ thống bán hàng có giỏ hàng, đơn hàng và thanh toán; các chức năng đó không bắt buộc nếu mục tiêu là nhận liên hệ/báo giá.

Đợt này chỉ kiểm tra và lập báo cáo, không sửa chức năng, tạo tài khoản, gửi đánh giá hoặc xóa dữ liệu.

## Phạm vi và bằng chứng

- Rà mã ứng dụng `app`, các trang `admin`, `public`, SQL, cấu hình thư mục và công cụ kiểm tra.
- Đọc số lượng và trạng thái dữ liệu MySQL hiện tại; kiểm tra đường dẫn ảnh đang tham chiếu.
- Kiểm tra cú pháp: 98 file PHP, 9 file JavaScript đạt; kiểm tra mã hóa 74 trang PHP đạt.
- Kiểm tra tham chiếu CSS/JS: 70 tham chiếu, thiếu 1 file `admin/assets/css/manufacturers/manufacturers-delete-2.css`.
- HTTP local: trang chủ, giới thiệu, sản phẩm, dịch vụ, dự án, tin tức, liên hệ, hỗ trợ trả 200, không phát hiện chuỗi lỗi PHP trong HTML. Chi tiết sản phẩm với ID 0 trả 404.
- Không chạy các endpoint xóa, tiện ích tạo tài khoản, form ghi dữ liệu hoặc trang có DDL để kiểm tra chức năng.
- Chưa nghiệm thu trực quan toàn bộ màn hình bằng trình duyệt, chưa kiểm thử CRUD end-to-end, chưa đo tải đồng thời/Core Web Vitals, chưa xác minh cấu hình hosting/HTTPS/backup thật.
- HTTP 200 và lint không chứng minh toàn bộ luồng nghiệp vụ đã đúng.

## Dữ liệu hiện tại

| Loại | Số lượng | Ghi nhận |
| --- | ---: | --- |
| Sản phẩm | 9 | Cả 9 active; 4 chưa có ảnh chính lẫn ảnh thư viện |
| Danh mục sản phẩm | 33 | Có cấu trúc cha–con |
| Hãng sản xuất | 16 | Có logo; đường dẫn Schneider chứa ký tự mã hóa URL, file giải mã tồn tại |
| Nhóm hãng | 3 | Phân loại thương hiệu dùng cho quản trị và trang chủ |
| Dịch vụ | 4 | Cả 4 active |
| Dự án | 1 | Đã công bố |
| Bài viết | 3 | Cả 3 published |
| Banner | 3 | Cả 3 active |
| Tài khoản | 2 | Một admin, một manager; đều active |
| Đánh giá sản phẩm | 2 | Có bảng lưu dữ liệu, chưa thấy màn hình quản lý đánh giá |

Sản phẩm thiếu ảnh: ID 9, 10, 11, 12. Ba sản phẩm ID 3, 5, 6 cùng tên PLC Mitsubishi FX5U: cần xác minh là biến thể thực hay dữ liệu thử, không tự kết luận trùng và xóa.

## Website người dùng

| Khu vực | Đã có | Còn thiếu hoặc cần nghiệm thu |
| --- | --- | --- |
| Trang chủ | Banner, danh mục, sản phẩm nổi bật, nhóm thương hiệu, dịch vụ, bài viết, liên hệ | Nội dung thực tế, ảnh sản phẩm; một số số điện thoại/Zalo/email còn viết cố định |
| Menu | Điều hướng các trang, tìm sản phẩm, menu mobile | Kiểm thử bàn phím, màn hình nhỏ, trường hợp tên dài |
| Giới thiệu | Nội dung doanh nghiệp, ảnh WebP nhiều kích thước | Chưa sửa được nội dung này từ CMS Pages |
| Sản phẩm | Từ khóa, cây danh mục, lọc danh mục, phân trang, liên hệ | Chưa thấy bộ lọc hãng/nhóm hãng cho khách; kiểm tra sản phẩm ở danh mục con |
| Chi tiết sản phẩm | Thông tin, SKU, hãng/danh mục, ảnh, giá hoặc báo giá, đánh giá | Duyệt/chống spam đánh giá; kiểm tra giá khuyến mãi và ảnh chính sau khi sửa |
| Thương hiệu | Toàn bộ hãng theo nhóm, logo và tên | Chưa có trang hãng riêng hoặc lọc sản phẩm khi chọn logo |
| Dịch vụ | Danh sách và chi tiết dịch vụ hoạt động | Nội dung kỹ thuật thực, hình ảnh và hành động liên hệ theo dịch vụ |
| Dự án | Danh sách, chi tiết, khách hàng, địa điểm, ảnh đại diện | Mới 1 hồ sơ; chưa thấy thư viện nhiều ảnh/tài liệu riêng cho dự án |
| Tin tức | Danh sách và chi tiết bài công bố | Chưa thấy danh mục tin, tìm bài, quản lý SEO từng bài |
| Liên hệ | Gọi điện, Zalo, email, bản đồ, thông tin công ty | Chưa có quy trình gửi/lưu/theo dõi yêu cầu báo giá trên hệ thống |
| Chat nhanh | Hộp chọn kênh liên hệ | Đây là liên kết Zalo/email, không phải chat trực tuyến có lịch sử hội thoại |
| Hỗ trợ | Nội dung bảo hành/đổi trả và các chủ đề hỗ trợ trong mã | Chưa có CMS chỉnh nội dung; cần chủ doanh nghiệp duyệt lại nội dung công bố |

## Dashboard quản trị

| Module | Đã có | Khoảng trống |
| --- | --- | --- |
| Đăng nhập/đăng xuất | Kiểm tra mật khẩu hash, trạng thái tài khoản, đổi session ID khi đăng nhập | Chưa thấy đổi/quên mật khẩu, khóa tạm khi sai nhiều lần, nhật ký đăng nhập |
| Phân quyền | Admin/manager, Settings chỉ admin | Bảo vệ endpoint chưa đồng đều; đặc biệt xóa hãng |
| Tổng quan | Số lượng module, sản phẩm mới, trạng thái, thao tác nhanh | Là thống kê nội dung, không phải lượt truy cập/doanh thu/chuyển đổi |
| Sản phẩm | CRUD, danh mục/hãng, giá, trạng thái, nổi bật, ảnh và ảnh chính, tìm/lọc/phân trang | CSRF, kiểm thử lưu ảnh và giá, kiểm tra dữ liệu bắt buộc/giới hạn; chưa có quản lý đánh giá |
| Danh mục | CRUD, cha–con, thứ tự lên/xuống, bộ lọc | Xóa/di chuyển qua GET; cần kiểm thử đổi cha và xử lý sản phẩm khi xóa |
| Hãng | CRUD logo/tên/slug, số sản phẩm, lọc nhóm | Endpoint xóa thiếu kiểm tra quyền; thiếu CSS ở nhánh báo lỗi |
| Nhóm hãng | CRUD nhóm, gán nhiều hãng, hãng thuộc nhiều nhóm, CSRF | Cần kiểm thử nhóm bị xóa giữa lúc mở form; có kiểm tra ID ném lỗi ngoài try trong groups.php |
| Dịch vụ | CRUD, slug, icon, nội dung, trạng thái, thứ tự | Chưa CSRF thống nhất; cần kiểm tra đồng bộ icon public/admin |
| Dự án | CRUD, nháp/công bố, nổi bật, ảnh chuyển WebP, CSRF | Nhiều ảnh, tài liệu đính kèm chưa có; cần kiểm thử ảnh thay thế/dọn ảnh |
| Bài viết | CRUD, slug, tóm tắt/nội dung/ảnh, trạng thái, ngày công bố, thứ tự | Chưa CSRF thống nhất, chưa phân loại bài/SEO riêng |
| Banner | Thêm/sửa/upload, ẩn/hiện, xóa, CTA, thứ tự | Xóa và đổi trạng thái qua GET, chưa CSRF; quản lý file ảnh cũ cần rà |
| Cài đặt | Tên/mô tả/địa chỉ, điện thoại, email, Zalo, Facebook, website, bản đồ | Chưa bao phủ thông tin cố định trong mọi trang; chưa CSRF |
| Media | File khởi tạo quyền/kết nối | Chưa có thư viện media, upload chung, tìm ảnh hoặc quản lý file đang dùng |
| Pages | Hai file khởi tạo | Chưa có danh sách trang hay form sửa nội dung |
| Tài khoản | Hai vai trò hiện có | Không có module tạo/sửa/khóa user hoặc đổi mật khẩu trong menu |
| Yêu cầu báo giá | Chưa thấy module/bảng tương ứng | Chưa lưu khách hàng, nội dung hỏi, trạng thái xử lý hoặc người phụ trách |

## P0 — xử lý trước khi công khai website

1. **Bảo vệ endpoint xóa hãng**: `admin/manufacturers/manufacturers-delete.php` nạp database và xử lý ID/DELETE nhưng không gọi helper phân quyền. Việc menu nằm trong dashboard không bảo vệ URL trực tiếp. Không thử xóa để chứng minh lỗi.
2. **Loại tiện ích khởi tạo tài khoản khỏi bản triển khai công khai**: `create-admin.php`, `admin/create-manager.php` chứa thông tin mặc định và có thao tác ghi database. Không coi chúng là module quản lý tài khoản. Đổi thông tin mặc định nếu đã dùng; chưa kiểm chứng mật khẩu thực tế của hai tài khoản.
3. **Thống nhất POST và CSRF cho thao tác ghi**: sản phẩm, danh mục, dịch vụ, bài viết, banner, settings chưa có token như dự án/nhóm hãng. Các thao tác xóa và đổi trạng thái qua GET cần chuyển sang form POST kiểm tra quyền và token phía máy chủ.
4. **Kiểm soát đánh giá công khai**: `public/product-detail.php` INSERT đánh giá với mặc định approved; chưa thấy chống spam/rate limit hay CMS duyệt. Bổ sung trạng thái chờ duyệt và quản trị, hoặc tạm tắt nhận đánh giá khi mở web.
5. **Tách migration khỏi request**: `ProductPricingHelper.php` chạy ALTER TABLE; `settings.php` và `product-detail.php` có CREATE TABLE trong luồng trang. Chuyển sang bước triển khai riêng rồi giới hạn quyền tài khoản database chạy web.
6. **Kiểm tra cấu hình production**: HTTPS, cookie Secure/HttpOnly/SameSite, tắt hiển thị lỗi chi tiết, lưu log nội bộ, quyền file/thư mục, cấm thực thi script trong uploads, chặn truy cập nguồn SQL/tài liệu/file tạm. Không thấy cấu hình triển khai tương ứng trong repo; hosting có thể cấu hình ngoài repo nên cần xác minh thực tế.
7. **Sao lưu và khôi phục thử**: cả database và uploads. Thư mục database hiện là các phần migration/seed, không phải bộ schema đầy đủ có thể dựng mọi bảng từ máy trắng. Phải có bản xuất/schema và quy trình restore đã kiểm tra.
8. **Sửa lỗi tài nguyên và dữ liệu công bố**: thiếu CSS nhánh xóa hãng; thêm ảnh cho 4 sản phẩm; xác nhận tên/model/SKU, giá, số điện thoại và nội dung doanh nghiệp.
9. **Nghiệm thu nghiệp vụ trên bản sao dữ liệu**: thêm/sửa/xóa, quyền admin/manager/khách, CSRF, giá/ảnh/trạng thái, tìm kiếm và 404. Không dùng dữ liệu thật để thử thao tác phá hủy.

## P1 — nên làm trước khi giới thiệu rộng rãi

- Gom mọi thông tin liên hệ về Settings. Hiện footer/partial dùng cấu hình, nhưng popup trang chủ, một số CTA và thông tin pháp lý vẫn viết trực tiếp trong PHP.
- SEO: thêm mô tả riêng từng trang, canonical, Open Graph, sitemap, robots và dữ liệu có cấu trúc phù hợp. Trong mã hiện tại chỉ thấy meta description rõ ở trang chủ; chưa thấy các file sitemap/robots hoặc cấu hình SEO đầy đủ.
- Chuẩn hóa URL/base path: nhiều chỗ cố định `/NHATTRAN/`; thử lại khi chuyển sang domain gốc hoặc document root khác.
- Tối ưu ảnh theo dữ liệu thực: còn ảnh khoảng 1,7–2,1 MB; có PNG khoảng 48,78 MB và ZIP 120,85 MB trong uploads. Hai file lớn là tài nguyên lưu trữ, chưa chứng minh được tải bởi trang hiện hành; cần kiểm tra tham chiếu trước khi dọn.
- Cân nhắc phục vụ font/icon cục bộ; public vẫn phụ thuộc Google Fonts và CDN Font Awesome. Kiểm tra giao diện khi mạng chậm hoặc CDN không tải được.
- Đo tốc độ trang chủ/danh sách/chi tiết trên mobile, cache tài nguyên, nén HTTP; không kết luận tốc độ chỉ từ kích thước mã.
- Thiết lập theo dõi lỗi và thống kê truy cập/chuyển đổi; dashboard hiện chưa thể hiện các chỉ số này.
- Duyệt nội dung giới thiệu, chính sách, địa chỉ, thông tin giao dịch và quyền sử dụng ảnh/logo trước khi công bố. Báo cáo này không xác minh nội dung pháp lý.

## P2 — bổ sung theo nhu cầu vận hành

- CMS Pages cho giới thiệu/chính sách, thư viện Media dùng chung.
- Module tài khoản: đổi mật khẩu, khóa tài khoản, nhật ký thao tác; cân nhắc xác thực nhiều lớp.
- Tiếp nhận và theo dõi yêu cầu báo giá; email thông báo khi có khách liên hệ.
- Lọc sản phẩm theo hãng/nhóm hãng, trang thương hiệu, tài liệu kỹ thuật/download.
- Danh mục tin tức, tìm bài, thư viện ảnh dự án.
- Giỏ hàng/đơn hàng/thanh toán chỉ cần nếu chuyển sang bán trực tiếp trên website.

## Thứ tự triển khai đề xuất

1. Chốt phạm vi ra mắt: website giới thiệu + catalogue + liên hệ báo giá.
2. Xử lý P0 về quyền, CSRF, script tài khoản, đánh giá và migration.
3. Hoàn thiện nội dung/ảnh, sửa CSS thiếu và đồng bộ cài đặt.
4. Tạo staging giống hosting, xuất dữ liệu và thử khôi phục.
5. Nghiệm thu các luồng bằng hai vai trò và khách chưa đăng nhập; mobile/desktop.
6. Hoàn thiện SEO/tốc độ cơ bản, HTTPS/log/backup rồi mới trỏ domain công khai.

Không cần chờ hoàn thành mọi mục P2 để mở một website catalogue, nhưng không nên bỏ qua các mục P0 vì giao diện đã hiển thị đẹp.
