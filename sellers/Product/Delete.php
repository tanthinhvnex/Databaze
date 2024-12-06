<?php

// Database connection
$serverName = "TanThinh";
$database = "shopee";
$uid = ""; // Your SQL Server username
$pass = ""; // Your SQL Server password

$connectionOptions = [
    "Database" => $database,
    "Uid" => $uid,
    "PWD" => $pass,
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productID = $_POST['product_id'];

    $sql = "{CALL DeleteProduct(?)}";
    $params = [$productID];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die("Delete product failed: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "<script>alert('Product deleted successfully!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product</title>
    <link rel="stylesheet" href="Delete.css">
</head>
<body>
    <div class="delete-container">
        <h1>Delete Product</h1>
        <p>Please enter the Product ID to delete the product from the database.</p>
        <form class="delete-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="product_id">Product ID:</label>
                <input type="number" id="product_id" name="product_id" placeholder="Enter Product ID" required>
            </div>
            <div class="button-group">
                <button type="submit">Delete Product</button>
            </div>
        </form>
    </div>
</body>
</html>
