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

// SQL query to check if the user exists
$sql = "SELECT user_id, email, password FROM [shopee].[dbo].[Users] WHERE email = ? AND password = ?";
$params = array($email, $password);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the result
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!is_array($row)) {
    echo "Login failed. Please check your email and password.";
    sqlsrv_close($conn);
    exit();
}

// Retrieve and store user_id
$user_id = $row['user_id'];
$_SESSION['user_id'] = $user_id;

// Check role and get specific ID (seller_id or buyer_id)
$sqlRole = "SELECT 
    CASE 
        WHEN s.seller_id IS NOT NULL THEN 'seller' 
        ELSE 'buyer' 
    END as role,
    COALESCE(s.seller_id, b.buyer_id) as specific_id
FROM [shopee].[dbo].[Users] u
LEFT JOIN [shopee].[dbo].[Sellers] s ON u.user_id = s.seller_id
LEFT JOIN [shopee].[dbo].[Buyers] b ON u.user_id = b.buyer_id
WHERE u.user_id = ?";

$paramsRole = array($user_id);
$stmtRole = sqlsrv_query($conn, $sqlRole, $paramsRole);

if ($stmtRole === false) {
    die(print_r(sqlsrv_errors(), true));
}

$rowRole = sqlsrv_fetch_array($stmtRole, SQLSRV_FETCH_ASSOC);

if ($rowRole === false) {
    echo "User found but no role (buyer/seller) assigned.<br>";
    sqlsrv_close($conn);
    exit();
}

// Store role and specific ID in session
$role = $rowRole['role'];
$specific_id = $rowRole['specific_id'];
$_SESSION['role'] = $role;
$_SESSION['specific_id'] = $specific_id;

// Redirect based on role
if ($role === 'seller') {
    header("Location: sellers/Nav_bar.html");
} else {
    header("Location: buyers/Nav_bar.html");
}
exit();

sqlsrv_close($conn);
?>