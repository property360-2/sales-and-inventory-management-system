<?php
session_start();
require_once 'include/Database-connector.php';

// Fetch search query & pagination details
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$rows_per_page = isset($_GET['rows']) ? (int) $_GET['rows'] : 5;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $rows_per_page;

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Fetch products with stock
$search_term_with_wildcards = "%" . $search_term . "%";
$sql = "SELECT * FROM Inventory WHERE name LIKE ? LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $search_term_with_wildcards, $offset, $rows_per_page);
$stmt->execute();
$result = $stmt->get_result();

// Count total products for pagination
$total_sql = "SELECT COUNT(*) AS total_products FROM Inventory WHERE name LIKE ?";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param("s", $search_term_with_wildcards);
$total_stmt->execute();
$total_products = $total_stmt->get_result()->fetch_assoc()['total_products'];
$total_stmt->close();

$total_pages = ceil($total_products / $rows_per_page);

// Cart functionality
$cart = $_SESSION['cart'] ?? [];
// Handle editing quantity in cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_cart'])) {
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['quantity'];

    foreach ($cart as &$item) {
        if ($item['id'] == $product_id) {
            // Fetch the product details again to check stock
            $product_stmt = $conn->prepare("SELECT quantity FROM Inventory WHERE product_id = ?");
            $product_stmt->bind_param("i", $product_id);
            $product_stmt->execute();
            $product_stock = $product_stmt->get_result()->fetch_assoc();

            if ($new_quantity > 0 && $new_quantity <= $product_stock['quantity']) {
                $item['quantity'] = $new_quantity;
                $item['total'] = $item['quantity'] * $item['price'];
                $_SESSION['cart'] = $cart;
            } else {
                $error = "Invalid quantity. Ensure it's within stock limits.";
            }
            break;
        }
    }
}


// Handle adding product to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Fetch product details from inventory
    $product_stmt = $conn->prepare("SELECT * FROM Inventory WHERE product_id = ?");
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product = $product_stmt->get_result()->fetch_assoc();

    // If product exists and quantity is available
    if ($product && $quantity > 0 && $quantity <= $product['quantity']) {
        // Check if the product is already in the cart
        $product_exists_in_cart = false;
        foreach ($cart as &$item) {
            if ($item['id'] == $product_id) {
                // Update the quantity and total if the product is already in the cart
                $item['quantity'] += $quantity;
                $item['total'] = $item['quantity'] * $item['price'];
                $product_exists_in_cart = true;
                break;
            }
        }

        // If the product is not already in the cart, add it
        if (!$product_exists_in_cart) {
            $cart[] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'total' => $product['price'] * $quantity
            ];
        }

        // Save the updated cart to session
        $_SESSION['cart'] = $cart;

        // **NEW CODE: Reduce stock immediately**
        $update_stock_stmt = $conn->prepare("UPDATE Inventory SET quantity = quantity - ? WHERE product_id = ?");
        $update_stock_stmt->bind_param("ii", $quantity, $product_id);
        $update_stock_stmt->execute();

        // Force reload to reflect changes
        header("Location: sales.php");
        exit();
    } else {
        $error = "Invalid quantity or product not found.";
    }
}




// Handle removing from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];

    foreach ($cart as $key => $item) {
        if ($item['id'] == $product_id) {
            $quantity_to_restore = $item['quantity']; // Get quantity before removing

            // Restore stock quantity to inventory
            $restore_stock_stmt = $conn->prepare("UPDATE Inventory SET quantity = quantity + ? WHERE product_id = ?");
            $restore_stock_stmt->bind_param("ii", $quantity_to_restore, $product_id);

            if ($restore_stock_stmt->execute()) {
                unset($cart[$key]); // Remove from cart only if stock update is successful
            } else {
                $error = "Failed to restore stock.";
            }
            // Force reload to reflect changes
            header("Location: sales.php");
            exit();
            
        }
    }

    $_SESSION['cart'] = array_values($cart);
}




