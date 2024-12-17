<?php

class AuthMiddleware
{
    public static function checkToken()
    {
        // Lấy header Authorization
        $headers = getallheaders();

        // Kiểm tra nếu header Authorization tồn tại
        if (!isset($headers['Authorization'])) {
            Response::send(400, "Authorization token is required.");
            return false;
        }
        // Lấy token từ header
        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            // Secret key dùng để mã hóa token (cần thay bằng secret key của bạn)
            $secretKey = KEY;  // Đảm bảo rằng KEY là key bí mật của bạn

            // Giải mã token để lấy payload
            $decoded = JWT::decode($token, $secretKey, ['HS256']);

            // Lưu user_id từ token vào session hoặc biến toàn cục để sử dụng sau
            $_SESSION['user_id'] = $decoded['sub'];  // sub là user_id trong payload
            return true;  // Token hợp lệ, cho phép tiếp tục
        } catch (Exception $e) {
            Response::send(401, "Invalid or expired token.");
            return false;
        }
    }
}
