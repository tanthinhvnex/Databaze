<!-- ProductRevenue.php -->
<?php
// Start session
session_start();

// Kiểm tra đăng nhập và role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

// Database connection configuration
$serverName = "TanThinh";  // Thay đổi theo cấu hình của bạn
$connectionOptions = array(
    "Database" => "shopee",
);

// Function to get database connection
function getConnection() {
    global $serverName, $connectionOptions;
    try {
        $conn = sqlsrv_connect($serverName, $connectionOptions);
        if($conn === false) {
            throw new Exception("Database connection failed: " . print_r(sqlsrv_errors(), true));
        }
        return $conn;
    } catch(Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Function to get seller info
function getSellerInfo($sellerId) {
    $conn = getConnection();
    $sql = "SELECT s.seller_id, s.shop_name, u.first_name, u.last_name 
            FROM Sellers s 
            JOIN Users u ON s.seller_id = u.user_id 
            WHERE s.seller_id = ?";
    $params = array($sellerId);
    $result = sqlsrv_query($conn, $sql, $params);
    
    if($result === false) {
        die("Error fetching seller info: " . print_r(sqlsrv_errors(), true));
    }
    
    $sellerInfo = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($result);
    sqlsrv_close($conn);
    
    return $sellerInfo;
}

// Function to execute the revenue report procedure
function getRevenueReport($sellerId, $startDate = null, $endDate = null) {
    $conn = getConnection();
    
    // Chuẩn bị tham số
    $params = array(
        array($sellerId, SQLSRV_PARAM_IN),
        array($startDate, SQLSRV_PARAM_IN),
        array($endDate, SQLSRV_PARAM_IN)
    );
    
    // Thực thi procedure
    $sql = "{CALL sp_SellerRevenueByCategory (?, ?, ?)}";
    $stmt = sqlsrv_prepare($conn, $sql, $params);
    
    if (!$stmt) {
        die("Error preparing statement: " . print_r(sqlsrv_errors(), true));
    }
    
    if (!sqlsrv_execute($stmt)) {
        die("Error executing statement: " . print_r(sqlsrv_errors(), true));
    }
    
    $data = array();
    
    // Lấy từng result set
    do {
        $resultSet = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $resultSet[] = $row;
        }
        if (!empty($resultSet)) {
            $data[] = $resultSet;
        }
    } while (sqlsrv_next_result($stmt));
    
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    
    return $data;
}

// Handle form submission
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$sellerId = $_SESSION['user_id'];  // Lấy seller_id từ session
$report = null;
$sellerInfo = null;

// Lấy thông tin seller và report
$sellerInfo = getSellerInfo($sellerId);
if($sellerInfo) {
    $report = getRevenueReport($sellerId, $startDate, $endDate);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo doanh thu theo danh mục</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Báo cáo doanh thu theo danh mục</h1>
        
        <!-- Search Form -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Ngày bắt đầu</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $startDate; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ngày kết thúc</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $endDate; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Xem báo cáo</button>
                </div>
            </div>
        </form>

        <?php if($sellerInfo): ?>
            <!-- Seller Information -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4>Thông tin người bán</h4>
                    <p class="mb-0">
                        <strong>Tên shop:</strong> <?php echo htmlspecialchars($sellerInfo['shop_name']); ?><br>
                        <strong>Người bán:</strong> <?php echo htmlspecialchars($sellerInfo['first_name'] . ' ' . $sellerInfo['last_name']); ?>
                    </p>
                </div>
            </div>

            <!-- Rest of the report display code remains the same -->
            <?php if($report): ?>
                <!-- Category Details -->
                <div class="card mb-4">
                <div class="card-body">
                        <h4>Chi tiết theo danh mục</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Danh mục</th>
                                        <th class="text-end">Số đơn hàng</th>
                                        <th class="text-end">Số sản phẩm đã bán</th>
                                        <th class="text-end">Doanh thu</th>
                                        <th class="text-end">Tỷ lệ doanh thu (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (isset($report) && is_array($report) && !empty($report)) {
                                        $categoryData = isset($report[1]) ? $report[1] : $report[0];
                                        
                                        if (is_array($categoryData)) {
                                            foreach($categoryData as $row): 
                                                // Sử dụng toán tử ?? để trả về giá trị mặc định nếu key không tồn tại
                                                $category = $row['category'] ?? 'N/A';
                                                $orderCount = $row['order_count'] ?? 0;
                                                $itemsSold = $row['total_items_sold'] ?? 0;
                                                $revenue = $row['revenue'] ?? 0;
                                                $revenuePercentage = $row['revenue_percentage'] ?? 0;
                                    ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category); ?></td>
                                                <td class="text-end"><?php echo number_format($orderCount); ?></td>
                                                <td class="text-end"><?php echo number_format($itemsSold); ?></td>
                                                <td class="text-end"><?php echo number_format($revenue); ?> đ</td>
                                                <td class="text-end"><?php echo number_format($revenuePercentage, 2); ?>%</td>
                                            </tr>
                                    <?php 
                                            endforeach;
                                        } else {
                                            echo '<tr><td colspan="5" class="text-center">No data available</td></tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="5" class="text-center">No data available</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h4>Overview</h4>
                        <?php 
                        $summary = isset($report[2][0]) ? $report[2][0] : [];
                        $totalOrders = $summary['total_orders'] ?? 0;
                        $totalItems = $summary['total_items'] ?? 0;
                        $totalRevenue = $summary['total_revenue'] ?? 0;
                        $avgOrderValue = $summary['average_order_value'] ?? 0;
                        ?>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <h6>Total Orders</h6>
                                    <h4><?php echo number_format($totalOrders); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <h6>Total Items Sold</h6>
                                    <h4><?php echo number_format($totalItems); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <h6>Total Revenue</h6>
                                    <h4><?php echo number_format($totalRevenue); ?> đ</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <h6>Average Order Value</h6>
                                    <h4><?php echo number_format($avgOrderValue); ?> đ</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-danger">Không tìm thấy thông tin người bán!</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>