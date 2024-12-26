<?php
// Include the database connection
require 'database.php';

// Simulating login state and user data for demonstration
$loggedIn = false; // Default to false
$userId = null;
$userName = ''; // Placeholder for user's name

// Check if the user is logged in (by checking a session variable)
session_start(); // Make sure session is started
if (isset($_SESSION['user'])) {
    $loggedIn = true;
    $userId = $_SESSION['user']['id']; // Access user ID from session
    $userName = $_SESSION['user']['username']; // Access username from session
}

// Initialize transaction data variables
$transaction = null;
$laundryStatus = null;
$transaction_code = null;

// Checking for a submitted transaction code
if (isset($_GET['transaction_code'])) {
    $transaction_code = htmlspecialchars($_GET['transaction_code']);
    
    // Query to check the transaction in the database
    $stmt = $pdo->prepare("SELECT * FROM transaksi_laundry WHERE faktur = :transaction_code");
    $stmt->execute(['transaction_code' => $transaction_code]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        // Get the laundry status from the transaction data
        $laundryStatus = $transaction['status']; // Assuming the status field exists
        echo "<script>var laundryStatus = '" . htmlspecialchars($laundryStatus) . "';</script>"; // Pass status to JavaScript
    } else {
        echo "<script>var invalidTransaction = true;</script>"; // For SweetAlert if transaction is not found
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Laundry</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.0/sweetalert2.min.css" rel="stylesheet">
</head>
<body>

<!-- Header -->
<header class="bg-light py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <img src="assets/images/images.png" alt="E-Laundry Logo" class="img-fluid" style="width: 50px;"> <!-- Replace with your logo -->
        <?php if ($loggedIn): ?>
    <div class="dropdown">
        <a href="#" class="text-dark dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            Halo, <?= htmlspecialchars($userName); ?>
        </a>
        <ul class="dropdown-menu" aria-labelledby="userDropdown">
            <?php 
            // Determine which dashboard to send the user based on their role
            if ($_SESSION['user']['role'] === 'admin') {
                $dashboard = 'admin/dashboard_admin.php';
            } elseif ($_SESSION['user']['role'] === 'cabang') {
                $dashboard = 'cabang/dashboard_cabang.php';
            } elseif ($_SESSION['user']['role'] === 'cashier') {
                $dashboard = 'cashier/dashboard_cashier.php';
            } elseif ($_SESSION['user']['role'] === 'user') {
                $dashboard = 'users/dashboard_user.php';
            } 
            ?>
            <li><a class="dropdown-item" href="<?= htmlspecialchars($dashboard); ?>">Go to Panel</a></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
        </ul>
    </div>
<?php else: ?>
    <a href="login.php" class="text-dark">Masuk</a>
<?php endif; ?>
    </div>
</header>

<!-- Search Section -->
<section class="text-white text-center py-5" style="background-image: url('assets/images/banner.jpg'); background-size: cover; background-position: center;">
    <div class="container">
        <h1>Lacak Status Laundry Kamu Disini.</h1>
        <form action="" method="GET" class="mt-4">
            <div class="input-group mb-3 mx-auto" style="max-width: 400px;">
                <input type="text" name="transaction_code" class="form-control" placeholder="Masukan nomor invoice" aria-label="Transaction Code" required>
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </form>
    </div>
</section>
<!-- Solution Section -->
<section class="text-center py-5">
    <div class="container">
        <h2 class="mb-3">Solusi Pakaian Kotor Ingat Kami!</h2>
        <p class="lead">Tunggu aja dirumah, biar kurir kami yang jemput dan antar pakaian kotor kamu :)</p>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3">
                <img src="assets/images/order.png" alt="Order Online" class="img-fluid mb-3">
                <h3>Order Online</h3>
            </div>
            <div class="col-md-3">
                <img src="assets/images/profesional.png" alt="Profesional" class="img-fluid mb-3">
                <h3>Profesional</h3>
            </div>
            <div class="col-md-3">
                <img src="assets/images/terpercaya.png" alt="Terpercaya" class="img-fluid mb-3">
                <h3>Terpercaya</h3>
            </div>
            <div class="col-md-3">
                <img src="assets/images/garansi.png" alt="Bergaransi" class="img-fluid mb-3">
                <h3>Bergaransi</h3>
            </div>
        </div>
    </div>
</section>
<!-- Footer -->
<footer class="bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h4>Tentang E-Laundry</h4>
                <p>Ini adalah aplikasi laundry.</p>
            </div>
            <div class="col-md-4">
                <h4>Ketentuan</h4>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white">FAQ</a></li>
                    <li><a href="#" class="text-white">Join Laundry</a></li>
                    <li><a href="#" class="text-white">Investasi</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h4>Hubungi Kami</h4>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white">Facebook</a></li>
                    <li><a href="#" class="text-white">Instagram</a></li>
                    <li><a href="#" class="text-white">WhatsApp</a></li>
                </ul>
            </div>
        </div>
        <p class="text-center mt-4">&copy; 2024 Build With ❤️</p>
    </div>
</footer>

<!-- WhatsApp Button -->
<a href="https://wa.me/1234567890" class="btn btn-success btn-lg position-fixed bottom-0 end-0 m-3 rounded-circle">
    <i class="bi bi-whatsapp"></i>
</a>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.0/sweetalert2.all.min.js"></script>

<!-- Handle SweetAlert for transaction status -->
<script>
    // Display laundry status in SweetAlert if found
    if (typeof laundryStatus !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Laundry Status',
            text: 'Your laundry status is: ' + laundryStatus
        });
    }

    // Handle SweetAlert for invalid transaction
    if (typeof invalidTransaction !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Transaction code not found. Please try again!',
        });
    }
</script>

</body>
</html>
