<?php
// Kết nối database
$serverName = "TanThinh";
$database = "shopee";
$uid = ""; 
$pwd = ""; 

try {
    $conn = sqlsrv_connect(
        $serverName, 
        array(
            "Database" => $database,
            "Uid" => $uid,
            "PWD" => $pwd
        )
    );
    
    if($conn === false) {
        throw new Exception("Unable to connect to database");
    }

    // Lấy thông tin tổng quan
    $sql_total_products = "SELECT COUNT(*) as total FROM Products";
    $stmt_total = sqlsrv_query($conn, $sql_total_products);
    $total_products = sqlsrv_fetch_array($stmt_total)['total'];

    // Lấy sản phẩm mới nhất
    $sql_latest = "SELECT TOP 5 
        product_id,
        product_name,
        CAST(price AS decimal(10,2)) as price,
        category,
        description
    FROM Products 
    ORDER BY product_id DESC";

    $stmt_latest = sqlsrv_query($conn, $sql_latest);
    $latest_products = [];
    while($row = sqlsrv_fetch_array($stmt_latest, SQLSRV_FETCH_ASSOC)) {
        $latest_products[] = $row;
    }

} catch(Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ Người Bán - Databaze</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ee4d2d;
            --hover-color: #d73211;
        }

        body {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .dashboard-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #dee2e6;
        }

        .action-card:hover {
            background: var(--primary-color);
            color: white;
        }

        .action-card i {
            font-size: 2em;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .action-card:hover i {
            color: white;
        }

        .stats-number {
            font-size: 2em;
            font-weight: bold;
            color: var(--primary-color);
        }

        .table th {
            background-color: #f8f9fa;
        }

        .welcome-message {
            background: linear-gradient(135deg, var(--primary-color), #ff6b6b);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Welcome Section -->
    <div class="welcome-message">
        <h2><i class="fas fa-store"></i> Chào mừng đến với Databaze</h2>
        <p class="mb-0">Quản lý cửa hàng của bạn một cách hiệu quả</p>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="action-card" onclick="parent.loadIframe('./Product/Insert.php')">
            <i class="fas fa-plus-circle"></i>
            <h5>Thêm Sản Phẩm</h5>
            <p>Đăng bán sản phẩm mới</p>
        </div>
        <div class="action-card" onclick="parent.loadIframe('./Product/Update.php')">
            <i class="fas fa-edit"></i>
            <h5>Cập Nhật</h5>
            <p>Chỉnh sửa thông tin sản phẩm</p>
        </div>
        <div class="action-card" onclick="parent.loadIframe('./Product/Delete.php')">
            <i class="fas fa-trash-alt"></i>
            <h5>Xóa Sản Phẩm</h5>
            <p>Gỡ sản phẩm khỏi cửa hàng</p>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4><i class="fas fa-chart-bar"></i> Thống Kê</h4>
                <div class="mt-4">
                    <p>Tổng số sản phẩm</p>
                    <div class="stats-number"><?= $total_products ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4><i class="fas fa-clock"></i> Sản Phẩm Mới Nhất</h4>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Danh mục</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($latest_products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['product_name']) ?></td>
                                <td><?= number_format($product['price'], 0, ',', '.') ?>đ</td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>