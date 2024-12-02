 <!-- login.php -->
<?php
// Replace these values with your SQL Server connection details
$serverName = "TanThinh";
$connectionOptions = array(
    "Database" => "shopee"
);

// Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

// Check connection
if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Retrieve values from the form
$username = $_POST['username'];
$password = $_POST['password'];
session_start();
$_SESSION['username'] = $username;
// SQL query to check if the username and password match for staff
$sql = "SELECT * FROM STAFF_ACCOUNT WHERE Username = ? AND Pass = ?";
$params = array($username, $password);
$options = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
$stmt = sqlsrv_query($conn, $sql, $params, $options);

// SQL query to check if the username and password match for customer
$sql1 = "SELECT * FROM CUSTOMER_ACCOUNT WHERE Username = ? AND Pass = ?";
$params1 = array($username, $password);
$options1 = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
$stmt1 = sqlsrv_query($conn, $sql1, $params1, $options1);

// Check if the user is a manager
$sql2 = "SELECT * FROM STAFF_ACCOUNT JOIN STAFF ON STAFF_ACCOUNT.ID = STAFF.ID WHERE username = ? AND Pass = ? AND STAFF.ID IN (SELECT Mgr_id FROM STAFF)";
$params2 = array($username, $password);
$options2 = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
$stmt2 = sqlsrv_query($conn, $sql2, $params2, $options2);

if ($stmt === false && $stmt1 === false && $stmt2 === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (sqlsrv_num_rows($stmt2) > 0) {
    // Login successful
    // Redirect to the staff page
    header("Location: staff/manager/ManageStaff/staff.php");
    exit(); // Ensure that no further code is executed after the redirect
}
else if (sqlsrv_num_rows($stmt) > 0) {
    // Login successful
    // Redirect to the menu page
    header("Location: staff/ManageCustomer/user.php");
    exit(); // Ensure that no further code is executed after the redirect
 } 
else if (sqlsrv_num_rows($stmt1) > 0) {
    // Login successful
    // Redirect to the menu page
    header("Location: customer/menu.php");
    exit(); // Ensure that no further code is executed after the redirect
}

else {
    // Login failed
    echo "Login failed. Please check your username and password.";
 }

// Close the database connection
sqlsrv_close($conn);
?>