<?php
session_start();
require_once 'include/Database-connector.php';
$cashier_id = $_SESSION['user_id'];
// Handle deleting a sale
if (isset($_POST['delete_sale'])) {
    $sale_id = $_POST['sale_id'];
    
    // Delete related sale items first (to maintain foreign key integrity)
    $conn->prepare("DELETE FROM Sale_Items WHERE sale_id = ?")->execute([$sale_id]);
    
    // Delete sale record
    $conn->prepare("DELETE FROM Sales WHERE sale_id = ?")->execute([$sale_id]);
    
    header("Location: sales-history.php?success=Sale deleted successfully");
    exit();
}

// Handle editing a sale
if (isset($_POST['edit_sale'])) {
    $sale_id = $_POST['sale_id'];
    header("Location: sale-details.php?sale_id=$sale_id"); // Redirect to edit page
    exit();
}

// Fetch all sales records
$sql = "SELECT s.sale_id, s.sale_date, s.total_amount
        FROM Sales s
        WHERE user_id = $cashier_id
        ORDER BY s.sale_date DESC";
$sales = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales History</title>
    <link rel="stylesheet" href="/css/account-management.css">
</head>
<body>
    <header>
        <div class="navbar-cashier">
            <a href="sales.php">Sales</a>
            <a href="logout.php">Log Out</a>
        </div>
    </header>
    
    <div class="container">
        <h2>Sales History</h2>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $sales->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['sale_id']; ?></td>
                        <td><?php echo $row['sale_date']; ?></td>
                        <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <form action="sales-history.php" method="post" style="display:inline;">
                                <input type="hidden" name="sale_id" value="<?php echo $row['sale_id']; ?>">
                                <button type="submit" name="edit_sale">Details</button>
                            </form>
                            <form action="sales-history.php" method="post" style="display:inline;">
                                <input type="hidden" name="sale_id" value="<?php echo $row['sale_id']; ?>">
                                <button type="submit" name="delete_sale" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
