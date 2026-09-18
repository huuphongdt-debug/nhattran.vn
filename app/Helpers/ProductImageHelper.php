<?php
/** Đồng bộ ảnh đại diện với ảnh đầu thư viện sau thao tác thêm/đổi/xóa ảnh. */
function syncProductCover(PDO $pdo, int $productId): void
{
    $query = $pdo->prepare('SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1');
    $query->execute([$productId]);
    $image = $query->fetchColumn();
    $pdo->prepare('UPDATE products SET image = ? WHERE id = ?')->execute([
        $image === false ? null : $image,
        $productId,
    ]);
}
