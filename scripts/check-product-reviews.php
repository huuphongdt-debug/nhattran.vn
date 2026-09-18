<?php
// Model integration checks; all fixtures are rolled back.
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/Models/ProductReview.php';
function expectReview(bool $value, string $message): void {
    if (!$value) throw new RuntimeException($message);
}
$pdo->beginTransaction();
try {
    $suffix = bin2hex(random_bytes(8));
    $pdo->prepare("INSERT INTO products(name,slug,status) VALUES (?,?,'active')")->execute(['Review test ' . $suffix, 'review-test-' . $suffix]);
    $product = (int) $pdo->lastInsertId();
    $model = new ProductReview($pdo);
    $model->submit($product, 'Test', 5, 'Review ' . $suffix);
    $id = (int) $pdo->lastInsertId();
    $status = fn() => $pdo->query('SELECT status FROM product_reviews WHERE id=' . $id)->fetchColumn();
    expectReview($status() === 'pending', 'New review must be pending');
    expectReview(!(bool) $pdo->query("SELECT id FROM product_reviews WHERE id=$id AND status='approved'")->fetchColumn(), 'Pending review leaked publicly');
    try {
        $model->submit($product, 'Test', 5, 'Review ' . $suffix);
        throw new RuntimeException('Duplicate accepted');
    } catch (InvalidArgumentException $expected) {}
    try {
        $model->submit($product, 'Test', 6, 'Invalid rating');
        throw new RuntimeException('Invalid rating accepted');
    } catch (InvalidArgumentException $expected) {}
    $model->moderate($id, 'approved'); expectReview($status() === 'approved', 'Approval failed');
    $model->moderate($id, 'hidden'); expectReview($status() === 'hidden', 'Hide failed');
    try {
        $model->moderate($id, 'invalid');
        throw new RuntimeException('Invalid moderation accepted');
    } catch (InvalidArgumentException $expected) {}
    $model->moderate($id, 'delete'); expectReview($status() === false, 'Deletion failed');
    echo "Review moderation, duplicate and validation checks passed; fixtures rolled back.\n";
} finally {
    $pdo->rollBack();
}
