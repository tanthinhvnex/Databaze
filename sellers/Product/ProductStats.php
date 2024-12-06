<?php
// Xử lý form tìm kiếm sản phẩm
$category = isset($_GET['category']) ? $_GET['category'] : 'All';
$minPrice = isset($_GET['minPrice']) ? floatval($_GET['minPrice']) : 0;
$maxPrice = isset($_GET['maxPrice']) ? floatval($_GET['maxPrice']) : 20000000;
$sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'price_asc';

// Khởi tạo mảng errors và messages
$errors = [];
$messages = [];

// Kiểm tra tính hợp lệ của các tham số
if (!in_array($category, ['All', 'UpperWear', 'LowerWear'])) {
    $errors[] = "Danh mục không hợp lệ.";
}

if (!is_numeric($minPrice) || $minPrice < 0) {
    $errors[] = "Giá tối thiểu không hợp lệ.";
}

if (!is_numeric($maxPrice) || $maxPrice < 0) {
    $errors[] = "Giá tối đa không hợp lệ.";
}

if (!in_array($sortBy, ['price_asc', 'price_desc'])) {
    $errors[] = "Tiêu chí sắp xếp không hợp lệ.";
}

if (count($errors) === 0) {
    // Khởi tạo kết nối đến cơ sở dữ liệu
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

    // Lấy danh sách sản phẩm
    try {
        if ($category !== 'All') {
            $sql = "{CALL GetProductsBySellerAndCategory (?, ?, ?)}";
            $params = array(
                $category,
                $minPrice,
                $maxPrice
            );
        } else {
            $sql = "{CALL GetProductsWithoutCategory (?, ?)}";
            $params = array(
                $minPrice,
                $maxPrice
            );
        }

        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        $products = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $products[] = $row;
        }

        // Sắp xếp sản phẩm theo tiêu chí
        usort($products, function($a, $b) use ($sortBy) {
            if ($sortBy === 'price_desc') {
                return $b['price'] - $a['price'];
            } else {
                return $a['price'] - $b['price'];
            }
        });

    } catch (Exception $e) {
        $errors[] = "Lỗi khi lấy danh sách sản phẩm: " . $e->getMessage();
        $products = [];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Databaze</title>
    <link rel="stylesheet" href="./ProductStats.css">
</head>
<body>
    <div class="container">
        <!-- Hiển thị thông báo nếu có lỗi -->
        <?php if (count($errors) > 0): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Hiển thị thông báo nếu có thành công -->
        <?php if (count($messages) > 0): ?>
            <div class="alert alert-success">
                <?php foreach ($messages as $message): ?>
                    <p><?= $message ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Form tìm kiếm sản phẩm -->
        <form method="GET">
            <div class="form-group">
                <label for="category">Danh mục:</label>
                <select name="category" id="category">
                    <option value="All" <?= $category == 'All' ? 'selected' : '' ?>>Tất cả</option>
                    <option value="UpperWear" <?= $category == 'UpperWear' ? 'selected' : '' ?>>Áo</option>
                    <option value="LowerWear" <?= $category == 'LowerWear' ? 'selected' : '' ?>>Quần</option>
                </select>
            </div>
            <div class="form-group">
                <label for="minPrice">Giá tối thiểu:</label>
                <input type="number" name="minPrice" id="minPrice" value="<?= htmlspecialchars($minPrice) ?>" min="0">
            </div>
            <div class="form-group">
                <label for="maxPrice">Giá tối đa:</label>
                <input type="number" name="maxPrice" id="maxPrice" value="<?= htmlspecialchars($maxPrice) ?>" min="0">
            </div>
            <div class="form-group">
                <label for="sortBy">Sắp xếp theo:</label>
                <select name="sortBy" id="sortBy">
                    <option value="price_asc" <?= $sortBy == 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                    <option value="price_desc" <?= $sortBy == 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                </select>
            </div>
            <button type="submit">Tìm kiếm</button>
        </form>

        <!-- Hiển thị danh sách sản phẩm nếu không có lỗi -->
        <?php if (count($products) > 0): ?>
            <div class="product-table">
                <table>
                    <thead>
                        <tr>
                            <th>Tên sản phẩm</th>
                            <th>Giá</th>
                            <th>Danh mục</th>
                            <th>Cửa hàng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['product_name']) ?></td>
                                <td><?= number_format($product['price'], 0, ',', '.') ?> VND</td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                                <td><?= htmlspecialchars($product['shop_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (count($errors) === 0): ?>
            <p>Không có sản phẩm nào phù hợp với tìm kiếm của bạn.</p>
        <?php endif; ?>
    </div>
</body>
</html>