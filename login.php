<?php
require_once 'include/Database-connector.php'; // Ensure database connection is properly set up

session_start([
    'use_strict_mode' => true,
    'use_cookies' => true,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']), // Secure cookies only over HTTPS
    'cookie_samesite' => 'Strict',
]);

$error = ''; // Initialize the error message variable

// Generate a CSRF token if not set || 
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

$lockout_time = 25; // 15 minutes
$max_attempts = 2;

if ($_SESSION['failed_attempts'] >= $max_attempts && (time() - $_SESSION['last_attempt_time']) < $lockout_time) {
    $error = "Too many failed attempts. Try again later.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $csrf_token = $_POST['csrf_token'] ?? '';

    // CSRF Protection
    if ($csrf_token !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    // Check if the inputs are empty
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Validate database connection
        if (!$conn) {
            die("Database connection failed: " . $conn->connect_error);
        }

        // Prepare SQL query (Prevents SQL injection)
        $query = "SELECT * FROM Users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Reset failed attempts
                $_SESSION['failed_attempts'] = 0;

                // Set session variables upon successful login
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username']; // Optional: for displaying the username

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Redirect based on user role
                if ($user['role'] === 'admin') {
                    header("Location: Report.php");
                    exit;
                } else {
                    header("Location: sales.php");
                    exit;
                }
            } else {
                // Failed attempt
                $_SESSION['failed_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $error = "Invalid login credentials.";
            }
        } else {
            $error = "Invalid login credentials.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/css/login.css">
</head>

<body>
    <div class="container">
        <h1>Login</h1>

        <!-- Display Error Message -->
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Login">
        </form>
    </div>
</body>

</html>
