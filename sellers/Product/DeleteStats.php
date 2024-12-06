<?php
session_start();
$seller_id = $_SESSION['user_id'];

$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id'])) {
    $serverName = "TanThinh";
    $connectionInfo = array(
        "Database" => "shopee", 
        "UID" => "",
        "PWD" => ""
    );
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $sql = "{CALL DeleteProduct (?)}";
    $params = array($_POST['product_id']);
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        echo "<script>alert('Xóa sản phẩm thất bại!'); window.location.href='ProductStats.php';</script>";
    } else {
        echo "<script>alert('Xóa sản phẩm thành công!'); window.location.href='ProductStats.php';</script>";
    }
}

// Lấy thông tin sản phẩm nếu có id
if ($product_id) {
    $serverName = "TanThinh";
    $connectionInfo = array(
        "Database" => "shopee", 
        "UID" => "",
        "PWD" => ""
    );
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $sql = "SELECT product_name, price, category FROM Products WHERE product_id = ? AND seller_id = ?";
    $params = array($product_id, $seller_id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $product = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xóa sản phẩm</title>
    <link rel="stylesheet" href="DeleteStats.css">
</head>
<body>
    <div class="container">
        <h2>Xóa sản phẩm</h2>
        <form method="POST">
            <div class="form-group">
                <label>ID Sản phẩm:</label>
                <input type="text" name="product_id" value="<?= htmlspecialchars($product_id) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Tên sản phẩm:</label>
                <input type="text" value="<?= htmlspecialchars($product['product_name']) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Giá:</label>
                <input type="text" value="<?= number_format($product['price'], 0, ',', '.') ?> VND" readonly>
            </div>

            <div class="form-group">
                <label>Danh mục:</label>
                <input type="text" value="<?= htmlspecialchars($product['category']) ?>" readonly>
            </div>

            <div class="warning">
                Bạn có chắc chắn muốn xóa sản phẩm này?
            </div>

            <div class="button-group">
                <button type="submit" class="delete-btn">Xóa</button>
                <button type="button" onclick="window.location.href='ProductStats.php'" class="cancel-btn">Hủy</button>
            </div>
        </form>
    </div>
</body>
</html>