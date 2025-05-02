<?php
session_start();
require_once 'include/Database-connector.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Validate user ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin-archives.php?error=Invalid user ID");
    exit;
}

$user_id = (int)$_GET['id'];

// Retrieve user details from archive
$select_stmt = $conn->prepare("SELECT * FROM Archive_Users WHERE user_id = ?");
$select_stmt->bind_param("i", $user_id);
$select_stmt->execute();
$result = $select_stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Restore user to Users table
    $restore_stmt = $conn->prepare("
        INSERT INTO Users (user_id, username, name, password, role) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $restore_stmt->bind_param(
        "issss", 
        $user['user_id'], 
        $user['username'], 
        $user['name'], 
        $user['password'], 
        $user['role']
    );

    if ($restore_stmt->execute()) {
        // Remove user from archive
        $delete_stmt = $conn->prepare("DELETE FROM Archive_Users WHERE user_id = ?");
        $delete_stmt->bind_param("i", $user_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        header("Location: admin-archives.php?success=User restored successfully");
    } else {
        header("Location: admin-archives.php?error=Failed to restore user");
    }

    $restore_stmt->close();
} else {
    header("Location: admin-archives.php?error=User not found");
}

$select_stmt->close();
$conn->close();
exit;
?>
