<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");  // Redirect if not logged in or not an admin
    exit;
}
require_once 'include/Database-connector.php';

// Check if a search term is set
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$rows_per_page = isset($_GET['rows']) ? (int) $_GET['rows'] : 5; // Default to 5 rows per page
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1; // Default to page 1
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Prepare the base SQL query
$sql = "SELECT * FROM users WHERE (username LIKE ? OR name LIKE ?)";
$search_term_with_wildcards = "%" . $search_term . "%";
$params = ["ss", $search_term_with_wildcards, $search_term_with_wildcards]; // Initial parameters

// Apply role filter if specified
if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $params[0] .= "s"; // Add string type for binding
    $params[] = $role_filter; // Add role to parameters
}

// Get the total number of users for pagination (no LIMIT)
$total_sql = "SELECT COUNT(*) AS total_users FROM users WHERE (username LIKE ? OR name LIKE ?)";
$total_params = ["ss", $search_term_with_wildcards, $search_term_with_wildcards];

if (!empty($role_filter)) {
    $total_sql .= " AND role = ?";
    $total_params[0] .= "s";
    $total_params[] = $role_filter;
}

// Prepare and execute the total count query
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param(...$total_params);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_users = $total_row['total_users']; // Total number of users in the database
$total_stmt->close();

// Calculate total pages
$total_pages = $rows_per_page == -1 ? 1 : ceil($total_users / $rows_per_page);

// Handle pagination
if ($rows_per_page != -1) {
    $offset = ($current_page - 1) * $rows_per_page;
    $sql .= " LIMIT ?, ?";
    $params[0] .= "ii"; // Add integer types for binding
    $params[] = $offset;
    $params[] = $rows_per_page;
}

// Prepare the main query statement
$stmt = $conn->prepare($sql);
$stmt->bind_param(...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management</title>
    <link rel="stylesheet" href="/css/account-management.css">
</head>

<body>
    <?php
    include 'include/navigation-for-admin.php';
    ?>
    <div class="container">
        <h2>Account Management</h2>

        <!-- Display Success or Error Message -->
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php elseif ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Search form -->
        <form action="Account-Management.php" method="get">
            <input type="text" name="search" placeholder="Search users..."
                value="<?php echo htmlspecialchars($search_term); ?>" />
            <button type="submit">Search</button>
        </form>

        <!-- Filters for role and rows per page -->
        <form action="Account-Management.php" method="get">
            <label for="role">Role:</label>
            <select name="role" id="role">
                <option value="">All</option>
                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="cashier" <?php echo $role_filter === 'cashier' ? 'selected' : ''; ?>>Cashier</option>
            </select>

            <label for="rows">Rows per page:</label>
            <select name="rows" id="rows">
                <option value="5" <?php echo $rows_per_page == 5 ? 'selected' : ''; ?>>5</option>
                <option value="10" <?php echo $rows_per_page == 10 ? 'selected' : ''; ?>>10</option>
                <option value="15" <?php echo $rows_per_page == 15 ? 'selected' : ''; ?>>15</option>
                <option value="20" <?php echo $rows_per_page == 20 ? 'selected' : ''; ?>>20</option>
                <option value="-1" <?php echo $rows_per_page == -1 ? 'selected' : ''; ?>>Show All</option>
            </select>

            <button type="submit">Apply</button>
            <button type="submit" name="reset" value="true">Reset</button>
        </form>

        <a href="add-user.php" class="btn btn-primary" role="button">New User</a>
        <br><br>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch data from the database with pagination and role filter
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "
                            <tr>
                                <td>{$row['user_id']}</td>
                                <td>{$row['username']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['role']}</td>
                                <td>
                                    <a href='edit-user.php?id={$row['user_id']}' class='btn btn-primary btn-sm' role='button'>Edit</a>
                                    <a href='javascript:void(0);' onclick='showModal({$row['user_id']})' class='btn btn-danger btn-sm' role='button'>Delete</a>
                                </td>
                            </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='5'>No users found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a
                        href="?search=<?php echo urlencode($search_term); ?>&role=<?php echo urlencode($role_filter); ?>&rows=<?php echo $rows_per_page; ?>&page=<?php echo $current_page - 1; ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?search=<?php echo urlencode($search_term); ?>&role=<?php echo urlencode($role_filter); ?>&rows=<?php echo $rows_per_page; ?>&page=<?php echo $i; ?>"
                        class="<?php echo $i == $current_page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a
                        href="?search=<?php echo urlencode($search_term); ?>&role=<?php echo urlencode($role_filter); ?>&rows=<?php echo $rows_per_page; ?>&page=<?php echo $current_page + 1; ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Delete Modal -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Confirm Delete</h3>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user?</p>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" onclick="closeModal()">Cancel</button>
                    <button class="confirm-btn" onclick="deleteUser()">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <script src=""></script>
    <script>
        var userIdToDelete;

        function showModal(userId) {
            userIdToDelete = userId;
            document.getElementById('deleteModal').style.display = 'flex'; // Show the modal
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none'; // Hide the modal
        }

        function deleteUser() {
            window.location.href = 'delete-user.php?id=' + userIdToDelete; // Redirect to delete the user
        }

        document.addEventListener('DOMContentLoaded', function () {
            closeModal(); // Hide the modal when the page loads
        });
        document.querySelector('button[name="reset"]').addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = 'Account-Management.php';
        });
        document.querySelectorAll('#role, #rows').forEach(select => {
            select.addEventListener('change', function () {
                this.form.submit();
            });
        });

    </script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>