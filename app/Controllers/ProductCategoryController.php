<?php
/*
 * Điều phối danh mục: kiểm tra dữ liệu, tạo/sửa/xóa, cây cha–con và di chuyển thứ tự.
 * tree() dựng cây; getParentCategories() làm phẳng cây với depth cho ô chọn danh mục cha.
 * Khi loại một nhánh khỏi danh sách cha, cần giữ việc loại các cấp con để tránh vòng lặp.
 */


require_once __DIR__ . "/../Models/ProductCategory.php";

class ProductCategoryController
{
    private ProductCategory $category;

    public function __construct(PDO $pdo)
    {
        $this->category = new ProductCategory($pdo);
    }

    /**
     * =====================================================
     * DANH SÁCH DANH MỤC
     *
     * Hỗ trợ:
     * - Tìm kiếm
     * - Lọc danh mục cha / con
     * - Lọc trạng thái
     * - Phân trang
     * =====================================================
     */
    public function index(
        string $keyword = '',
        string $type = 'all',
        string $status = 'all',
        int $page = 1,
        int $perPage = 20
    ): array {

        return $this->category->search(
            $keyword,
            $type,
            $status,
            $page,
            $perPage
        );
    }

    /**
     * Lấy danh mục theo ID
     */
    public function find(int $id): ?array
    {
        return $this->category->findById($id);
    }

/**
 * =====================================================
 * TẠO DANH MỤC
 * =====================================================
 */
public function create(array $data): array
{
    $name = trim($data['name'] ?? '');
    $slug = trim($data['slug'] ?? '');

    /*
     * ================================
     * KIỂM TRA TÊN
     * ================================
     */

    if ($name === '') {

        return [
            'success' => false,
            'message' => 'Vui lòng nhập tên danh mục.'
        ];
    }


    /*
     * ================================
     * TỰ TẠO SLUG
     * ================================
     */

    if ($slug === '') {

        $slug = $this->makeSlug($name);
    }


    /*
     * ================================
     * KIỂM TRA SLUG TRÙNG
     * ================================
     */

    if ($this->category->findBySlug($slug)) {

        return [
            'success' => false,
            'message' => 'Slug danh mục đã tồn tại.'
        ];
    }


    /*
     * ================================
     * DANH MỤC CHA
     * ================================
     */

    $parentId = !empty($data['parent_id'])
        ? (int) $data['parent_id']
        : null;


    /*
     * ================================
     * TẠO DANH MỤC
     *
     * Model sẽ tự tính sort_order
     * ================================
     */

    $id = $this->category->create(
        $name,
        $slug,
        null,
        null,
        $data['status'] ?? 'active',
        $parentId
    );


    return [
        'success' => true,
        'message' => 'Thêm danh mục thành công.',
        'id' => $id
    ];
}

/**
 * =====================================================
 * CẬP NHẬT DANH MỤC
 * =====================================================
 */
public function update(int $id, array $data): array
{
    $category = $this->category->findById($id);

    if (!$category) {

        return [
            'success' => false,
            'message' => 'Không tìm thấy danh mục.'
        ];
    }


    $name = trim($data['name'] ?? '');
    $slug = trim($data['slug'] ?? '');


    /*
     * ================================
     * KIỂM TRA TÊN
     * ================================
     */

    if ($name === '') {

        return [
            'success' => false,
            'message' => 'Vui lòng nhập tên danh mục.'
        ];
    }


    /*
     * ================================
     * TỰ TẠO SLUG
     * ================================
     */

    if ($slug === '') {

        $slug = $this->makeSlug($name);
    }


    /*
     * ================================
     * KIỂM TRA SLUG TRÙNG
     * ================================
     */

    $existing = $this->category->findBySlug($slug);

    if (
        $existing &&
        (int) $existing['id'] !== $id
    ) {

        return [
            'success' => false,
            'message' => 'Slug danh mục đã tồn tại.'
        ];
    }


    /*
     * ================================
     * DANH MỤC CHA
     * ================================
     */

    $parentId = !empty($data['parent_id'])
        ? (int) $data['parent_id']
        : null;


    /*
     * ================================
     * KHÔNG CHO DANH MỤC LÀM CHA
     * CỦA CHÍNH NÓ
     * ================================
     */

    if ($parentId === $id) {

        return [
            'success' => false,
            'message' => 'Danh mục không thể là danh mục cha của chính nó.'
        ];
    }


    /*
     * ================================
     * CẬP NHẬT
     *
     * sort_order sẽ được xử lý
     * tự động ở Model
     * ================================
     */

    $this->category->update(
        $id,
        $name,
        $slug,
        null,
        null,
        $data['status'] ?? 'active',
        0,
        $parentId
    );


    return [
        'success' => true,
        'message' => 'Cập nhật danh mục thành công.'
    ];
}

/**
 * =====================================================
 * LẤY CÂY DANH MỤC ĐANG HOẠT ĐỘNG
 * =====================================================
 */
public function tree(): array
{
    $categories = $this->category->getActiveWithParent();

    $buildBranch = function (?int $parentId) use (&$buildBranch, $categories): array {
        $branch = [];

        foreach ($categories as $category) {
            $categoryParentId = $category['parent_id'] === null
                ? null
                : (int) $category['parent_id'];

            if ($categoryParentId !== $parentId) {
                continue;
            }

            $category['children'] = $buildBranch((int) $category['id']);
            $branch[] = $category;
        }

        return $branch;
    };

    return $buildBranch(null);
}

/**
 * =====================================================
 * LẤY DANH SÁCH DANH MỤC CHA
 *
 * Dùng cho:
 * - category-create.php
 * - category-edit.php
 * =====================================================
 */
public function getParentCategories(?int $excludeId = null): array
{
    $options = [];

    $flatten = function (array $categories, int $depth = 0) use (&$flatten, &$options, $excludeId): void {
        foreach ($categories as $category) {
            if ((int) $category['id'] === $excludeId) {
                continue;
            }

            $category['depth'] = $depth;
            $options[] = $category;

            if (!empty($category['children'])) {
                $flatten($category['children'], $depth + 1);
            }
        }
    };

    $flatten($this->tree());

    return $options;
}


/**
 * =====================================================
 * DI CHUYỂN DANH MỤC
 *
 * direction:
 * - up
 * - down
 * =====================================================
 */
public function move(int $id, string $direction): array
{
    $category = $this->category->findById($id);

    if (!$category) {

        return [
            'success' => false,
            'message' => 'Không tìm thấy danh mục.'
        ];
    }


    if (!in_array($direction, ['up', 'down'], true)) {

        return [
            'success' => false,
            'message' => 'Hướng di chuyển không hợp lệ.'
        ];
    }


    $success = $this->category->move(
        $id,
        $direction
    );


    if (!$success) {

        return [
            'success' => false,
            'message' =>
                $direction === 'up'
                    ? 'Danh mục đã ở vị trí đầu tiên.'
                    : 'Danh mục đã ở vị trí cuối cùng.'
        ];
    }


    return [
        'success' => true,
        'message' => 'Đã thay đổi vị trí danh mục.'
    ];
}

    /**
     * Xóa danh mục
     */
    public function delete(int $id): array
    {
        $category = $this->category->findById($id);

        if (!$category) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy danh mục.'
            ];
        }

        try {

            $this->category->delete($id);

            return [
                'success' => true,
                'message' => 'Xóa danh mục thành công.'
            ];

        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' => 'Không thể xóa danh mục này. Có thể danh mục đang được sử dụng bởi sản phẩm.'
            ];
        }
    }

    /**
     * Tạo slug từ tên
     */
    private function makeSlug(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        $text = preg_replace(
            [
                '/[áàảãạăắằẳẵặâấầẩẫậ]/u',
                '/[éèẻẽẹêếềểễệ]/u',
                '/[íìỉĩị]/u',
                '/[óòỏõọôốồổỗộơớờởỡợ]/u',
                '/[úùủũụưứừửữự]/u',
                '/[ýỳỷỹỵ]/u',
                '/[đ]/u'
            ],
            [
                'a',
                'e',
                'i',
                'o',
                'u',
                'y',
                'd'
            ],
            $text
        );

        $text = preg_replace('/[^a-z0-9]+/u', '-', $text);

        return trim($text, '-');
    }
}
