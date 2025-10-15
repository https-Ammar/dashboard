<?php
require('../db.php');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
$total_sales = 0;
$total_cars_sold = 0;
$total_customers = 0;
$total_brands = 0;
$total_categories = 0;
$total_cars = 0;
$total_orders = 0;
$total_pending_orders = 0;
$total_completed_orders = 0;
$sql_sales = "SELECT SUM(c.price) as total_sales, COUNT(o.id) as total_cars_sold
              FROM `order` o
              LEFT JOIN car_cars c ON o.car_id = c.id
              WHERE o.state = 'مكتمل'";
$result_sales = $conn->query($sql_sales);
if ($result_sales && $row = $result_sales->fetch_assoc()) {
    $total_sales = $row['total_sales'] ?? 0;
    $total_cars_sold = $row['total_cars_sold'] ?? 0;
}
$result_sales->close();
$sql_customers = "SELECT COUNT(DISTINCT userid) as total_customers
                  FROM `order`
                  WHERE userid != 0";
$result_customers = $conn->query($sql_customers);
if ($result_customers && $row = $result_customers->fetch_assoc()) {
    $total_customers = $row['total_customers'];
}
$result_customers->close();
$sql_brands = "SELECT COUNT(id) as total_brands FROM car_brands";
$result_brands = $conn->query($sql_brands);
if ($result_brands && $row = $result_brands->fetch_assoc()) {
    $total_brands = $row['total_brands'];
}
$result_brands->close();
$sql_categories = "SELECT COUNT(id) as total_categories FROM car_categories";
$result_categories = $conn->query($sql_categories);
if ($result_categories && $row = $result_categories->fetch_assoc()) {
    $total_categories = $row['total_categories'];
}
$result_categories->close();
$sql_cars = "SELECT COUNT(id) as total_cars FROM car_cars";
$result_cars = $conn->query($sql_cars);
if ($result_cars && $row = $result_cars->fetch_assoc()) {
    $total_cars = $row['total_cars'];
}
$result_cars->close();
$sql_orders = "SELECT COUNT(id) as total_orders,
                      SUM(CASE WHEN state = 'مكتمل' THEN 1 ELSE 0 END) as completed_orders,
                      SUM(CASE WHEN state != 'مكتمل' THEN 1 ELSE 0 END) as pending_orders
               FROM `order`";
$result_orders = $conn->query($sql_orders);
if ($result_orders && $row = $result_orders->fetch_assoc()) {
    $total_orders = $row['total_orders'];
    $total_completed_orders = $row['completed_orders'];
    $total_pending_orders = $row['pending_orders'];
}
$result_orders->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إحصائيات لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./main.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card i {
            font-size: 2rem;
            color: #bc1e2c;
            margin-bottom: 10px;
        }
        .stat-card h4 {
            font-size: 1.2rem;
            color: #374151;
            margin: 10px 0;
        }
        .stat-card p {
            font-size: 1.5rem;
            font-weight: bold;
            color: #bc1e2c;
            margin: 0;
        }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background-color: #bc1e2c;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
        }
        .btn-back:hover {
            background-color: #a31925;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-2xl font-bold text-center mb-6">إحصائيات لوحة التحكم</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-money-bill-wave"></i>
                <h4>إجمالي المبيعات</h4>
                <p><?php echo number_format($total_sales, 2); ?> ريال</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-car"></i>
                <h4>عدد السيارات المحجوزة</h4>
                <p><?php echo $total_cars_sold; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h4>إجمالي العملاء</h4>
                <p><?php echo $total_customers; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-tags"></i>
                <h4>عدد الماركات</h4>
                <p><?php echo $total_brands; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-list"></i>
                <h4>عدد الفئات</h4>
                <p><?php echo $total_categories; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-car-side"></i>
                <h4>إجمالي السيارات</h4>
                <p><?php echo $total_cars; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <h4>إجمالي الطلبات</h4>
                <p><?php echo $total_orders; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-hourglass-half"></i>
                <h4>الطلبات قيد الانتظار</h4>
                <p><?php echo $total_pending_orders; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <h4>الطلبات المكتملة</h4>
                <p><?php echo $total_completed_orders; ?></p>
            </div>
        </div>
        <a href="index.php" class="btn-back">العودة إلى لوحة التحكم</a>
    </div>
</body>
</html>