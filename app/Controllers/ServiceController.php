<?php
/*
 * Lớp trung gian gọi model Service; index() lấy danh sách, active() lấy dịch vụ hoạt động.
 * Các trang quản trị dùng create/update/delete và makeSlug(); trang công khai dùng dữ liệu đọc.
 */


require_once __DIR__ . "/../Models/Service.php";

class ServiceController
{
    private Service $service;

    public function __construct(PDO $pdo)
    {
        $this->service = new Service($pdo);
    }


    /**
     * =====================================================
     * LẤY TẤT CẢ DỊCH VỤ
     * =====================================================
     */
    public function index(): array
    {
        return $this->service->getAll();
    }


    /**
     * =====================================================
     * LẤY DỊCH VỤ ĐANG HOẠT ĐỘNG
     * =====================================================
     */
    public function active(): array
    {
        return $this->service->getActive();
    }


    /**
     * =====================================================
     * TÌM DỊCH VỤ THEO ID
     * =====================================================
     */
    public function find(int $id): ?array
    {
        return $this->service->findById($id);
    }


    /**
     * =====================================================
     * TÌM DỊCH VỤ THEO SLUG
     * =====================================================
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->service->findBySlug($slug);
    }


    /**
     * =====================================================
     * TẠO DỊCH VỤ
     * =====================================================
     */
    public function create(array $data): bool
    {
        return $this->service->create($data);
    }


    /**
     * =====================================================
     * CẬP NHẬT DỊCH VỤ
     * =====================================================
     */
    public function update(int $id, array $data): bool
    {
        return $this->service->update($id, $data);
    }


    /**
     * =====================================================
     * XÓA DỊCH VỤ
     * =====================================================
     */
    public function delete(int $id): bool
    {
        return $this->service->delete($id);
    }


    /**
     * =====================================================
     * ĐẾM TỔNG DỊCH VỤ
     * =====================================================
     */
    public function count(): int
    {
        return $this->service->count();
    }


    /**
     * =====================================================
     * ĐẾM DỊCH VỤ ĐANG HOẠT ĐỘNG
     * =====================================================
     */
    public function countActive(): int
    {
        return $this->service->countActive();
    }


    /**
     * =====================================================
     * TẠO SLUG
     * =====================================================
     */
    public function makeSlug(string $text): string
    {
        $text = trim($text);

        $text = mb_strtolower(
            $text,
            'UTF-8'
        );

        $text = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $text
        );

        $text = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $text
        );

        $text = trim(
            $text,
            '-'
        );

        return $text;
    }
}