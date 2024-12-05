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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phonenumber = $_POST['phonenumber'];
    $sql = "{CALL InsertUser(?, ?, ?, ?, ?)}";
    $params = [$firstname, $lastname, $email, $password, $phonenumber];
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        $errorMessage = "Error occurred during registration.";
        if (!empty($errors)) {
            $errorMessage .= " " . $errors[0]['message'];
        }
        echo "<script>alert('$errorMessage'); window.location.href='Register.html';</script>";
    } else {
        echo "<script>alert('Registration successful!'); window.location.href='Login.html';</script>";
    }
}
sqlsrv_close($conn);
?>