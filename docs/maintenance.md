# Hướng dẫn bảo trì

## Muốn sửa phần nào thì mở file nào?

| Nhu cầu | File |
| --- | --- |
| Tên menu, thứ tự menu, đường dẫn | `public/includes/header.php` |
| Cỡ chữ menu, logo, tìm kiếm, menu điện thoại | `public/css/navigation.css` |
| Đóng/mở menu, hiệu ứng khi cuộn | `public/js/site.js` |
| Nội dung chân trang | `public/includes/footer.php` |
| HTML phần đầu trang nền xanh | `public/includes/hero.php` |
| Màu và bố cục phần đầu trang, trang chi tiết | `public/css/pages.css` |
| Màu nền, biến màu, trang chủ và thành phần nền tảng | `public/css/style.css` |
| Danh mục, thẻ sản phẩm và hộp liên hệ | `public/css/products.css` |
| Thứ tự tải CSS và font | `public/includes/styles.php` |
| JavaScript cần tải trên từng trang | `public/includes/scripts.php` |
| Slideshow và hộp liên hệ trang chủ | `public/js/home.js` |
| Cây danh mục và hộp liên hệ trang sản phẩm | `public/js/products.js` |
| Chọn ảnh trong trang chi tiết sản phẩm | `public/js/pages.js` |
| Escape HTML và chuẩn hóa đường dẫn ảnh | `public/includes/helpers.php` |
| Các hàm dựng trang `siteStart`, `siteHero`, `siteEnd` | `public/includes/site.php` |

`navigation.css` được tải cuối để menu thống nhất ở mọi trang. Cỡ chữ hiện tại là **16px**; menu chuyển sang nút thu gọn ở độ rộng **1200px trở xuống**. Không thêm CSS menu vào `pages.css` hoặc `products.css`.

## Các module admin

### Khung quản trị và dashboard

`admin/dashboard.php` là trang lắp ghép các phần, không chứa truy vấn hoặc khối HTML thống kê dài.

| Phần cần sửa | File |
| --- | --- |
| Danh sách và thứ tự menu quản trị | `admin/includes/navigation.php` |
| Thanh trên cùng, thông tin tài khoản | `admin/includes/topbar.php` |
| Menu bên trái | `admin/includes/sidebar.php` |
| Khởi tạo quyền, URL admin và helper escape | `admin/includes/layout.php` |
| Mở/đóng khung quản trị | `admin/includes/shell-start.php`, `shell-end.php` |
| CSS khung quản trị, sidebar, responsive | `admin/assets/css/layout.css` |
| Font, tiêu đề, nút, form và bảng dùng chung | `admin/assets/css/components.css` |
| Bộ icon SVG dùng chung | `admin/includes/icons.php` |
| Đóng/mở sidebar và phím Escape | `admin/assets/js/layout.js` |
| Truy vấn thống kê dashboard | `app/Models/Dashboard.php` |
| Chuẩn bị dữ liệu và cấu hình thẻ dashboard | `admin/dashboard/data.php` |
| Thẻ thống kê | `admin/dashboard/summary.php` |
| Sản phẩm mới nhất | `admin/dashboard/recent-products.php` |
| Các bản nháp và trạng thái sản phẩm | `admin/dashboard/status.php` |
| Thao tác nhanh | `admin/dashboard/actions.php` |
| CSS riêng dashboard | `admin/assets/css/dashboard.css` |

Các màn hình quản lý đang hoạt động dùng chung sidebar. Trang đăng nhập và các endpoint chỉ xử lý dữ liệu không dùng khung này. Các module Trang, Media và Cài đặt cũ còn là khung PHP chưa có nội dung, chưa được đưa vào menu mới.

`layout.css` nạp `components.css` cho mọi module. Admin dùng font hệ thống Segoe UI/Arial hỗ trợ tiếng Việt, không cần tải font từ CDN. Khi thêm icon, dùng `adminIcon('search')`, `adminIcon('edit')`, `adminIcon('trash')`… thay vì emoji hoặc ký tự ô vuông. Danh sách SVG được định nghĩa ở `icons.php`.

Lưu mã nguồn bằng UTF-8 theo `.editorconfig`. Nếu chạy script chứa tiếng Việt qua PowerShell, phải cấu hình đầu ra UTF-8 hoặc đọc script từ file UTF-8; không đưa nội dung tiếng Việt qua pipeline dùng ASCII. `scripts/check.ps1` có kiểm tra chữ bị thay bằng dấu hỏi trong HTML để phát hiện lỗi này sớm.

