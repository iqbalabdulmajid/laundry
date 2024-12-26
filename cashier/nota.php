<?php
session_start();
require_once '../database.php';

// Cek jika admin sudah login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'cashier') {
    header("Location: login.php");
    exit;
}

// Proses jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_number = $_POST['invoice_number'] ?? '';

    // Cek apakah nomor invoice valid
    $stmt = $pdo->prepare("SELECT faktur FROM transaksi_laundry WHERE faktur = ?");
    $stmt->execute([$invoice_number]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        // Redirect ke halaman generate_invoice.php dengan faktur
        header("Location: generate_invoice.php?faktur=" . urlencode($transaction['faktur']));
        exit;
    } else {
        $error_message = "Nomor invoice tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Input Nomor Invoice</title>
</head>
<body>
<?php include 'sidebar_cashier.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">Masukkan Nomor Invoice untuk Cetak Nota</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="nota.php">
            <div class="mb-3">
                <label for="invoice_number" class="form-label">Nomor Invoice</label>
                <input type="text" name="invoice_number" id="invoice_number" class="form-control" placeholder="Masukkan nomor invoice" required>
            </div>
            <button type="submit" class="btn btn-primary">Cetak Nota</button>
        </form>
    </div>
</body>
</html>
