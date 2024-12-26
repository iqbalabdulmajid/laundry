<?php
session_start(); // Start the session

// Check if user is logged in and is an admin, cashier, or user
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'cabang' && $_SESSION['user']['role'] !== 'cashier' && $_SESSION['user']['role'] !== 'user')) {
    header('Location: login.php');
    exit;
}

// Include the database connection
include '../database.php'; // Ensure your DB connection is correct

// Get the logged-in user's ID
$userId = $_SESSION['user']['id'];

// Initialize variables for invoice checking
$currentStatus = 'menunggu pembayaran';
$statusMessage = '';

// Checking for a submitted transaction code
$transaction_code = $_GET['transaction_code'] ?? null;

if ($transaction_code) {
    $transaction_code = htmlspecialchars($transaction_code);

    // Query to check the transaction in the database
    $stmt = $pdo->prepare("SELECT * FROM transaksi_laundry WHERE faktur = :transaction_code AND id_akun = :user_id");
    $stmt->bindParam(':transaction_code', $transaction_code);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        // Get the laundry status from the transaction data
        $currentStatus = $transaction['status'];
        $statusMessage = "Status untuk Faktur ID {$transaction_code}: {$currentStatus}";

        // Update counts dynamically based on status
        if ($currentStatus === 'selesai') {
            $updateStatus = $pdo->prepare("UPDATE transaksi_laundry SET status = 'laundry sedang di ambil' WHERE faktur = :transaction_code");
            $updateStatus->bindParam(':transaction_code', $transaction_code);
            $updateStatus->execute();
        } elseif ($currentStatus === 'laundry sedang di ambil') {
            $updateStatus = $pdo->prepare("UPDATE transaksi_laundry SET status = 'diambil' WHERE faktur = :transaction_code");
            $updateStatus->bindParam(':transaction_code', $transaction_code);
            $updateStatus->execute();
        }
    } else {
        $statusMessage = "No transaction found with Faktur ID {$transaction_code}.";
    }
}

// Fetch the count of laundry orders for the logged-in user
$laundryCountQuery = "SELECT COUNT(*) as total_laundry FROM transaksi_laundry WHERE id_akun = :user_id";
$laundryCountStmt = $pdo->prepare($laundryCountQuery);
$laundryCountStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$laundryCountStmt->execute();
$laundryCountResult = $laundryCountStmt->fetch(PDO::FETCH_ASSOC);
$totalLaundry = $laundryCountResult['total_laundry'] ?? 0;

// Fetch the count of completed laundry
$completedLaundryQuery = "SELECT COUNT(*) as completed_laundry FROM transaksi_laundry WHERE id_akun = :user_id AND status = 'laundry selesai'";
$completedLaundryStmt = $pdo->prepare($completedLaundryQuery);
$completedLaundryStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$completedLaundryStmt->execute();
$completedLaundryResult = $completedLaundryStmt->fetch(PDO::FETCH_ASSOC);
$totalCompletedLaundry = $completedLaundryResult['completed_laundry'] ?? 0;

// Fetch the count of laundry picked up
$pickedLaundryQuery = "SELECT COUNT(*) as picked_laundry FROM transaksi_laundry WHERE id_akun = :user_id AND status = 'selesai'";
$pickedLaundryStmt = $pdo->prepare($pickedLaundryQuery);
$pickedLaundryStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$pickedLaundryStmt->execute();
$pickedLaundryResult = $pickedLaundryStmt->fetch(PDO::FETCH_ASSOC);
$totalPickedLaundry = $pickedLaundryResult['picked_laundry'] ?? 0;

// Fetch the count of laundry masuk
$laundryOrderQuery = "SELECT COUNT(*) as laundry_order FROM transaksi_laundry WHERE id_akun = :user_id AND status = 'pending'";
$laundryOrderStmt = $pdo->prepare($laundryOrderQuery);
$laundryOrderStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$laundryOrderStmt->execute();
$laundryOrderResult = $laundryOrderStmt->fetch(PDO::FETCH_ASSOC);
$laundryOrder = $laundryOrderResult['laundry_order'] ?? 0;

$statusList = [
    'pending',
    'menunggu pembayaran',
    'laundry sedang di ambil',
    'sedang proses laundry',
    'laundry selesai',
    'laundry sedang di antar',
    'selesai'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php include 'sidebar_user.php'; ?>

    <div class="container-fluid">
        <h1 class="dashboard-title">Dashboard</h1>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Jumlah Laundry Dipesan</h5>
                        <h1 id="laundryOrderedCount"><?php echo $totalLaundry; ?></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Laundry Selesai</h5>
                        <h1 id="laundryCompletedCount"><?php echo $totalCompletedLaundry; ?></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Laundry Diambil</h5>
                        <h1 id="laundryPickedCount"><?php echo $totalPickedLaundry; ?></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Laundry Masuk</h5>
                        <h1 id="laundryInCount"><?php echo $laundryOrder; ?></h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Check Form -->
        <div class="mb-4">
            <form method="GET" action="">
                <div class="input-group">
                    <input type="text" name="transaction_code" class="form-control" placeholder="Masukkan Kode Transaksi" required>
                    <button class="btn btn-primary" type="submit">Cek Status</button>
                </div>
            </form>
            <p class="text-success"><?php echo $statusMessage; ?></p>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
