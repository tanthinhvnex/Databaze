<?php
session_start(); // Start the session

// Database connection configuration
$serverName = "NGUYENMINHKHANG\\MSSQLSERVER04"; // SQL Server hostname
$database = "shopee"; // Database name
$uid = ""; // SQL Server username
$pass = ""; // SQL Server password

// Connection options
$connectionOptions = [
    "Database" => $database,
    "Uid" => $uid,
    "PWD" => $pass,
];

// Establish connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

// Check connection
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from form
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Ensure to hash passwords in real applications
    $phonenumber = $_POST['phonenumber'];

    // Insert user via stored procedure
    $sql = "{CALL InsertUser(?, ?, ?, ?, ?)}";
    $params = [$firstname, $lastname, $email, $password, $phonenumber];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        $errors = sqlsrv_errors();
        $errorMessage = "Error occurred during registration.";
        if (!empty($errors)) {
            $errorMessage .= " " . $errors[0]['message'];
        }
        echo "<script>alert('$errorMessage'); window.location.href='Register.html';</script>";
    } else {
        // Fetch the user_id of the registered user
        $sql = "SELECT user_id FROM Users WHERE email = ?";
        $params = [$email];
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            die("Failed to fetch user_id: " . print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($row) {
            $_SESSION['user_id'] = $row['user_id']; // Save user_id in session
            echo "<script>alert('Registration successful!'); window.location.href='Dashboard.php';</script>";
        } else {
            echo "<script>alert('User ID could not be retrieved.'); window.location.href='Register.html';</script>";
        }
    }
}

sqlsrv_close($conn);
?>
