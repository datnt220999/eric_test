<?php
class JWT {
    // Hàm mã hóa JWT
    public static function encode($payload, $key) {
        $header = json_encode(["typ" => "JWT", "alg" => "HS256"]);
        $base64Header = self::base64UrlEncode($header);

        $base64Payload = self::base64UrlEncode(json_encode($payload));

        $signature = self::base64UrlEncode(hash_hmac('sha256', "$base64Header.$base64Payload", $key, true));

        return "$base64Header.$base64Payload.$signature";
    }

    // Hàm giải mã JWT
    public static function decode($jwt, $key) {
        $segments = explode('.', $jwt);

        if(count($segments) != 3) {
            throw new Exception('Invalid token format');
        }

        list($base64Header, $base64Payload, $signature) = $segments;

        // Kiểm tra chữ ký
        $validSignature = self::base64UrlEncode(hash_hmac('sha256', "$base64Header.$base64Payload", $key, true));
        if ($signature !== $validSignature) {
            throw new Exception('Invalid signature');
        }

        // Giải mã payload
        $payload = json_decode(self::base64UrlDecode($base64Payload), true);

        return $payload;
    }

    // Hàm mã hóa Base64 URL
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Hàm giải mã Base64 URL
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
