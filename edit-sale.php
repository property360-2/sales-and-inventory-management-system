<?php
session_start();
require_once 'include/Database-connector.php';

if (!isset($_GET['sale_id']) || empty($_GET['sale_id'])) {
    die("Invalid Sale ID.");
}

$sale_id = intval($_GET['sale_id']);

// Fetch sale details
$saleQuery = $conn->prepare("SELECT sale_id, sale_date, total_amount FROM sales WHERE sale_id = ?");
$saleQuery->bind_param("i", $sale_id);
$saleQuery->execute();
$saleResult = $saleQuery->get_result();
$sale = $saleResult->fetch_assoc();

if (!$sale) {
    die("Sale not found.");
}

// Fetch sale items
$itemQuery = $conn->prepare("SELECT si.sale_item_id, i.name, si.quantity, si.subtotal, i.price 
                             FROM sale_items si
                             JOIN inventory i ON si.product_id = i.product_id
                             WHERE si.sale_id = ?");
$itemQuery->bind_param("i", $sale_id);
$itemQuery->execute();
$items = $itemQuery->get_result();

// Handle form submission for updating sale
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_date = $_POST['sale_date'];
    $new_total_amount = 0;

    $conn->prepare("UPDATE sales SET sale_date = ? WHERE sale_id = ?")
         ->execute([$new_date, $sale_id]);

    foreach ($_POST['items'] as $sale_item_id => $item_data) {
        $new_quantity = intval($item_data['quantity']);
        $product_price = floatval($item_data['price']);
        $new_subtotal = $new_quantity * $product_price;
        $new_total_amount += $new_subtotal;

        $updateItemQuery = $conn->prepare("UPDATE sale_items SET quantity = ?, subtotal = ? WHERE sale_item_id = ?");
        $updateItemQuery->bind_param("idi", $new_quantity, $new_subtotal, $sale_item_id);
        $updateItemQuery->execute();
    }

    // Update total amount in sales table
    $updateTotalQuery = $conn->prepare("UPDATE sales SET total_amount = ? WHERE sale_id = ?");
    $updateTotalQuery->bind_param("di", $new_total_amount, $sale_id);
    $updateTotalQuery->execute();

    header("Location: sales-history.php?success=Sale updated successfully");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sale</title>
    <link rel="stylesheet" href="/css/account-management.css">
    <script>
        function updateSubtotal(input) {
            let row = input.closest('tr');
            let quantity = parseInt(input.value);
            let price = parseFloat(row.querySelector('.product-price').value);
            let subtotalField = row.querySelector('.subtotal');

            if (!isNaN(quantity) && !isNaN(price)) {
                let newSubtotal = (quantity * price).toFixed(2);
                subtotalField.value = newSubtotal;
            }
            updateTotal();
        }

        function updateTotal() {
            let totalAmount = 0;
            document.querySelectorAll('.subtotal').forEach(subtotalField => {
                totalAmount += parseFloat(subtotalField.value);
            });
            document.getElementById('total_amount').value = totalAmount.toFixed(2);
        }
    </script>
</head>
<body>
    <header>
        <div class="navbar-cashier">
            <a href="sales-history.php">Back to Sales History</a>
            <a href="logout.php">Log Out</a>
        </div>
    </header>
    
    <div class="container">
        <h2>Edit Sale</h2>

        <form action="edit-sale.php?sale_id=<?php echo $sale_id; ?>" method="post">
            <label for="sale_date">Sale Date:</label>
            <input type="datetime-local" name="sale_date" id="sale_date" 
                   value="<?php echo date('Y-m-d\TH:i', strtotime($sale['sale_date'])); ?>" required>

            <h3>Sale Items</h3>
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
                            <td>
                                <input type="number" name="items[<?php echo $row['sale_item_id']; ?>][quantity]" 
                                       value="<?php echo $row['quantity']; ?>" min="1" required 
                                       oninput="updateSubtotal(this)">
                                <input type="hidden" class="product-price" 
                                       name="items[<?php echo $row['sale_item_id']; ?>][price]" 
                                       value="<?php echo $row['price']; ?>">
                            </td>
                            <td>
                                <input type="text" class="subtotal" name="items[<?php echo $row['sale_item_id']; ?>][subtotal]" 
                                       value="<?php echo number_format($row['subtotal'], 2); ?>" readonly>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h3>Total Amount:</h3>
            <input type="text" id="total_amount" name="total_amount" value="<?php echo number_format($sale['total_amount'], 2); ?>" readonly>

            <button type="submit">Update Sale</button>
        </form>
    </div>
</body>
</html>

<?php
$saleQuery->close();
$itemQuery->close();
$conn->close();
?>
