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
} catch(Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Xử lý thông báo
$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Xử lý xóa sản phẩm
if(isset($_POST['delete']) && isset($_POST['product_id'])) {
    try {
        $sql = "{CALL DeleteProduct(?)}";
        $params = array($_POST['product_id']);
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if($stmt === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }
        $message = "Xóa sản phẩm thành công!";
    } catch(Exception $e) {
        $error = "Lỗi khi xóa sản phẩm: " . $e->getMessage();
    }
}

// Xử lý lọc và tìm kiếm
$category = isset($_GET['category']) ? $_GET['category'] : 'All';
$minPrice = isset($_GET['minPrice']) ? floatval($_GET['minPrice']) : 0;
$maxPrice = isset($_GET['maxPrice']) ? floatval($_GET['maxPrice']) : PHP_FLOAT_MAX;
$sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'price_asc';

// Lấy danh sách sản phẩm
try {
    $sql = "{CALL GetProductsBySellerAndCategory (?, ?, ?)}";
    $params = array(
        $category !== 'All' ? $category : null,
        $minPrice,
        $maxPrice
    );
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    if($stmt === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }
    
    $products = array();
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $products[] = $row;
    }
    
    // Sắp xếp sản phẩm
    usort($products, function($a, $b) use ($sortBy) {
        switch($sortBy) {
            case 'price_desc':
                return $b['price'] - $a['price'];
            case 'name_asc':
                return strcmp($a['product_name'], $b['product_name']);
            case 'name_desc':
                return strcmp($b['product_name'], $a['product_name']);
            default: // price_asc
                return $a['price'] - $b['price'];
        }
    });
    
} catch(Exception $e) {
    $error = "Lỗi khi lấy danh sách sản phẩm: " . $e->getMessage();
    $products = array();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Databaze</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ee4d2d;
            --hover-color: #d73211;
        }
        
        .navbar {
            background-color: var(--primary-color);
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--hover-color);
            border-color: var(--hover-color);
        }
        
        .action-buttons .btn {
            margin-right: 5px;
        }
        
        .back-to-home {
            margin-bottom: 20px;
        }
        
        .back-to-home a {
            color: var(--primary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .back-to-home a i {
            margin-right: 5px;
        }
        
        .back-to-home a:hover {
            color: var(--hover-color);
        }
        
        .table th {
            background-color: #f8f9fa;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="../../Homepage.php">Databaze</a>
        </div>
    </nav>

    <div class="container">
        <!-- Link về trang chủ -->
        <div class="back-to-home">
            <a href="../../Homepage.php">
                <i class="fas fa-arrow-left"></i> Trở về trang chủ
            </a>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản Lý Sản Phẩm</h2>
            <a href="add-product.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm sản phẩm mới
            </a>
        </div>
        
        <!-- Hiển thị thông báo -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Phần lọc và tìm kiếm -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Danh mục</label>
                    <select name="category" class="form-select">
                        <option value="All" <?= $category === 'All' ? 'selected' : '' ?>>Tất cả</option>
                        <option value="UpperWear" <?= $category === 'UpperWear' ? 'selected' : '' ?>>Áo</option>
                        <option value="LowerWear" <?= $category === 'LowerWear' ? 'selected' : '' ?>>Quần</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Giá từ</label>
                    <input type="number" name="minPrice" class="form-control" value="<?= $minPrice ?>" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Đến</label>
                    <input type="number" name="maxPrice" class="form-control" value="<?= $maxPrice ?>" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sắp xếp</label>
                    <select name="sortBy" class="form-select">
                        <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                        <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                        <option value="name_asc" <?= $sortBy === 'name_asc' ? 'selected' : '' ?>>Tên A-Z</option>
                        <option value="name_desc" <?= $sortBy === 'name_desc' ? 'selected' : '' ?>>Tên Z-A</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>

        <!-- Bảng sản phẩm -->
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open fa-3x mb-3"></i>
                <h3>Không tìm thấy sản phẩm</h3>
                <p>Thử thay đổi điều kiện lọc hoặc thêm sản phẩm mới.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Mô tả</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['product_id']) ?></td>
                            <td><?= htmlspecialchars($product['product_name']) ?></td>
                            <td><?= htmlspecialchars($product['category']) ?></td>
                            <td><?= number_format($product['price'], 0, ',', '.') ?>đ</td>
                            <td><?= htmlspecialchars($product['description'] ?? '') ?></td>
                            <td class="action-buttons">
                                <a href="edit-product.php?id=<?= $product['product_id'] ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <button onclick="confirmDelete(<?= $product['product_id'] ?>)" 
                                        class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
        function confirmDelete(productId) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Bạn không thể hoàn tác sau khi xóa!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = window.location.href;

                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'product_id';
                    input.value = productId;

                    let deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete';
                    deleteInput.value = '1';

                    form.appendChild(input);
                    form.appendChild(deleteInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>