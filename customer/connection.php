<?php
// Cấu hình thông tin kết nối
$serverName = "NGUYENMINHKHANG\MSSQLSERVER04"; // Tên máy chủ SQL Server
$database = "shoppee";       // Tên cơ sở dữ liệu
$uid = "";                       // Tài khoản đăng nhập SQL Server
$pass = "";                      // Mật khẩu đăng nhập SQL Server

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
} else {
    echo "Kết nối thành công đến cơ sở dữ liệu $database!";
}

?>
