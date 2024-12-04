<?php
// Replace these values with your SQL Server connection details
$serverName = "TanThinh";
$connectionOptions = array(
    "Database" => "shopee",
);

// Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

// Check connection
if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Retrieve values from the form
$email = $_POST['email'];
$password = $_POST['password'];
session_start();
$_SESSION['email'] = $email;

// SQL query to check if the user exists in the Users table with matching email and password
$sql = "SELECT user_id, email, password FROM [shopee].[dbo].[Users] WHERE email = ? AND password = ?";
$params = array($email, $password);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));  // In lỗi chi tiết nếu có lỗi khi thực hiện truy vấn
}

// Fetch the result
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);  // Get the first row of the result

// Kiểm tra nếu $row là một mảng hợp lệ
if (!is_array($row)) {
    // Nếu không có dữ liệu nào
    echo "Login failed. Please check your email and password.";
    sqlsrv_close($conn);
    exit();  // Exit if no rows are returned
} 


// Retrieve user_id from the fetched row
$user_id = $row['user_id'];  // Lấy user_id từ kết quả

// SQL query to check if the user is a seller or buyer
$sqlRole = "SELECT 'seller' AS role FROM [shopee].[dbo].[Sellers] WHERE seller_id = ?
            UNION
            SELECT 'buyer' AS role FROM [shopee].[dbo].[Buyers] WHERE buyer_id = ?";
$paramsRole = array($user_id, $user_id);
$stmtRole = sqlsrv_query($conn, $sqlRole, $paramsRole);

// Check if the query executed successfully
if ($stmtRole === false) {
    die(print_r(sqlsrv_errors(), true));  // In case of query execution error
}

// Fetch the role information
$rowRole = sqlsrv_fetch_array($stmtRole, SQLSRV_FETCH_ASSOC);  // Get the first row of the role query

// Kiểm tra nếu có dữ liệu trong $rowRole
if ($rowRole === false) {
    echo "User found but no role (buyer/seller) assigned.<br>";
    sqlsrv_close($conn);
    exit();  // Exit if no role is found
}

$role = $rowRole['role'];  // Lấy vai trò người dùng
$_SESSION['role'] = $role;  // Set the session role

// Redirect to the appropriate dashboard based on role
if ($role === 'seller') {
    header("Location: sellers/dashboard.php");  // Redirect to seller dashboard
} else {
    header("Location: buyers/dashboard.php");  // Redirect to buyer dashboard
}
exit();

// Close the database connection
sqlsrv_close($conn);
?>