Trang danh sách sản phẩm tách xử lý vào `admin/products/products-data.php`, HTML vào `admin/products/views/index.php`. Module dự án tách xử lý form vào `admin/projects/projects-data.php`, giao diện vào `admin/projects/views/index.php`, `form.php` và `list.php`. Giữ file dữ liệu trong thư mục module để các đường dẫn `__DIR__` của upload/controller tiếp tục đúng.

Khi thêm màn hình admin: giữ kiểm tra quyền ở đầu file trước xử lý POST; tải `layout.css` sau CSS riêng; thêm `class="admin-shell"` cho body và nạp `shell-start.php`/`shell-end.php`. `layout.php` chỉ yêu cầu đăng nhập, quyền thao tác cụ thể vẫn do module kiểm tra.

Mỗi module giữ URL PHP cũ. CSS và JavaScript được đặt theo cùng đường dẫn tương đối:

```text
admin/products/product-edit.php
admin/assets/css/products/product-edit.css
admin/assets/js/products/product-edit.js

admin/dashboard.php
admin/assets/css/dashboard.css
admin/assets/js/layout.js
```

CSS dùng chung: `admin/assets/css/admin-common.css`. Module dự án dùng `admin/assets/css/projects/projects.css`. Màn hình xóa hãng có hai trạng thái HTML với hai stylesheet `manufacturers-delete.css` và `manufacturers-delete-2.css`; giữ thứ tự tải tại từng nhánh.

PHP xử lý dữ liệu và render HTML; không nhúng thêm `<style>` hoặc khối JavaScript lớn. Dữ liệu PHP cần cho JS nên truyền qua thuộc tính `data-*`, ví dụ `data-product-edit-url` trên trang sửa sản phẩm. Giữ việc kiểm tra đăng nhập và quyền trước mọi thao tác dữ liệu.

## Thêm một trang công khai

1. Tạo trang trong `public/`, nạp `includes/site.php`.
2. Gọi `siteStart($title, $activeMenu)` trước nội dung, `siteHero(...)` cho phần đầu trang và `siteEnd()` ở cuối.
3. Truy vấn qua model/controller tương ứng; dùng `siteEscape()` khi hiển thị văn bản và `siteAsset()` cho ảnh.
4. Nếu có JavaScript riêng, khai báo trong bảng ánh xạ ở `includes/scripts.php`.
5. Thêm liên kết vào `includes/header.php` khi cần. Trang mới tự nhận font, CSS và menu điện thoại.

Các trang cũ như trang chủ, tin tức và danh sách sản phẩm vẫn giữ phần HTML riêng; chúng đã dùng chung `styles.php`, `scripts.php`, header và footer. Không cần đổi URL để bảo trì giao diện.

## Dữ liệu và tài nguyên

- Không di chuyển `uploads/` tùy ý: đường dẫn ảnh đã được lưu trong database.
- Tài liệu `docs/planning/` là thiết kế ban đầu, có thể khác mã đã triển khai.
- `docs/archive/products2.js` là phiên bản cũ chưa được website sử dụng; mã hiện hành là `public/js/products.js`.
- Module dự án dùng bảng `projects`, `app/Models/Project.php`, `admin/projects/projects.php` và hai trang công khai `projects.php`, `project-detail.php`.
- Việc sắp xếp file không thay đổi schema hay dữ liệu database.

## Kiểm tra sau khi sửa

Chạy từ PowerShell tại thư mục gốc:

```powershell
./scripts/check.ps1 -PhpPath 'C:/laragon/bin/php/php-8.3.33-Win32-vs16-x64/php.exe' -NodePath 'C:/laragon/bin/nodejs/node-v22/node.exe'
```

Nếu PHP và Node đã có trong PATH, chỉ cần `./scripts/check.ps1`.

Công cụ kiểm tra cú pháp PHP/JS và đường dẫn CSS/JS tĩnh. Sau đó mở website để kiểm tra menu ở màn hình rộng và điện thoại, tìm kiếm/lọc sản phẩm, các trang chi tiết và màn hình quản trị vừa sửa. Kiểm tra cú pháp không thay thế kiểm tra giao diện hoặc luồng lưu dữ liệu.
