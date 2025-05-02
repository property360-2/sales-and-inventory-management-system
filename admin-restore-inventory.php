<?php
session_start();
require_once 'include/Database-connector.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin-archives.php?error=Invalid product ID");
    exit;
}

$product_id = (int)$_GET['id'];

try {
    // Retrieve the product details from Archive_Inventory
    $select_stmt = $conn->prepare("SELECT * FROM Archive_Inventory WHERE product_id = ?");
    $select_stmt->bind_param("i", $product_id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();

        // Restore the product to Inventory
        $restore_stmt = $conn->prepare("
            INSERT INTO Inventory (product_id, name, description, price, quantity)
            VALUES (?, ?, ?, ?, ?)
        ");
        $restore_stmt->bind_param(
            "issdi",
            $product['product_id'],
            $product['name'],
            $product['description'],
            $product['price'],
            $product['quantity']
        );

        if ($restore_stmt->execute()) {
            // Remove the product from Archive_Inventory after restoring
            $delete_stmt = $conn->prepare("DELETE FROM Archive_Inventory WHERE product_id = ?");
            $delete_stmt->bind_param("i", $product_id);

            if ($delete_stmt->execute()) {
                header("Location: admin-archives.php?success=Product restored successfully");
                exit();
            } else {
                header("Location: admin-archives.php?error=Failed to delete product from archive after restoring");
            }
            $delete_stmt->close();
        } else {
            header("Location: admin-archives.php?error=Failed to restore product");
        }
        $restore_stmt->close();
    } else {
        header("Location: admin-archives.php?error=Product not found in archive");
    }

    $select_stmt->close();
} catch (mysqli_sql_exception $e) {
    header("Location: admin-archives.php?error=" . urlencode($e->getMessage()));
}

$conn->close();
exit;
?>
