<?php
session_start(); // Start the session

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Include database pdoection
include 'database.php'; // Pastikan file ini berisi koneksi database

// Ambil data transaksi dari database
$totalOrders = 0;
$dailyEarnings = 0;
$weeklyEarnings = 0;
$monthlyEarnings = 0;
$orderData = [];

try {
    // Query untuk total pesanan
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM transaksi_laundry");
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Query pendapatan harian
    $stmt = $pdo->query("SELECT SUM(biaya) as daily FROM transaksi_laundry WHERE DATE(tanggal_transaksi) = CURDATE()");
    $dailyEarnings = $stmt->fetch(PDO::FETCH_ASSOC)['daily'] ?? 0;

    // Query pendapatan mingguan
    $stmt = $pdo->query("SELECT SUM(biaya) as weekly FROM transaksi_laundry WHERE YEARWEEK(tanggal_transaksi, 1) = YEARWEEK(CURDATE(), 1)");
    $weeklyEarnings = $stmt->fetch(PDO::FETCH_ASSOC)['weekly'] ?? 0;

    // Query pendapatan bulanan
    $stmt = $pdo->query("SELECT SUM(biaya) as monthly FROM transaksi_laundry WHERE MONTH(tanggal_transaksi) = MONTH(CURDATE()) AND YEAR(tanggal_transaksi) = YEAR(CURDATE())");
    $monthlyEarnings = $stmt->fetch(PDO::FETCH_ASSOC)['monthly'] ?? 0;

    // Query data chart mingguan
    $stmt = $pdo->query("SELECT DAYNAME(tanggal_transaksi) as day, COUNT(*) as total FROM transaksi_laundry WHERE YEARWEEK(tanggal_transaksi, 1) = YEARWEEK(CURDATE(), 1) GROUP BY day");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $orderData[$row['day']] = $row['total'];
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'sidebar_admin.php'; ?>
<div class="container-fluid">
    <h1 class="mt-3">Dashboard Finance</h1>
    <div class="row">
        <div class="col-md-6">
            <canvas id="ordersChart"></canvas>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Earnings Overview</div>
                <div class="card-body">
                    <h5>Total Orders: <span id="totalOrders"><?php echo $totalOrders; ?></span></h5>
                    <h5>Daily Earnings: Rp <?php echo number_format($dailyEarnings, 0, ',', '.'); ?></h5>
                    <h5>Weekly Earnings: Rp <?php echo number_format($weeklyEarnings, 0, ',', '.'); ?></h5>
                    <h5>Monthly Earnings: Rp <?php echo number_format($monthlyEarnings, 0, ',', '.'); ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Data chart dari PHP
        const orderData = <?php echo json_encode($orderData); ?>;
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const dataCounts = days.map(day => orderData[day] || 0);

        // Chart.js konfigurasi
        const ctx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: days,
                datasets: [{
                    label: 'Total Orders',
                    data: dataCounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });
</script>
</body>
</html>
