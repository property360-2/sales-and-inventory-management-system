<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");  // Redirect if not logged in or not an admin
    exit;
}
require_once 'include/Database-connector.php';

// Check if a search term and stock filter are set
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : '';
$rows_per_page = isset($_GET['rows']) ? (int) $_GET['rows'] : 5; // Default to 5 rows per page
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1; // Default to page 1
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Prepare the base SQL query
$sql = "SELECT * FROM Inventory WHERE name LIKE ?";
$search_term_with_wildcards = "%" . $search_term . "%";
$params = ["s", $search_term_with_wildcards];

// Apply stock filter to the query
if ($stock_filter == 'low') {
    $sql .= " AND quantity < 100";
} elseif ($stock_filter == 'optimal') {
    $sql .= " AND quantity BETWEEN 100 AND 200";
} elseif ($stock_filter == 'high') {
    $sql .= " AND quantity > 200";
}

// Get the total number of products for pagination (no LIMIT)
$total_sql = "SELECT COUNT(*) AS total_products FROM Inventory WHERE name LIKE ?";
if ($stock_filter == 'low') {
    $total_sql .= " AND quantity < 100";
} elseif ($stock_filter == 'optimal') {
    $total_sql .= " AND quantity BETWEEN 100 AND 200";
} elseif ($stock_filter == 'high') {
    $total_sql .= " AND quantity > 200";
}
$total_params = ["s", $search_term_with_wildcards];

// Prepare and execute the total count query
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param(...$total_params);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_products = $total_row['total_products']; // Total number of products in the database
$total_stmt->close();

// Calculate total pages
$total_pages = $rows_per_page == -1 ? 1 : ceil($total_products / $rows_per_page);

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
    <title>Inventory Management</title>
    <link rel="stylesheet" href="/css/inventory-management.css">
</head>

<body>
    <?php
    include 'include/navigation-for-admin.php';
    ?>
    <div class="container">
        <h2>Inventory Management</h2>

        <!-- Display Success or Error Message -->
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php elseif ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Rows per page filter and search form -->
        <form action="inventory-management.php" method="get">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_term); ?>" />
            <label for="stock">Stock Filter:</label>
            <select name="stock" id="stock">
                <option value="" <?php echo $stock_filter == '' ? 'selected' : ''; ?>>All</option>
                <option value="low" <?php echo $stock_filter == 'low' ? 'selected' : ''; ?>>Low Stock (less than 100)</option>
                <option value="optimal" <?php echo $stock_filter == 'optimal' ? 'selected' : ''; ?>>Optimal Stock (100-200)</option>
                <option value="high" <?php echo $stock_filter == 'high' ? 'selected' : ''; ?>>High Stock (more than 200)</option>
            </select>
            <label for="rows">Rows per page:</label>
            <select name="rows" id="rows">
                <option value="5" <?php echo $rows_per_page == 5 ? 'selected' : ''; ?>>5</option>
                <option value="10" <?php echo $rows_per_page == 10 ? 'selected' : ''; ?>>10</option>
                <option value="15" <?php echo $rows_per_page == 15 ? 'selected' : ''; ?>>15</option>
                <option value="20" <?php echo $rows_per_page == 20 ? 'selected' : ''; ?>>20</option>
                <option value="-1" <?php echo $rows_per_page == -1 ? 'selected' : ''; ?>>Show All</option>
            </select>

            <!-- Submit Button -->
            <button type="submit" name="submit">Apply Filters</button>
            
            <!-- Reset Button -->
            <button type="submit" name="reset" value="true">Reset Filters</button>
        </form>

        <a href="add-product.php" class="new-product" role="button">Add New Product</a>
        <br><br>

        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch data from the database with pagination
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "
                            <tr>
                                <td>{$row['product_id']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['description']}</td>
                                <td>{$row['price']}</td>
                                <td>{$row['quantity']}</td>
                                <td class='action-button'>
                                    <a href='edit-product.php?id={$row['product_id']}' class='btn btn-primary btn-sm' role='button'>Edit</a>
                                    <a href='javascript:void(0);' onclick='showModal({$row['product_id']})' class='btn btn-danger btn-sm' role='button'>Delete</a>
                                </td>
                            </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='6'>No products found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?search=<?php echo urlencode($search_term); ?>&stock=<?php echo urlencode($stock_filter); ?>&rows=<?php echo $rows_per_page; ?>&page=<?php echo $current_page - 1; ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?search=<?php echo urlencode($search_term); ?>&stock=<?php echo urlencode($stock_filter); ?>&rows=<?php echo $rows_per_page; ?>&page=<?php echo $i; ?>"
                        class="<?php echo $i == $current_page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?search=<?php echo urlencode($search_term); ?>&stock=<?php echo urlencode($stock_filter); ?>&rows=<?php echo $rows_per_page; ?>&page=<?php echo $current_page + 1; ?>">Next</a>
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
                    <p>Are you sure you want to delete this product?</p>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" onclick="closeModal()">Cancel</button>
                    <button class="confirm-btn" onclick="deleteProduct()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var productIdToDelete;

        function showModal(productId) {
            productIdToDelete = productId;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function deleteProduct() {
            window.location.href = 'delete-product.php?id=' + productIdToDelete;
        }

        document.addEventListener('DOMContentLoaded', function () {
            closeModal(); // Ensure modal is hidden on page load
        });

        document.querySelector('button[name="reset"]').addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = 'inventory-management.php';
        });
    </script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
