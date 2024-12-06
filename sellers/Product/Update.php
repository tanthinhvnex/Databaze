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
    $description = $_POST['description'] ?? null;
    $price = $_POST['price'] ?? null;

    $sql = "{CALL UpdateProductBasic(?, ?, ?)}";
    $params = [$productID, $description, $price];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die("Update product failed: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "<script>alert('Product updated successfully!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="Update.css">
</head>
<body>
    <div class="update-container">
        <h1>Update Product</h1>
        <form class="update-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="product_id">Product ID:</label>
                <input type="number" id="product_id" name="product_id" placeholder="Enter Product ID" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" placeholder="Enter new description"></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" placeholder="Enter new price">
            </div>
            <div class="button-group">
                <button type="submit">Update Product</button>
                <!-- <button type="button" onclick="window.location.href='productManagement.php'">Cancel</button> -->
            </div>
        </form>
    </div>
</body>
</html>
