<?php
session_start();

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
    $user_id = $_SESSION['user_id'];
    $productName = $_POST['product_name'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $price = $_POST['price'];
    $stockQuantity = $_POST['stock_quantity'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $sleeveLength = $_POST['sleeve_length'] ?? null;
    $neckStyle = $_POST['neck_style'] ?? null;
    $waistSize = $_POST['waist_size'] ?? null;
    $legLength = $_POST['leg_length'] ?? null;
    $sql = "{CALL InsertProduct(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
    $params = [
        $user_id,$productName, $size, $color, $price, $stockQuantity,
        $description, $category, $sleeveLength, $neckStyle, $waistSize, $legLength
    ];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die("Insert product failed: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "<script>alert('Product added successfully!')</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Product</title>
    <link rel="stylesheet" href="Insert.css">
</head>
<body>
    <div class="product-form-container">
        <h1>Add a New Product</h1>
        <form class="product-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <div class="form-group">
                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="product_name" placeholder="Enter product name" required>
            </div>
            <div class="form-group">
                <label for="size">Size:</label>
                <input type="text" id="size" name="size" placeholder="Enter size" required>
            </div>
            <div class="form-group">
                <label for="color">Color:</label>
                <input type="text" id="color" name="color" placeholder="Enter color" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" placeholder="Enter price" required>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity:</label>
                <input type="number" id="stock_quantity" name="stock_quantity" placeholder="Enter stock quantity" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" placeholder="Enter product description" required></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <option value="UpperWear">UpperWear</option>
                    <option value="LowerWear">LowerWear</option>
                </select>
            </div>
            <div class="form-group conditional-group" id="upperwear-options" style="display: none;">
                <label for="sleeve_length">Sleeve Length:</label>
                <input type="text" id="sleeve_length" name="sleeve_length" placeholder="Enter sleeve length">
                <label for="neck_style">Neck Style:</label>
                <input type="text" id="neck_style" name="neck_style" placeholder="Enter neck style">
            </div>
            <div class="form-group conditional-group" id="lowerwear-options" style="display: none;">
                <label for="waist_size">Waist Size:</label>
                <input type="text" id="waist_size" name="waist_size" placeholder="Enter waist size">
                <label for="leg_length">Leg Length:</label>
                <input type="text" id="leg_length" name="leg_length" placeholder="Enter leg length">
            </div>
            <div class="button-group">
                <button type="submit">Add Product</button>
            </div>
        </form>
    </div>
    <script>
        document.getElementById('category').addEventListener('change', function () {
            const upperwearOptions = document.getElementById('upperwear-options');
            const lowerwearOptions = document.getElementById('lowerwear-options');
            upperwearOptions.style.display = this.value === 'UpperWear' ? 'block' : 'none';
            lowerwearOptions.style.display = this.value === 'LowerWear' ? 'block' : 'none';
        });
    </script>
</body>
</html>
