<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");  // Redirect if not logged in or not an admin
    exit;
}
require_once 'include/Database-connector.php';

// Enable exception mode for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the values from the form
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $price = $conn->real_escape_string($_POST['price']);
        $quantity = $conn->real_escape_string($_POST['quantity']);

        // SQL query to insert the new product into the Inventory table
        $sql = "INSERT INTO Inventory (name, description, price, quantity) VALUES ('$name', '$description', '$price', '$quantity')";

        // Execute the query
        $conn->query($sql);

        // Redirect to the inventory management page with success parameter
        header("Location: Inventory-Management.php?success=Product is added");
        exit();
    }
} catch (mysqli_sql_exception $e) {
    // Handle SQL errors
    echo "An error occurred: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <link rel="stylesheet" href="/css/add-product.css">
</head>
<body>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
    <h1>Add New Product</h1>

        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>

        <label for="price">Price:</label>
        <input type="number" step="0.01" id="price" name="price" required>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" required>

        <input type="submit" value="Add Product">
    </form>
</body>
</html>

<?php
$conn->close();
?>
