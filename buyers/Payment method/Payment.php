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
if (!isset($_SESSION['order_id'])) {
    echo "<script>alert('Bạn chưa mua hàng!'); window.location.href='Login.html';</script>";
    exit;
}
$order_id = $_SESSION['order_id'];

// Xử lý yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payment_method = $_POST['payment_method'];
    $ref_code = $_POST['ref_code'] ?? null;
    $status = $_POST['status'];

    // Gọi stored procedure InsertPayment
    $sql = "{CALL InsertPayment(?, ?, ?, ?)}";
    $params = [$order_id, $ref_code,$payment_method];

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die("Thêm thanh toán thất bại: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "<script>alert('Thêm thanh toán thành công!'); window.location.href='Payment.html';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán</title>
    <link rel="stylesheet" href="Payment.css">
</head>
<body>
    <div class="payment-container">
        <h1>Thanh Toán</h1>
        <form id="paymentForm" action="processPayment.php" method="POST">
            <div class="form-group">
                <label for="payment-method">Phương thức thanh toán:</label>
                <select id="payment-method" name="payment_method" required>
                    <option value="">-- Chọn phương thức --</option>
                    <option value="bank transfer">Chuyển khoản ngân hàng</option>
                    <option value="cash">Tiền mặt</option>
                    <option value="PayPal">PayPal</option>                
                </select>
            </div>
            <div class="form-group">
                <label for="ref-code">Mã tham chiếu (nếu có):</label>
                <input type="text" id="ref-code" name="ref_code" placeholder="Nhập mã tham chiếu">
            </div>
            <button type="submit">Xác nhận thanh toán</button>
        </form>
    </div>
</body>
</html>
