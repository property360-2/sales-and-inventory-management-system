<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation</title>
    <style>
        :root {
            --primary-dark: rgb(67, 56, 120);
            --primary: rgb(126, 96, 191);
            --secondary: rgb(228, 177, 240);
            --accent: rgb(255, 225, 255);

            --font-color: #fff;
            --font-light: #f8f9fa;
            --bg-color: #f3f4f6;
            --error: #ff0000;
            --success: #00ff00;
        }

        .nav-bar {
            background-color: var(--primary-dark);
            margin: 5px 46px;
            border-radius: 5px;
        }

        .nav-bar a {
            color: var(--font-color);
            text-decoration: none;
            padding: 10px;
            display: inline-block;
            transition: 0.3s ease;
        }

        .nav-bar a:hover {
            background-color: var(--primary);
            transition: 0.3s ease;
        }
    </style>
</head>

<body>
    <div class="nav-bar">
        <a href="Account-Management.php">Account Management</a>
        <a href="inventory-management.php">Inventory Management</a>
        <a href="Report.php">Reports</a>
        <a href="logout.php">logout</a>
        <a href="admin-archives.php">Archives</a>
    </div>
</body>

</html>