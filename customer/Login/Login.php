<?php
// Cấu hình thông tin kết nối
$serverName = "NGUYENMINHKHANG\MSSQLSERVER04"; // Tên máy chủ SQL Server
$database = "shoppee";       // Tên cơ sở dữ liệu
$uid = "";                   // Tài khoản đăng nhập SQL Server
$pass = "";                  // Mật khẩu đăng nhập SQL Server

// Mảng thông tin kết nối
$connectionOptions = [
    "Database" => $database,
    "Uid" => $uid,
    "PWD" => $pass,
];

// Thực hiện kết nối
$conn = sqlsrv_connect($serverName, $connectionOptions);

// Kiểm tra kết nối
if ($conn === false) {
    die("Kết nối thất bại: " . print_r(sqlsrv_errors(), true));
}

// Khởi động session
session_start();

// Xử lý khi nhận được yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Kiểm tra trong bảng Users
    $sqlUser = "SELECT user_id FROM Users WHERE email = ? AND password = ?";
    $paramsUser = [$username, $password];
    $stmtUser = sqlsrv_query($conn, $sqlUser, $paramsUser);

    if ($stmtUser && sqlsrv_has_rows($stmtUser)) {
        // Lấy user_id từ kết quả truy vấn
        $row = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC);
        $user_id = $row['user_id'];

        // Lưu user_id vào session
        $_SESSION['user_id'] = $user_id;

        // Chuyển hướng đến giao diện User
        header("Location: ../customer/Info/Info.php");
        exit;
    }

    // Thông báo lỗi nếu thông tin không hợp lệ
    echo "<script>alert('Invalid username or password.'); window.location.href='Login.html';</script>";
}

// Đóng kết nối
sqlsrv_close($conn);
?>
