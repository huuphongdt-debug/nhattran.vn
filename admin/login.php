<?php
/*
 * Trang đăng nhập: phiên đã có admin_user chuyển thẳng về dashboard.
 * POST gửi thông tin đến AuthController::login(); thành công đổi session ID rồi lưu user.
 * Thất bại lấy message hiển thị trong form; giao diện dùng assets/css/login.css.
 */


require_once __DIR__ . '/../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../app/Helpers/AuthHelper.php";
require_once __DIR__ . '/../app/Helpers/CsrfHelper.php';
require_once __DIR__ . '/../app/Models/AccountSecurity.php';

/*
|--------------------------------------------------------------------------
| Nếu đã đăng nhập thì chuyển thẳng đến Dashboard
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['admin_user'])) {

    header("Location: dashboard.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| Kết nối Database
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Auth Controller
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/../app/Controllers/AuthController.php";


$auth = new AuthController($pdo);


/*
|--------------------------------------------------------------------------
| Biến thông báo
|--------------------------------------------------------------------------
*/

$error = "";


/*
|--------------------------------------------------------------------------
| Xử lý đăng nhập
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    requireAdminPost();
    $username = is_string($_POST['username'] ?? null) ? $_POST['username'] : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';


    /*
    |----------------------------------------------------------------------
    | Gọi AuthController
    |----------------------------------------------------------------------
    */

    try {
        $security = new AccountSecurity($pdo);
        if (!$security->allowAttempt($username, $_SERVER['REMOTE_ADDR'] ?? '')) {
            http_response_code(429);
            header('Retry-After: 900');
            $result = ['success' => false, 'message' => 'Quá nhiều lần đăng nhập. Vui lòng chờ 15 phút.'];
        } else {
            $result = $auth->login($username, $password);
        }
    } catch (Throwable $e) {
        error_log('Login failed: ' . $e->getMessage());
        $result = ['success' => false, 'message' => 'Chưa thể đăng nhập. Vui lòng thử lại sau.'];
    }


    /*
    |----------------------------------------------------------------------
    | Đăng nhập thành công
    |----------------------------------------------------------------------
    */

    if ($result['success']) {

        /*
        |------------------------------------------------------------------
        | Tạo session ID mới
        |------------------------------------------------------------------
        */

        session_regenerate_id(true);


        /*
        |------------------------------------------------------------------
        | Lưu thông tin admin vào session
        |------------------------------------------------------------------
        */

        unset($result['user']['password']);
        $_SESSION['admin_user'] = $result['user'];
        unset($_SESSION['admin_csrf']);


        /*
        |------------------------------------------------------------------
        | Chuyển đến Dashboard
        |------------------------------------------------------------------
        */

        header("Location: dashboard.php");

        exit;
    }


    /*
    |----------------------------------------------------------------------
    | Đăng nhập thất bại
    |----------------------------------------------------------------------
    */

    $error = $result['message'];
}

?>


<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Đăng nhập Admin | NHAT TRAN</title>


    <link rel="stylesheet" href="assets/css/login.css">

</head>


<body>


<div class="login-box">


    <!-- =========================
         TITLE
    ========================= -->

    <div class="login-title">

        <h1>
            NHAT TRAN
        </h1>

        <p>
            Hệ thống quản trị website
        </p>

    </div>


    <!-- =========================
         ERROR
    ========================= -->

    <?php if ($error !== ''): ?>

        <div class="error">

            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <!-- =========================
         LOGIN FORM
    ========================= -->

    <form
        method="POST"
        action=""
        autocomplete="off"
    >
        <?= adminCsrfField() ?>


        <!-- USERNAME -->

        <div class="form-group">

            <label for="username">

                Tên đăng nhập

            </label>


            <input
                type="text"
                id="username"
                name="username"
                placeholder="Nhập tên đăng nhập"
                autocomplete="username"
                required
                autofocus
            >

        </div>


        <!-- PASSWORD -->

        <div class="form-group">

            <label for="password">

                Mật khẩu

            </label>


            <input
                type="password"
                id="password"
                name="password"
                placeholder="Nhập mật khẩu"
                autocomplete="current-password"
                required
            >

        </div>


        <!-- BUTTON -->

        <button
            type="submit"
            class="btn-login"
        >

            ĐĂNG NHẬP

        </button>


    </form>


</div>


</body>

</html>
