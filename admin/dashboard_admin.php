<?php
session_start();

// Redirect jika user belum login atau bukan admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Koneksi ke database
include '../database.php';

// Fetch data utama untuk dashboard
$customerCount = $pdo->query("SELECT COUNT(*) as total FROM data_customer")->fetch(PDO::FETCH_ASSOC)['total'];
$laundryInCount = $pdo->query("SELECT COUNT(*) as total FROM transaksi_laundry WHERE status NOT IN ('laundry selesai', 'selesai')")->fetch(PDO::FETCH_ASSOC)['total'];
$laundryCompletedCount = $pdo->query("SELECT COUNT(*) as total FROM transaksi_laundry WHERE status = 'laundry selesai'")->fetch(PDO::FETCH_ASSOC)['total'];
$laundryPickedCount = $pdo->query("SELECT COUNT(*) as total FROM transaksi_laundry WHERE status = 'selesai '")->fetch(PDO::FETCH_ASSOC)['total'];

// Pendapatan berdasarkan periode
$todayIncome = $pdo->query("SELECT SUM(biaya) as total FROM transaksi_laundry WHERE DATE(tanggal_transaksi) = CURDATE()")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$yesterdayIncome = $pdo->query("SELECT SUM(biaya) as total FROM transaksi_laundry WHERE DATE(tanggal_transaksi) = CURDATE() - INTERVAL 1 DAY")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$monthlyIncome = $pdo->query("SELECT SUM(biaya) as total FROM transaksi_laundry WHERE MONTH(tanggal_transaksi) = MONTH(CURDATE()) AND YEAR(tanggal_transaksi) = YEAR(CURDATE())")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$annualIncome = $pdo->query("SELECT SUM(biaya) as total FROM transaksi_laundry WHERE YEAR(tanggal_transaksi) = YEAR(CURDATE())")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Data laundry harian untuk grafik
$dailyLaundryData = $pdo->query("
    SELECT DATE(tanggal_transaksi) as day, COUNT(*) as count 
    FROM transaksi_laundry 
    WHERE MONTH(tanggal_transaksi) = MONTH(CURDATE()) AND YEAR(tanggal_transaksi) = YEAR(CURDATE())
    GROUP BY day
")->fetchAll(PDO::FETCH_ASSOC);

// Format data harian untuk grafik
$dailyDataFormatted = [];
for ($i = 1; $i <= date('t'); $i++) {
    $dailyDataFormatted[$i] = 0;
}
foreach ($dailyLaundryData as $data) {
    $day = (int) date('j', strtotime($data['day']));
    $dailyDataFormatted[$day] = (int) $data['count'];
}
$dailyDataJson = json_encode($dailyDataFormatted);

// Data laundry bulanan untuk grafik
$monthlyLaundryData = $pdo->query("
    SELECT MONTH(tanggal_transaksi) as month, COUNT(*) as count 
    FROM transaksi_laundry 
    WHERE YEAR(tanggal_transaksi) = YEAR(CURDATE())
    GROUP BY month
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="admin/css/style.css">
</head>

<body>
    <?php include 'sidebar_admin.php'; ?>

    <div class="container-fluid">
        <h1 class="dashboard-title">Dashboard</h1>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Jumlah Customer</h5>
                        <h1><?= $customerCount ?></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Laundry Masuk</h5>
                        <h1><?= $laundryInCount ?></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Laundry Selesai</h5>
                        <h1><?= $laundryCompletedCount ?></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Laundry Diambil</h5>
                        <h1><?= $laundryPickedCount ?></h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Pendapatan</div>
                    <div class="card-body">
                        <h5>Pendapatan Tahunan: Rp <?= number_format($annualIncome, 0, ',', '.') ?></h5>
                        <h5>Pendapatan Bulanan: Rp <?= number_format($monthlyIncome, 0, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Pendapatan Harian</div>
                    <div class="card-body">
                        <h5>Hari ini: Rp <?= number_format($todayIncome, 0, ',', '.') ?></h5>
                        <h5>Kemarin: Rp <?= number_format($yesterdayIncome, 0, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h3>Data Laundry Masuk Per-hari</h3>
                <canvas id="dailyLaundryChart"></canvas>
            </div>
            <div class="col-md-6">
                <h3>Data Laundry Masuk Per-bulan</h3>
                <canvas id="monthlyLaundryChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Data harian untuk grafik
        const dailyData = <?= $dailyDataJson ?>;

        const dailyLabels = Object.keys(dailyData).map(day => `Hari ${day}`);
        const dailyCounts = Object.values(dailyData);

        const dailyChartCtx = document.getElementById('dailyLaundryChart').getContext('2d');
        new Chart(dailyChartCtx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Laundry Masuk Per-hari',
                    data: dailyCounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Data bulanan untuk grafik
        const monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthlyCounts = Array(12).fill(0);
        const monthlyData = <?= json_encode($monthlyLaundryData) ?>;
        monthlyData.forEach(data => {
            monthlyCounts[data.month - 1] = data.count;
        });

        const monthlyChartCtx = document.getElementById('monthlyLaundryChart').getContext('2d');
        new Chart(monthlyChartCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Laundry Masuk Per-bulan',
                    data: monthlyCounts,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>
