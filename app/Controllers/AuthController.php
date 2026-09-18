<?php
/*
 * Xử lý đăng nhập qua model User; trả kết quả cho admin/login.php.
 * Controller kiểm tra tài khoản/mật khẩu; trang đăng nhập quản lý session và chuyển hướng.
 */


require_once __DIR__ . "/../Models/User.php";

class AuthController
{
    private User $user;

    public function __construct(PDO $pdo)
    {
        $this->user = new User($pdo);
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(string $username, string $password): array
    {
        // Loại bỏ khoảng trắng thừa
        $username = trim($username);

        // Kiểm tra dữ liệu đầu vào
        if ($username === "" || $password === "") {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.'
            ];
        }

        // Tìm user
        $user = $this->user->findByUsername($username);

        // Không tìm thấy user
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.'
            ];
        }

        // Kiểm tra trạng thái tài khoản
        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'Tài khoản hiện không thể đăng nhập.'
            ];
        }

        // Kiểm tra mật khẩu
        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.'
            ];
        }

        // Đăng nhập thành công
        return [
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'user' => $user
        ];
    }
}