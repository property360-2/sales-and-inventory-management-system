<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");  // Redirect if not logged in or not an admin
    exit;
}
require_once 'include/Database-connector.php';

// Check if the product ID is provided in the URL
if (!isset($_GET['id'])) {
    die("Error: Product ID not provided.");
}

$product_id = $_GET['id'];

// Fetch the product data from the database
$sql = "SELECT * FROM Inventory WHERE product_id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Error: Product not found.");
}

$product = $result->fetch_assoc();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Update the product in the database
    $update_sql = "UPDATE Inventory SET name = '$name', description = '$description', price = '$price', quantity = '$quantity' WHERE product_id = $product_id";

    // Execute the update query
    if ($conn->query($update_sql)) {
        // Redirect to the inventory management page with a success message
        header("Location: Inventory-Management.php?success=product is updated");
        exit();
    } else {
        echo "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="css/edit-product.css">
</head>

<body>
    <form action="edit-product.php?id=<?php echo $product_id; ?>" method="post">
            <h1>Edit Product</h1>
        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" value="<?php echo $product['name']; ?>" required>
        <br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo $product['description']; ?></textarea>
        <br><br>

        <label for="price">Price:</label>
        <input type="number" step="0.01" id="price" name="price" value="<?php echo $product['price']; ?>" required>
        <br><br>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo $product['quantity']; ?>" required>
        <br><br>

        <input type="submit" value="Update Product">
        <a href="Inventory-Management.php">Cancel</a>
    </form>
</body>

</html>

<?php
$conn->close();
?>
