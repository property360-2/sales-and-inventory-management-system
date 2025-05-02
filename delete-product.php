<?php
session_start();
require_once 'include/Database-connector.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if a product ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $product_id = (int)$_GET['id'];

    try {
        // Start a new transaction
        $conn->begin_transaction();

        // Retrieve product details before deletion
        $select_stmt = $conn->prepare("SELECT * FROM Inventory WHERE product_id = ?");
        $select_stmt->bind_param("i", $product_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();

        if ($result->num_rows === 1) {
            $product = $result->fetch_assoc();

            // Archive the product before deleting
            $archive_stmt = $conn->prepare("
                INSERT INTO Archive_Inventory (product_id, name, description, price, quantity, deleted_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $archive_stmt->bind_param(
                "issdi",
                $product['product_id'],
                $product['name'],
                $product['description'],
                $product['price'],
                $product['quantity']
            );
            $archive_stmt->execute();

            // Delete related Sale Items first to prevent foreign key constraint violation
            $delete_sale_items_stmt = $conn->prepare("DELETE FROM Sale_Items WHERE product_id = ?");
            $delete_sale_items_stmt->bind_param("i", $product_id);
            $delete_sale_items_stmt->execute();

            // Delete the product from Inventory
            $delete_stmt = $conn->prepare("DELETE FROM Inventory WHERE product_id = ?");
            $delete_stmt->bind_param("i", $product_id);
            $delete_stmt->execute();

            // Commit the transaction
            $conn->commit();

            header("Location: Inventory-Management.php?success=Product archived and deleted successfully");
            exit();
        } else {
            // Rollback the transaction if product not found
            $conn->rollback();
            header("Location: Inventory-Management.php?error=Product not found");
        }

        $select_stmt->close();
    } catch (mysqli_sql_exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        // Log the error message for debugging purposes
        error_log($e->getMessage());
        header("Location: Inventory-Management.php?error=An error occurred while processing your request");
    }
} else {
    // If no valid ID is provided, redirect back to inventory management
    header("Location: Inventory-Management.php?error=Invalid or missing product ID");
    exit();
}

$conn->close();
?>
