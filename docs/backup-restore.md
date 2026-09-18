# Sao lưu và khôi phục NHAT TRAN

## Công cụ

- `scripts/backup.php`: dump database và sao chép uploads vào một thư mục mới; tạo manifest SHA-256 và số bản ghi từng bảng.
- `scripts/restore-rehearsal.php`: kiểm tra checksum, nhập dump vào database ngẫu nhiên riêng, đối chiếu số bản ghi và phục hồi bản sao uploads. Chỉ xóa database thử do chính lần chạy tạo ra, giữ thư mục kết quả để xem.
- `scripts/backup-common.php`: helper CLI. Tất cả đều chặn HTTP; không nhập dump lên database đang chạy.

## Thực hiện

Chọn nơi lưu ngoài thư mục website, trên ổ được bảo vệ. Thay các đường dẫn dưới đây theo máy:

```powershell
php scripts/backup.php --output=D:/NHATTRAN-backups --mysqldump=C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysqldump.exe
php scripts/restore-rehearsal.php --backup=D:/NHATTRAN-backups/THU-MUC-BAN-SAO --mysql=C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysql.exe --output=D:/NHATTRAN-restore-tests
```

Tạm dừng thao tác ghi (quản trị, đánh giá) trong lúc sao lưu để database và uploads cùng một thời điểm. `--single-transaction` cung cấp snapshot cho InnoDB nhưng không khóa ứng dụng hoặc bảo đảm đồng bộ database với file. Nếu dữ liệu thay đổi, số bản ghi có thể khác khi kiểm tra restore; tạo lại bản sao trong thời gian bảo trì. Không coi snapshot đang lỗi/thiếu manifest là bản sao hoàn chỉnh.

Thông tin kết nối đọc từ config/database.php. Mật khẩu chỉ truyền trong môi trường tiến trình MySQL, không đặt trong đối số hoặc manifest. Không ghi nội dung database/khóa vào log công khai. Tài khoản dùng khôi phục thử cần quyền tạo/xóa database riêng; không cấp quyền này cho tài khoản website chỉ để chạy backup.

Chỉ dùng dump do công cụ này tạo và được giữ trong nơi tin cậy. SQL là mã có thể thực thi: checksum phát hiện hỏng file, không xác thực người tạo nếu cả manifest cũng bị sửa. Công cụ từ chối dump có lệnh chọn/tạo/xóa database hoặc tham chiếu database nguồn để tránh nhập nhầm, nhưng không phải sandbox cho SQL lạ. Với database có view/routine/event hoặc bảng không dùng InnoDB, cần kiểm tra riêng trước khi dùng quy trình này.

## Bản sao và kết quả thử ngày 13/09/2026

- Bản sao: `C:/Users/huuph/AppData/Local/Temp/nhattran-backups/nhattran-20260913-102112-cfc89b53`.
- Đã xác minh 60 file: 1 dump SQL và 59 file uploads.
- Kết quả khôi phục: `C:/Users/huuph/AppData/Local/Temp/nhattran-restore-tests/nhattran_restore_316688dc242f4b3e/restore-report.json`.
- Import database thành công, số bản ghi khớp manifest, 59 file uploads khớp checksum. Database thử đã xóa, database website không bị ghi đè.

**Bản sao này đang trong Temp để thử nghiệm, không phải kho lưu trữ lâu dài.** Sao chép sang ổ/thiết bị lưu trữ riêng có kiểm soát quyền, và giữ thêm một bản ngoài máy trước khi mở website. Windows có thể dọn Temp. Chưa cấu hình lịch tự động, lưu trữ ngoài máy hoặc mã hóa backup trong bước này.

## Khi cần khôi phục website thật

1. Chọn bản sao đã kiểm tra, sao lưu trạng thái hiện tại và đưa website vào bảo trì.
2. Thử phục hồi bằng công cụ rehearsal trước.
3. Chuẩn bị database mới và thư mục triển khai mới; nhập dump/copy uploads bằng quy trình của hosting, không nhập đè khi chưa xác định mục tiêu.
4. Kiểm tra cấu hình kết nối, migration còn thiếu, quyền file, đăng nhập, dữ liệu và đường dẫn ảnh.
5. Chuyển ứng dụng sang bộ dữ liệu đã xác minh; giữ bản cũ để quay lại nếu có lỗi.

Đề xuất vận hành: sao lưu hằng ngày và trước mỗi cập nhật, giữ nhiều phiên bản, kiểm tra restore định kỳ. Lịch và thời gian lưu cần chọn theo dung lượng thực tế và mức mất dữ liệu chấp nhận được.
