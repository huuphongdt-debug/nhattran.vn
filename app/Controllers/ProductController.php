<?php
/*
 * Điểm truy cập model Product cho danh sách, sản phẩm nổi bật, chi tiết và thống kê.
 * Tham số bộ lọc và phân trang cần giữ đồng bộ giữa trang gọi và model.
 */


require_once __DIR__ . "/../Models/Product.php";


class ProductController
{
    private Product $product;


    /*
    |--------------------------------------------------------------------------
    | Khởi tạo
    |--------------------------------------------------------------------------
    */

    public function __construct(PDO $pdo)
    {
        $this->product = new Product($pdo);
    }


    /*
    |--------------------------------------------------------------------------
    | Danh sách sản phẩm
    |--------------------------------------------------------------------------
    */

    public function index(
            string $keyword = '',
            string $status = '',
            int|array $categoryId = 0,
            int $page = 1,
            int $perPage = 20
        ): array {

        return $this->product->getPaginated(
            $keyword,
            $status,
            $categoryId,
            $page,
            $perPage
        );
    }

    /** Lấy các sản phẩm đã được admin đánh dấu nổi bật. */
    public function featured(int $limit = 3): array
    {
        return $this->product->getFeatured($limit);
    }

    /*
    |--------------------------------------------------------------------------
    | Tổng số sản phẩm sau tìm kiếm + lọc
    |--------------------------------------------------------------------------
    */

    public function countPaginated(
            string $keyword = '',
            string $status = '',
            int|array $categoryId = 0
    ): int {

        return $this->product->countPaginated(
            $keyword,
            $status,
            $categoryId
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Chi tiết sản phẩm
    |--------------------------------------------------------------------------
    */

    public function show(int $id): ?array
    {
        return $this->product->findById($id);
    }


    /*
    |--------------------------------------------------------------------------
    | Tổng số sản phẩm
    |--------------------------------------------------------------------------
    */

    public function count(): int
    {
        return $this->product->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Đếm sản phẩm theo trạng thái
    |--------------------------------------------------------------------------
    */

    public function countByStatus(string $status): int
    {
        return $this->product->countByStatus($status);
    }
}
