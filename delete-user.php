<?php
session_start();
require_once 'include/Database-connector.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: Account-Management.php?error=Invalid user ID");
    exit;
}

$user_id = (int)$_GET['id'];

// Prevent admin self-deletion
if ($user_id == $_SESSION['user_id']) {
    header("Location: Account-Management.php?error=You cannot delete yourself");
    exit;
}

// Retrieve user details before deletion
$select_stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
$select_stmt->bind_param("i", $user_id);
$select_stmt->execute();
$result = $select_stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Move user to Archive_Users
    $archive_stmt = $conn->prepare("
        INSERT INTO Archive_Users (user_id, username, name, password, role, deleted_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $archive_stmt->bind_param(
        "issss", 
        $user['user_id'], 
        $user['username'], 
        $user['name'], 
        $user['password'], 
        $user['role']
    );

    if ($archive_stmt->execute()) {
        // Proceed with deletion after archiving
        $delete_stmt = $conn->prepare("DELETE FROM Users WHERE user_id = ?");
        $delete_stmt->bind_param("i", $user_id);

        if ($delete_stmt->execute()) {
            // Set user_id in Sales table to NULL (maintains referential integrity)
            $conn->query("UPDATE Sales SET user_id = NULL WHERE user_id = $user_id");

            header("Location: Account-Management.php?success=User archived and deleted successfully");
        } else {
            header("Location: Account-Management.php?error=Failed to delete user after archiving");
        }
        $delete_stmt->close();
    } else {
        header("Location: Account-Management.php?error=Failed to archive user");
    }

    $archive_stmt->close();
} else {
    header("Location: Account-Management.php?error=User not found");
}

$select_stmt->close();
$conn->close();
exit;
?>
