<?php
session_start();
require_once 'include/Database-connector.php';

// Ensure user is an admin and prevent CSRF attacks
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['csrf_token']) || $_GET
['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token.");
}

// Fetch report filters (date range)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build WHERE clause dynamically
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

// Prepare CSV file for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=business_report.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Section', 'Field', 'Value']); // Column headers

// Fetch Total Sales & Revenue
$total_sales_sql = "SELECT SUM(total_amount) AS total_revenue FROM Sales $date_filter";
$total_sales_stmt = $conn->prepare($total_sales_sql);
if (!empty($params)) {
    $total_sales_stmt->bind_param($types, ...$params);
}
$total_sales_stmt->execute();
$total_sales_result = $total_sales_stmt->get_result()->fetch_assoc();
$total_sales_stmt->close();

fputcsv($output, ['Total Sales Overview', 'Total Revenue', $total_sales_result['total_revenue']]);

// Fetch Sales by User
$sales_by_user_sql = "SELECT u.username, SUM(s.total_amount) AS total_sales 
                      FROM Sales s JOIN Users u ON s.user_id = u.user_id $date_filter 
                      GROUP BY u.username";
$sales_by_user_stmt = $conn->prepare($sales_by_user_sql);
if (!empty($params)) {
    $sales_by_user_stmt->bind_param($types, ...$params);
}
$sales_by_user_stmt->execute();
$sales_by_user_result = $sales_by_user_stmt->get_result();

fputcsv($output, []);
fputcsv($output, ['Sales by User']);
fputcsv($output, ['Username', 'Total Sales']);
while ($row = $sales_by_user_result->fetch_assoc()) {
    fputcsv($output, [$row['username'], $row['total_sales']]);
}

// Fetch Stock Levels
$stock_levels = [
    'Low Stock' => "SELECT name, quantity FROM Inventory WHERE quantity < 100",
    'Optimal Stock' => "SELECT name, quantity FROM Inventory WHERE quantity BETWEEN 100 AND 200",
    'High Stock' => "SELECT name, quantity FROM Inventory WHERE quantity > 200"
];

foreach ($stock_levels as $label => $sql) {
    fputcsv($output, []);
    fputcsv($output, [$label]);
    fputcsv($output, ['Product Name', 'Stock Quantity']);

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['name'], $row['quantity']]);
    }
}

// Fetch Product Sales Performance
$product_sales_sql = "SELECT i.name, SUM(si.quantity) AS total_quantity_sold, SUM(si.subtotal) AS total_sales
                      FROM Sale_Items si
                      JOIN Inventory i ON si.product_id = i.product_id
                      GROUP BY si.product_id";
$product_sales_stmt = $conn->prepare($product_sales_sql);
$product_sales_stmt->execute();
$product_sales_result = $product_sales_stmt->get_result();

fputcsv($output, []);
fputcsv($output, ['Product Sales Performance']);
fputcsv($output, ['Product Name', 'Quantity Sold', 'Total Sales']);
while ($row = $product_sales_result->fetch_assoc()) {
    fputcsv($output, [$row['name'], $row['total_quantity_sold'], $row['total_sales']]);
}

fclose($output);
exit();
?>
