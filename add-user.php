<?php
session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: login.php");  // Redirect if not logged in or not an admin
//     exit;
// }
require_once 'include/Database-connector.php';

// Enable exception mode for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the values from the form
        $name = $_POST['name'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Simple password hashing for security 
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // SQL query to insert the new user into the Users table
        $sql = "INSERT INTO Users (username, name, password, role) VALUES ('$username', '$name', '$hashed_password', '$role')";

        // Execute the query
        $conn->query($sql);

        // Redirect to the account-management page with success parameter
        header("Location: Account-Management.php?success=user is added");
        exit(); // Ensure no further code is executed
    }
} catch (mysqli_sql_exception $e) {
    // Handle duplicate entry or other SQL errors
    if ($e->getCode() == 1062) { // Error code for duplicate entry
        $error_message = "Error: The username '$username' already exists. Please choose a different username.";
    } else {
        // Generic error message
        $error_message = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New User</title>
    <link rel="stylesheet" href="css/add-user.css">
</head>

<body>
    <div class="container">
        <h1>Register New User</h1>

        <!-- Display Error Message if Any -->
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="admin">Admin</option>
                <option value="cashier">Cashier</option>
            </select>

            <input type="submit" value="Register">
        </form>
    </div>
</body>

</html>

<?php
$conn->close();
?>
