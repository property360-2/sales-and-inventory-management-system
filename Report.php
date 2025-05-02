<?php
session_start();
require_once 'include/Database-connector.php';

// 🔐 Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 🛡️ Generate CSRF Token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 🗓️ Fetch report filters (date range)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// 🔍 Build WHERE clause dynamically
$date_conditions = [];
$params = [];
$types = '';

if ($start_date) {
    $date_conditions[] = "sale_date >= ?";
    $params[] = $start_date;
    $types .= 's';
}
if ($end_date) {
    $date_conditions[] = "sale_date <= ?";
    $params[] = $end_date;
    $types .= 's';
}

$date_filter = !empty($date_conditions) ? "WHERE " . implode(" AND ", $date_conditions) : "";

// 🏷️ Fetch Total Sales & Revenue
$total_sales_sql = "SELECT SUM(total_amount) AS total_revenue FROM Sales $date_filter";
$total_sales_stmt = $conn->prepare($total_sales_sql);
if (!empty($params)) {
    $total_sales_stmt->bind_param($types, ...$params);
}
$total_sales_stmt->execute();
$total_sales_result = $total_sales_stmt->get_result()->fetch_assoc();
$total_sales_stmt->close();

// 👥 Fetch Sales by User
$sales_by_user_sql = "SELECT u.username, SUM(s.total_amount) AS total_sales 
                      FROM Sales s JOIN Users u ON s.user_id = u.user_id $date_filter 
                      GROUP BY u.username";
$sales_by_user_stmt = $conn->prepare($sales_by_user_sql);
if (!empty($params)) {
    $sales_by_user_stmt->bind_param($types, ...$params);
}
$sales_by_user_stmt->execute();
$sales_by_user_result = $sales_by_user_stmt->get_result();

// 📦 Fetch Stock Levels (Low, Optimal, High)
$stock_levels = [
    'low' => ["SELECT * FROM Inventory WHERE quantity < 100 AND name LIKE ?", isset($_GET['search_low_stock']) ? $_GET['search_low_stock'] : ''],
    'optimal' => ["SELECT * FROM Inventory WHERE quantity BETWEEN 100 AND 200 AND name LIKE ?", isset($_GET['search_optimal_stock']) ? $_GET['search_optimal_stock'] : ''],
    'high' => ["SELECT * FROM Inventory WHERE quantity > 200 AND name LIKE ?", isset($_GET['search_high_stock']) ? $_GET['search_high_stock'] : '']
];

$stock_results = [];
foreach ($stock_levels as $key => [$sql, $search]) {
    $stmt = $conn->prepare($sql);
    $search_term = "%" . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . "%";
    $stmt->bind_param('s', $search_term);
    $stmt->execute();
    $stock_results[$key] = $stmt->get_result();
}

// 📊 Fetch Product Sales Performance
$product_sales_sql = "SELECT i.name, SUM(si.quantity) AS total_quantity_sold, SUM(si.subtotal) AS total_sales
                      FROM Sale_Items si
                      JOIN Inventory i ON si.product_id = i.product_id
                      GROUP BY si.product_id";
$product_sales_stmt = $conn->prepare($product_sales_sql);
$product_sales_stmt->execute();
$product_sales_result = $product_sales_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Report</title>
    <link rel="stylesheet" href="/css/account-management.css">
</head>

<body>
    <?php include 'include/navigation-for-admin.php'; ?>

    <div class="container">
        <h2>Business Report</h2>

        <div class="overview">
            <h3>Total Sales Overview</h3>
            <p><strong>Total Revenue:</strong> ₱<?php echo number_format($total_sales_result['total_revenue'], 2); ?>
            </p>
        </div>

        <!-- 📅 Date Range Filter -->
        <form action="report.php" method="get">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="date-filter">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                <button type="submit">Filter</button>
            </div>
        </form>
        <form action="download_report.php" method="get">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
            <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
            <button type="submit">📥 Download Report</button>
        </form>


        <!-- 📈 Sales by User -->
        <div class="sales-by-user">
            <h3>Sales by User</h3>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $sales_by_user_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>₱<?php echo number_format($row['total_sales'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- 📦 Stock Levels -->
        <?php foreach ($stock_results as $key => $result): ?>
            <div class="stock-section">
                <h3><?php echo ucfirst($key); ?> Stock</h3>
                <form action="report.php" method="get">
                    <input type="text" name="search_<?php echo $key; ?>_stock" placeholder="Search product">
                    <button type="submit">Search</button>
                </form>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Stock Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        <!-- 📊 Product Sales Performance -->
        <div class="product-sales">
            <h3>Product Sales Performance</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity Sold</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $product_sales_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo $row['total_quantity_sold']; ?></td>
                            <td>₱<?php echo number_format($row['total_sales'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.querySelectorAll('input[type="date"]').forEach(input => {
            input.addEventListener('change', function () {
                this.form.submit();
            });
        });
    </script>

</body>

</html>