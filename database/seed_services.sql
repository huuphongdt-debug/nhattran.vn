-- Dữ liệu dịch vụ: có cả UPDATE và INSERT; xem điều kiện trước khi dùng trên dữ liệu đang quản lý.
-- SQL chạy thủ công; ghi chú không thay đổi câu lệnh hoặc dữ liệu seed.
-- Dịch vụ hiển thị trên trang chủ. Có thể tiếp tục thêm/sửa trong dashboard.
SET NAMES utf8mb4;

UPDATE services
SET
    name = 'Lập trình PLC',
    slug = 'lap-trinh-plc',
    short_description = 'Lập trình, hiệu chỉnh và tối ưu chương trình PLC phù hợp với nhu cầu vận hành.',
    icon = 'fa-code',
    sort_order = 1,
    status = 'active'
WHERE id = 2;

INSERT INTO services (name, slug, short_description, content, icon, sort_order, status)
VALUES
(
    'Tư vấn giải pháp tự động hóa',
    'tu-van-giai-phap-tu-dong-hoa',
    'Khảo sát nhu cầu và đề xuất giải pháp tự động hóa phù hợp cho từng hệ thống.',
    'Tư vấn lựa chọn thiết bị và phương án triển khai giúp hệ thống vận hành ổn định, hiệu quả.',
    'fa-gears',
    2,
    'active'
),
(
    'Thiết kế và lắp đặt tủ điện',
    'thiet-ke-lap-dat-tu-dien',
    'Thiết kế, lắp đặt tủ điện điều khiển và tủ điện công nghiệp theo yêu cầu.',
    'Thực hiện thiết kế, thi công và kiểm tra tủ điện điều khiển phù hợp với điều kiện vận hành thực tế.',
    'fa-table-cells-large',
    3,
    'active'
),
(
    'Bảo trì và sửa chữa thiết bị',
    'bao-tri-sua-chua-thiet-bi',
    'Kiểm tra, bảo trì và hỗ trợ xử lý sự cố cho thiết bị điện công nghiệp.',
    'Hỗ trợ kiểm tra tình trạng thiết bị, bảo trì định kỳ và xử lý các sự cố kỹ thuật cơ bản.',
    'fa-screwdriver-wrench',
    4,
    'active'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    short_description = VALUES(short_description),
    content = VALUES(content),
    icon = VALUES(icon),
    sort_order = VALUES(sort_order),
    status = VALUES(status);
