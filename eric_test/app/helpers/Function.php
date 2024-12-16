<?php
function logError($message) {
    $user_folder = isset($_SESSION['user_id']) ? $_SESSION['user_id'] . '/' : '';
    $logDirectory = __DIR__ . '/../../logs/' . $user_folder;
    $file = $logDirectory . 'error.log';

    // Kiểm tra xem thư mục logs có tồn tại không, nếu không thì tạo mới
    if (!file_exists($logDirectory)) {
        if (!mkdir($logDirectory, 0777, true)) {
            die("Không thể tạo thư mục logs, vui lòng kiểm tra quyền truy cập.");
        }
    }

    // Kiểm tra xem file error.log có tồn tại không, nếu không thì tạo mới
    if (!file_exists($file)) {
        if (!touch($file)) {
            die("Không thể tạo file error.log, vui lòng kiểm tra quyền truy cập.");
        }
    }

    // Ghi thông báo vào file error.log
    $logMessage = date('Y-m-d H:i:s') . " - " . $message . PHP_EOL;
    if (file_put_contents($file, $logMessage, FILE_APPEND) === false) {
        die("Không thể ghi vào file error.log, vui lòng kiểm tra quyền truy cập.");
    }
}

function dd($var){
    echo json_encode([$var]);
    die;
}
