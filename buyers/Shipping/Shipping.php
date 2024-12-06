<?php
session_start();

// Database connection setup
$serverName = "TanThinh";
$database = "shopee";
$uid = ""; // Your SQL Server username
$pass = ""; // Your SQL Server password

$connectionOptions = [
    "Database" => $database,
    "Uid" => $uid,
    "PWD" => $pass,
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Check if session variables are set
if (!isset($_SESSION['order_id']) || !isset($_SESSION['user_id'])) {
    echo "<script>alert('No order or user session found!'); window.location.href='Homepage.php';</script>";
    exit;
}

$order_id = $_SESSION['order_id'];
$user_id = $_SESSION['user_id'];

// Retrieve shipping address from database
$sql = "SELECT address FROM ShippingAddress WHERE user_id = ?";
$params = [$user_id];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Failed to retrieve shipping address: " . print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$shipping_address = $row['address'] ?? '';

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $shipping_partner = $_POST['shipping_partner'];
    $tracking_number = $_POST['tracking_number'];
    $driver = $_POST['driver'];
    $shipping_fee = $_POST['shipping_fee'];

    // Call stored procedure to insert shipping
    $sql = "{CALL InsertShipping(?, ?, ?, ?, ?, ?)}";
    $params = [$order_id, $tracking_number, $shipping_address, $shipping_partner, $driver, $shipping_fee];

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die("Insert shipping failed: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "<script>alert('Shipping information added successfully!'); window.location.href='ShippingConfirmation.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Thông Tin Giao Hàng</title>
    <link rel="stylesheet" href="Shipping.css">
</head>
<body>
    <div class="shipping-container">
        <h1>Thông Tin Giao Hàng</h1>
        <form id="shippingForm">
            <div class="form-group">
                <label for="shipping-fee">Phí giao hàng:</label>
                <input type="number" step="0.01" id="shipping-fee" name="shipping_fee" placeholder="Nhập phí giao hàng" required>
            </div>
            <div class="form-group">
                <label for="shipping-partner">Đối tác giao hàng:</label>
                <select id="payment-method" name="shipping_partner" required>
                    <option value="">-- Chọn đối tác --</option>
                    <option value="VNPost">VNPost</option>
                    <option value="ViettelPost">ViettelPost</option>
                    <option value="J&T Express">J&T Express</option>                
                </select>            
            </div>
            <div class="form-group">
                <label for="tracking-number">Mã vận đơn:</label>
                <input type="text" id="tracking-number" name="tracking_number" placeholder="Nhập mã vận đơn" required>
            </div>
            <div class="form-group">
                <label for="driver">Tên tài xế:</label>
                <select id="payment-method" name="driver" required>
                    <option value="">-- Chọn đối tác --</option>
                    <option value="Messi">Messi</option>
                    <option value="Ronaldo">Ronaldo</option>
                    <option value="Pele">Pele</option>                
                </select>     
            </div>
            <div class="button-group">
                <button type="submit">Thêm Giao Hàng</button>
                <button type="button" id="cancel-button">Hủy</button>
            </div>
        </form>
    </div>
</body>
</html>

