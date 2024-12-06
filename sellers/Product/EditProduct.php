<?php
session_start();
$seller_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
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

    // Lấy thông tin sản phẩm
    $sql = "SELECT product_name, price, category, description FROM Products WHERE product_id = ? AND seller_id = ?";
    $params = array($product_id, $seller_id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $product = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    // Xử lý form submit
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $description = $_POST['description'];
        $price = $_POST['price'];

        if ($price <= 0 || $price > 20000000) {
            echo "<script>alert('Giá phải nằm trong khoảng từ 0 đến 20.000.000 VND');</script>";
        } else {
            $sql = "{CALL UpdateProductBasic(?, ?, ?)}";
            $params = array($product_id, $description, $price);
            
            $stmt = sqlsrv_query($conn, $sql, $params);
            
            if ($stmt === false) {
                echo "<script>alert('Cập nhật thất bại: " . str_replace("'", "\'", print_r(sqlsrv_errors(), true)) . "');</script>";
            } else {
                echo "<script>
                    alert('Cập nhật thành công!');
                    window.location.href = 'ProductStats.php';
                </script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa sản phẩm</title>
    <link rel="stylesheet" href="EditProduct.css">
</head>
<body>
    <div class="container">
        <h2>Chỉnh sửa sản phẩm</h2>
        <form method="POST">
            <div class="form-group">
                <label>ID Sản phẩm:</label>
                <input type="text" value="<?= htmlspecialchars($product_id) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Tên sản phẩm:</label>
                <input type="text" value="<?= htmlspecialchars($product['product_name']) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Danh mục:</label>
                <input type="text" value="<?= htmlspecialchars($product['category']) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Giá:</label>
                <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" min="0" max="20000000" required>
            </div>

            <div class="form-group">
                <label>Mô tả:</label>
                <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="button-group">
                <button type="submit">Cập nhật</button>
                <button type="button" onclick="window.location.href='ProductStats.php'">Hủy</button>
            </div>
        </form>
    </div>
</body>
</html>