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
    // Delete the product permanently from Archive_Inventory
    // Note that Archive_Inventory now has an 'archive_id' as primary key
    $stmt = $conn->prepare("DELETE FROM Archive_Inventory WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        header("Location: admin-archives.php?success=Product permanently deleted");
    } else {
        header("Location: admin-archives.php?error=Failed to delete product");
    }

    $stmt->close();
} catch (mysqli_sql_exception $e) {
    // Catch any SQL exceptions and redirect with the error message
    header("Location: admin-archives.php?error=" . urlencode($e->getMessage()));
}

$conn->close();
exit;
?>
