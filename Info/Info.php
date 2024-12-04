<?php
session_start();

// Kết nối SQL Server
$serverName = "NGUYENMINHKHANG\MSSQLSERVER04";
$database = "shoppee";
$uid = ""; // Username for SQL Server
$pass = ""; // Password for SQL Server

$connectionOptions = [
    "Database" => $database,
    "Uid" => $uid,
    "PWD" => $pass,
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("Kết nối thất bại: " . print_r(sqlsrv_errors(), true));
}

// Kiểm tra session user_id
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Bạn chưa đăng nhập!'); window.location.href='Login.html';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

//Nếu có post thì thực hiện cập nhật
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phonenumber = $_POST['phonenumber'];

    // Gọi stored procedure UpdateUser
    $sql = "{CALL UpdateUser(?, ?, ?, ?, ?)}";
    $params = [$user_id, $firstname, $lastname, $email, $phonenumber];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die("Cập nhật thất bại: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "<script>alert('Cập nhật thông tin thành công!');</script>";
    }
}

// Lấy thông tin người dùng để hiển thị
$sql = "SELECT first_name, last_name, email, phone_number FROM Users WHERE user_id = ?";
$params = [$user_id];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Lấy thông tin thất bại: " . print_r(sqlsrv_errors(), true));
}

$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
?>
<script>    
    // Điền thông tin người dùng vào form
    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("firstname").value = "<?php echo $user['first_name']; ?>";
        document.getElementById("lastname").value = "<?php echo $user['last_name']; ?>";
        document.getElementById("email").value = "<?php echo $user['email']; ?>";
        document.getElementById("phonenumber").value = "<?php echo $user['phone_number']; ?>";
    });
</script>
