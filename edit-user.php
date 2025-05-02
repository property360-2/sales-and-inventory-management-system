<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");  // Redirect if not logged in or not an admin
    exit;
}
require_once 'include/Database-connector.php';

if (!isset($_GET['id'])) {
    die("Error: User ID not provided.");
}

$user_id = $_GET['id'];

$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Error: User not found.");
}

$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET name = '$name', username = '$username', password = '$hashed_password', role = '$role' WHERE user_id = $user_id";
    } else {
        $update_sql = "UPDATE users SET name = '$name', username = '$username', role = '$role' WHERE user_id = $user_id";
    }

    if ($conn->query($update_sql)) {
        header("Location: Account-Management.php?success=User has been updated successfully");
        exit();
    } else {
        echo "Error updating user: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="/css/edit-user.css">
</head>

<body>
    <div class="container">
        <h1>Edit User</h1>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $user_id; ?>" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="password">Password (leave blank to keep current):</label>
            <input type="password" id="password" name="password">

            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="cashier" <?php echo $user['role'] == 'cashier' ? 'selected' : ''; ?>>Cashier</option>
            </select>

            <input type="submit" value="Update">
            <a href="Account-Management.php">Cancel</a>
        </form>
    </div>
</body>

</html>

<?php
$conn->close();
?>
