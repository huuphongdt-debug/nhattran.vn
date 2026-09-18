<?php
// Requires $product/$id and an active session. Only submissions write to the database.
require_once __DIR__ . '/../../app/Models/ProductReview.php';
$reviewError = '';
$reviewName = is_string($_POST['reviewer_name'] ?? null) ? trim($_POST['reviewer_name']) : '';
$reviewText = is_string($_POST['review_text'] ?? null) ? trim($_POST['review_text']) : '';
$reviewRating = is_scalar($_POST['rating'] ?? null) ? (int) $_POST['rating'] : 0;
$_SESSION['review_csrf'] ??= bin2hex(random_bytes(32));
$reviewNotice = $_SESSION['review_sent'][$id] ?? false;
unset($_SESSION['review_sent'][$id]);
if ($product && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_review'])) {
    $now = time();
    $attempts = array_values(array_filter($_SESSION['review_attempts'] ?? [], static fn($time) => $time > $now - 3600));
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['review_csrf'], $_POST['csrf'])) {
        http_response_code(403);
        $reviewError = 'Phiên gửi đánh giá không hợp lệ. Vui lòng tải lại trang.';
    } elseif (count($attempts) >= 5 || ($attempts && $now - end($attempts) < 60)) {
        http_response_code(429);
        $retryAfter = count($attempts) >= 5 ? max(1, $attempts[0] + 3600 - $now) : max(1, 60 - ($now - end($attempts)));
        header('Retry-After: ' . $retryAfter);
        $reviewError = 'Bạn đang gửi quá nhanh. Vui lòng chờ rồi thử lại sau.';
    } else {
        // Count attempts, including invalid submissions, within this browser session.
        $attempts[] = $now;
        $_SESSION['review_attempts'] = $attempts;
        try {
            (new ProductReview($pdo))->submit($id, $reviewName, $reviewRating, $reviewText);
            $_SESSION['review_sent'][$id] = true;
            header('Location: product-detail.php?id=' . $id . '#danh-gia');
            exit;
        } catch (InvalidArgumentException $e) {
            $reviewError = $e->getMessage();
        } catch (Throwable $e) {
            error_log('Review submission: ' . $e->getMessage());
            $reviewError = 'Chưa gửi được đánh giá. Vui lòng thử lại sau.';
        }
    }
}
