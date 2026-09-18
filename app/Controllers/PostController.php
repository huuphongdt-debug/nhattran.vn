<?php
/*
 * Lớp gọi model Post cho danh sách, chi tiết, thêm/sửa/xóa và thống kê bài viết.
 * index() dành cho danh sách quản trị; published() dùng cho nội dung công khai.
 * makeSlug() chuẩn hóa chuỗi phục vụ form quản trị.
 */


require_once __DIR__ . "/../Models/Post.php";

class PostController
{
    private Post $post;

    public function __construct(PDO $pdo)
    {
        $this->post = new Post($pdo);
    }


    /*
    |--------------------------------------------------------------------------
    | LẤY TẤT CẢ BÀI VIẾT
    |--------------------------------------------------------------------------
    */

    public function index(): array
    {
        return $this->post->getAll();
    }


    /*
    |--------------------------------------------------------------------------
    | LẤY BÀI VIẾT ĐÃ XUẤT BẢN
    |--------------------------------------------------------------------------
    */

    public function published(): array
    {
        return $this->post->getPublished();
    }


    /*
    |--------------------------------------------------------------------------
    | TÌM BÀI VIẾT THEO ID
    |--------------------------------------------------------------------------
    */

    public function find(int $id): ?array
    {
        return $this->post->findById($id);
    }


    /*
    |--------------------------------------------------------------------------
    | TÌM BÀI VIẾT THEO SLUG
    |--------------------------------------------------------------------------
    */

    public function findBySlug(string $slug): ?array
    {
        return $this->post->findBySlug($slug);
    }


    /*
    |--------------------------------------------------------------------------
    | TẠO BÀI VIẾT
    |--------------------------------------------------------------------------
    */

    public function create(array $data): bool
    {
        return $this->post->create($data);
    }


    /*
    |--------------------------------------------------------------------------
    | CẬP NHẬT BÀI VIẾT
    |--------------------------------------------------------------------------
    */

    public function update(int $id, array $data): bool
    {
        return $this->post->update($id, $data);
    }


    /*
    |--------------------------------------------------------------------------
    | XÓA BÀI VIẾT
    |--------------------------------------------------------------------------
    */

    public function delete(int $id): bool
    {
        return $this->post->delete($id);
    }


    /*
    |--------------------------------------------------------------------------
    | ĐẾM TỔNG BÀI VIẾT
    |--------------------------------------------------------------------------
    */

    public function count(): int
    {
        return $this->post->count();
    }


    /*
    |--------------------------------------------------------------------------
    | ĐẾM BÀI VIẾT ĐÃ XUẤT BẢN
    |--------------------------------------------------------------------------
    */

    public function countPublished(): int
    {
        return $this->post->countPublished();
    }


    /*
    |--------------------------------------------------------------------------
    | TẠO SLUG
    |--------------------------------------------------------------------------
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