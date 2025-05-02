<?php
session_start();
require_once 'include/Database-connector.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin-archives.php?error=Invalid%20user%20ID");
    exit;
}

$user_id = (int)$_GET['id'];

try {
    // Delete user permanently from Archive_Users
    $stmt = $conn->prepare("DELETE FROM Archive_Users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Redirect to the correct URL for success
        header("Location: admin-archives.php?success=User%20permanently%20deleted");
    } else {
        // Redirect to the correct URL for failure
        header("Location: admin-archives.php?error=Failed%20to%20delete%20user");
    }

    $stmt->close();
} catch (mysqli_sql_exception $e) {
    // Handle error and redirect with the error message
    header("Location: admin-archives.php?error=" . urlencode($e->getMessage()));
}

$conn->close();
exit;
?>