// Handle checkout - Step 1: Process payment before completing sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    $payment_method = $_POST['payment_method'] ?? '';
    $amount_paid = $_POST['amount_paid'] ?? 0;

    if (!$user_id) {
        $error = "User not logged in.";
    } elseif (empty($cart)) {
        $error = "Cart is empty.";
    } elseif (empty($payment_method)) {
        $error = "please choose payment method";
    } else if ($amount_paid <= 0) {
        $error = "The Customer Money Is Insufficient.";
    } else {
        // Step 2: Insert payment details into Payments table
        $total_amount = array_sum(array_column($cart, 'total')); // Calculate total cart value
        if ($amount_paid < $total_amount) {
            $error = "Customer Montey is Insufficient.";
        } else {
            // Step 3: Insert sale record
            $sale_stmt = $conn->prepare("INSERT INTO Sales (user_id, sale_date, total_amount) VALUES (?, NOW(), ?)");
            $sale_stmt->bind_param("id", $user_id, $total_amount);
            $sale_stmt->execute();
            $sale_id = $conn->insert_id;

            // Step 4: Insert payment record
            $payment_stmt = $conn->prepare("INSERT INTO Payments (sale_id, payment_method, amount_paid) VALUES (?, ?, ?)");
            $payment_stmt->bind_param("isd", $sale_id, $payment_method, $amount_paid);
            $payment_stmt->execute();

            // Step 5: Insert sale items and update inventory
            foreach ($cart as $item) {
                $sale_item_stmt = $conn->prepare("INSERT INTO Sale_Items (sale_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
                $sale_item_stmt->bind_param("iiii", $sale_id, $item['id'], $item['quantity'], $item['total']);
                $sale_item_stmt->execute();

                // Update inventory stock
                $update_stock_stmt = $conn->prepare("UPDATE Inventory SET quantity = quantity - ? WHERE product_id = ?");
                $update_stock_stmt->bind_param("ii", $item['quantity'], $item['id']);
                $update_stock_stmt->execute();
            }

            // Clear the cart after sale
            $_SESSION['cart'] = [];

            header("Location: sales.php?success=Sale completed and payment processed successfully");
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Page</title>
    <link rel="stylesheet" href="/css/account-management.css">
    <style>
        .payment-form {
            border: 1px solid #ccc;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .payment-form input {
            box-sizing: border-box;
        }

        .payment-form label {
            font-weight: 600;
        }

        .payment-form #payment_method {
            display: block;
        }
    </style>
</head>

<body>
    <header>
        <div class="navbar-cashier">
            <a href="#cart-summary">Cart</a>
            <a href="sales-history.php">History</a>
            <a href="logout.php">log out</a>
        </div>
    </header>

    <div class="container">
        <h2>Sales Page</h2>

        <?php if ($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php elseif ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="search-bar">
            <form action="sales.php" method="get">
                <input type="text" name="search" placeholder="Search products..."
                    value="<?php echo htmlspecialchars($search_term); ?>" />
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="products-list">
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['product_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>₱<?php echo number_format($row['price'], 2); ?></td>
                            <td>
                                <?php
                                if ($row['quantity'] == 0) {
                                    echo "<span style='color: red; font-weight: bold;'>Unavailable</span>";
                                } else {
                                    echo $row['quantity'];
                                }
                                ?>
                            </td>
                            <td>
                                <form action="sales.php" method="post" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <input type="number" name="quantity" value="1" min="1"
                                        max="<?php echo $row['quantity']; ?>" required>
                                    <button type="submit" name="add_to_cart">Add to Cart</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div id="cart-summary">
            <h3>Shopping Cart</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total = 0; // Initialize grand total
                    foreach ($cart as $product):
                        $grand_total += $product['total']; // Sum up total of each item
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['quantity']; ?></td>
                            <td>₱<?php echo number_format($product['total'], 2); ?></td>
                            <td>
                                <form action="sales.php" method="post" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>"
                                        min="1">
                                    <button type="submit" name="edit_cart">Edit</button>
                                </form>
                                <form action="sales.php" method="post" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="remove_from_cart">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;"><strong>Grand Total:</strong></td>
                        <td><strong>₱<?php echo number_format($grand_total, 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <form action="sales.php" method="post" class="payment-form">
                <h3>Payment Information</h3>

                <!-- Display automatically calculated amount to be paid -->
                <label for="amount_to_be_paid">Amount to be Paid:</label>
                <input type="text" id="amount_to_be_paid" name="amount_to_be_paid"
                    value="₱<?php echo number_format($grand_total, 2); ?>" readonly />
                <label for="payment_method">Payment Method:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="cash">Cash</option>
                    <!-- <option value="credit_card">Credit Card</option>
                    <option value="debit_card">Debit Card</option> -->
                    <option value="mobile_payment">Gcash</option>
                </select>

                <!-- User inputs the money the customer gives -->
                <label for="customer_money">Customer's Money:</label>
                <input type="number" name="customer_money" id="customer_money" required min="0" step="0.01" />

                <!-- Display automatically calculated change -->
                <label for="change">Change:</label>
                <input type="text" id="change" name="change" value="₱0.00" readonly />

                <!-- Hidden field to store the amount paid -->
                <input type="hidden" id="amount_paid" name="amount_paid" />

                <button type="submit" name="process_payment" class="btn btn-success">Process Payment</button>
            </form>

            <script>
                // This script calculates the change automatically and updates the amount_paid field
                document.getElementById('customer_money').addEventListener('input', function () {
                    const amountToBePaid = <?php echo $grand_total; ?>;
                    const customerMoney = parseFloat(this.value) || 0;
                    const change = customerMoney - amountToBePaid;

                    // Update the change field
                    document.getElementById('change').value = '₱' + (change >= 0 ? change.toFixed(2) : '0.00');

                    // Set the amount paid (amount paid is the customer money given)
                    document.getElementById('amount_paid').value = customerMoney.toFixed(2);
                });
            </script>


        </div>


    </div>
</body>

</html>