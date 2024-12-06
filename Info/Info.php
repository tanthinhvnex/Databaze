<?php
session_start();

// Kết nối SQL Server
$serverName = "TanThinh";
$database = "shopee";
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

// Nếu có post thì thực hiện cập nhật
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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin người dùng</title>
    <link rel="stylesheet" href="Info.css">
</head>
<body>
    <div class="container">
        <div class="user-image">
            <img src="../Img/user_img.jpg" alt="Ảnh người dùng">
        </div>
        <div class="user-details">
            <div><strong>User ID:</strong> <span id="user_id"><?php echo htmlspecialchars($user_id); ?></span></div>
            <div><strong>Email:</strong> <span id="email"><?php echo htmlspecialchars($user['email']); ?></span></div>
            <div><strong>Họ:</strong> <span id="lastname"><?php echo htmlspecialchars($user['last_name']); ?></span></div>
            <div><strong>Tên:</strong> <span id="firstname"><?php echo htmlspecialchars($user['first_name']); ?></span></div>
            <div><strong>Số điện thoại:</strong> <span id="phone"><?php echo htmlspecialchars($user['phone_number']); ?></span></div>
            <button class="edit-button" onclick="openEditForm()">Chỉnh sửa thông tin</button>
        </div>
    </div>

    <!-- Form chỉnh sửa thông tin -->
    <div id="edit-form" class="edit-form" style="display: none;">
        <form id="userForm" method="POST" action="info.php">
            <label for="edit-email">Email:</label>
            <input type="email" id="edit-email" name="email" required><br>

            <label for="edit-lastname">Họ:</label>
            <input type="text" id="edit-lastname" name="lastname" required><br>

            <label for="edit-firstname">Tên:</label>
            <input type="text" id="edit-firstname" name="firstname" required><br>

            <label for="edit-phone">Số điện thoại:</label>
            <input type="tel" id="edit-phone" name="phonenumber" required><br>

            <button type="submit">Lưu thay đổi</button>
            <button type="button" onclick="closeEditForm()">Hủy</button>
        </form>
    </div>

    <script src="Info.js"></script>
</body>
</html>
