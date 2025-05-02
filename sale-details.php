<?php
session_start();
require_once 'include/Database-connector.php';

// Check if sale_id is provided
if (!isset($_GET['sale_id']) || empty($_GET['sale_id'])) {
    die("Invalid Sale ID.");
}

$sale_id = intval($_GET['sale_id']); // Ensure it's an integer

// Fetch sale details
$saleQuery = $conn->prepare("SELECT s.sale_id, s.sale_date, s.total_amount, u.username 
                             FROM sales s 
                             LEFT JOIN users u ON s.user_id = u.user_id
                             WHERE s.sale_id = ?");
$saleQuery->bind_param("i", $sale_id);
$saleQuery->execute();
$saleResult = $saleQuery->get_result();
$sale = $saleResult->fetch_assoc();

if (!$sale) {
    die("Sale not found.");
}

// Fetch sale items
$itemQuery = $conn->prepare("SELECT i.name, si.quantity, si.subtotal 
                             FROM sale_items si
                             JOIN inventory i ON si.product_id = i.product_id
                             WHERE si.sale_id = ?");
$itemQuery->bind_param("i", $sale_id);
$itemQuery->execute();
$items = $itemQuery->get_result();

// Fetch payment details
$paymentQuery = $conn->prepare("SELECT payment_method, amount_paid, payment_date 
                                FROM payments 
                                WHERE sale_id = ?");
$paymentQuery->bind_param("i", $sale_id);
$paymentQuery->execute();
$paymentResult = $paymentQuery->get_result();
$payment = $paymentResult->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Details</title>
    <link rel="stylesheet" href="/css/account-management.css">
</head>
<body>
    <header>
        <div class="navbar-cashier">
            <a href="sales-history.php">Back to Sales History</a>
            <a href="logout.php">Log Out</a>
        </div>
    </header>
    
    <div class="container">
        <h2>Sale Details</h2>
        
        <p><strong>Sale ID:</strong> <?php echo $sale['sale_id']; ?></p>
        <p><strong>Cashier:</strong> <?php echo htmlspecialchars($sale['username']); ?></p>
        <p><strong>Date:</strong> <?php echo $sale['sale_date']; ?></p>
        <p><strong>Total Amount:</strong> ₱<?php echo number_format($sale['total_amount'], 2); ?></p>

        <h3>Purchased Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>₱<?php echo number_format($row['subtotal'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3>Payment Details</h3>
        <?php if ($payment): ?>
            <p><strong>Payment Method:</strong> <?php echo ucfirst($payment['payment_method']); ?></p>
            <p><strong>Amount Paid:</strong> ₱<?php echo number_format($payment['amount_paid'], 2); ?></p>
            <p><strong>Payment Date:</strong> <?php echo $payment['payment_date']; ?></p>
        <?php else: ?>
            <p>No payment information available.</p>
        <?php endif; ?>

        <!-- Edit Sale Button -->
        <form action="edit-sale.php" method="get">
            <input type="hidden" name="sale_id" value="<?php echo $sale['sale_id']; ?>">
            <button type="submit">Edit Sale</button>
        </form>

    </div>
</body>
</html>

<?php
$saleQuery->close();
$itemQuery->close();
$paymentQuery->close();
$conn->close();
?>
