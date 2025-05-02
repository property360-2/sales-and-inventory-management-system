<?php
session_start();
require_once 'include/Database-connector.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch archived users
$query_users = "SELECT * FROM Archive_Users ORDER BY deleted_at DESC";
$result_users = $conn->query($query_users);

// Fetch archived inventory
$query_inventory = "SELECT * FROM Archive_Inventory ORDER BY deleted_at DESC";
$result_inventory = $conn->query($query_inventory);

// Initialize search variables
$search_user = isset($_POST['search_user']) ? $_POST['search_user'] : '';
$search_inventory = isset($_POST['search_inventory']) ? $_POST['search_inventory'] : '';
$selected_role = isset($_POST['role']) ? $_POST['role'] : ''; // Initialize selected role with an empty string if not set


// Fetch archived users with search filter and role filter
$query_users = "SELECT * FROM Archive_Users WHERE (username LIKE ? OR name LIKE ?) AND (role LIKE ? OR ? = '') ORDER BY deleted_at DESC";
$stmt_users = $conn->prepare($query_users);
$search_user_param = "%" . $search_user . "%";
$selected_role_param = "%" . $selected_role . "%";
$stmt_users->bind_param('ssss', $search_user_param, $search_user_param, $selected_role_param, $selected_role_param);
$stmt_users->execute();
$result_users = $stmt_users->get_result();

// Fetch archived inventory with search filter
$query_inventory = "SELECT * FROM Archive_Inventory WHERE name LIKE ? OR description LIKE ? ORDER BY deleted_at DESC";
$stmt_inventory = $conn->prepare($query_inventory);
$search_inventory_param = "%" . $search_inventory . "%";
$stmt_inventory->bind_param('ss', $search_inventory_param, $search_inventory_param);
$stmt_inventory->execute();
$result_inventory = $stmt_inventory->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Archives</title>
    <link rel="stylesheet" href="/css/account-management.css"> <!-- Add your styles -->
    <style>
        .navbar {
            margin-left: 45px;
        }

        .navbar li {
            margin: 5px;
        }
        td a{
            display: block;
            margin: 5px;
        } 
    </style>
</head>

<body>
    <?php include 'include/navigation-for-admin.php'; ?>
    <ul class="navbar">
        <li><a href="#Archived-Users">Archived Users</a></li>
        <li><a href="#Archived-Inventory">Archived Inventory</a></li>
    </ul>
    <div class="container">
        <h2 id="Archived-Users">Archived Users</h2>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="search-container">
                <input type="text" name="search_user" value="<?= htmlspecialchars($search_user) ?>"
                    placeholder="Search by Username or Name">
                <select name="role">
                    <option value="">All Roles</option>
                    <option value="admin" <?= $selected_role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="cashier" <?= $selected_role === 'cashier' ? 'selected' : '' ?>>Cashier</option>
                </select>
                <button type="submit">Search</button>
                <button type="reset" onclick="window.location.href = window.location.pathname;">Reset</button>
            </div>

        </form>
        <table border="1">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Deleted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['user_id']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td><?= htmlspecialchars($row['deleted_at']) ?></td>
                        <td>
                            <a href="javascript:void(0);" onclick="showRestoreModal(<?= $row['user_id'] ?>, 'user')" class="btn btn-primary btn-sm">Restore</a> 
                            <a href="javascript:void(0);" onclick="showDeleteModal(<?= $row['user_id'] ?>, 'user')" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2 id="Archived-Inventory">Archived Inventory</h2>
        <form method="POST" action="">
            <div class="search-container">
                <input type="text" name="search_inventory" value="<?= htmlspecialchars($search_inventory) ?>"
                    placeholder="Search by Product Name or Description">
                <button type="submit">Search</button>
                <button type="reset" onclick="window.location.href = window.location.pathname;">Reset</button>

            </div>
        </form>
        <table border="1">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Deleted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_inventory->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['product_id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['price']) ?></td>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                        <td><?= htmlspecialchars($row['deleted_at']) ?></td>
                        <td>
                            <a href="javascript:void(0);" onclick="showRestoreModal(<?= $row['product_id'] ?>, 'inventory')" class="btn btn-primary btn-sm">Restore</a> 
                            <a href="javascript:void(0);" onclick="showDeleteModal(<?= $row['product_id'] ?>, 'inventory')" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this?</p>
            </div>
            <div class="modal-footer">
                <button class="btn cancel-btn" onclick="closeModal()">Cancel</button>
                <button class="btn confirm-btn" onclick="deleteItem()">Delete</button>
            </div>
        </div>
    </div>

    <!-- Restore Modal -->
    <div id="restoreModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Restore</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore this?</p>
            </div>
            <div class="modal-footer">
                <button class="btn cancel-btn" onclick="closeModal()">Cancel</button>
                <button class="btn confirm-btn" onclick="restoreItem()">Restore</button>
            </div>
        </div>
    </div>

    <script>
        var itemIdToDelete;
        var itemType;

        function showDeleteModal(id, type) {
            itemIdToDelete = id;
            itemType = type;
            document.getElementById('deleteModal').style.display = 'flex'; // Show the delete modal
        }

        function showRestoreModal(id, type) {
            itemIdToDelete = id;
            itemType = type;
            document.getElementById('restoreModal').style.display = 'flex'; // Show the restore modal
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.getElementById('restoreModal').style.display = 'none';
        }

        function deleteItem() {
            var url = itemType === 'user' ? 'admin-delete-user-permanently.php?id=' : 'admin-delete-inventory-permanently.php?id=';
            window.location.href = url + itemIdToDelete;
        }

        function restoreItem() {
            var url = itemType === 'user' ? 'admin-restore-user.php?id=' : 'admin-restore-inventory.php?id=';
            window.location.href = url + itemIdToDelete;
        }
    </script>
</body>

</html>

<?php
$conn->close();
?>
