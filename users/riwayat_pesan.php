<?php
session_start(); // Start the session

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    // Redirect to login page
    header('Location: login.php'); // Change 'login.php' to your actual login page
    exit; // Stop further execution of the script
}

// Include the database connection
include '../database.php'; // Make sure this file sets up the connection properly

// Get the logged-in user's ID
$user_id = $_SESSION['user']['id'];

// Fetch transactions for the logged-in user, including the charge column
$query = "SELECT t.id, t.faktur, t.jumlah AS weight, t.biaya AS total, 
                 t.charge, t.tanggal_transaksi AS transaction_date, t.status, 
                 t.alamat, t.kode_pembayaran AS payment_code, 
                 p.nama AS package_name 
          FROM transaksi_laundry t 
          LEFT JOIN paket_laundry p ON t.id_paket = p.id 
          WHERE t.id_akun = :user_id"; // Ensure customer_id relates to the user

$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle file upload for payment proof
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['id']; // Use 'id' for the transaction

    // Check if a file is uploaded
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $allowed_file_types = ['image/jpeg', 'image/png', 'application/pdf']; // Allowed file types
        $file_size_limit = 5 * 1024 * 1024; // 5MB file size limit

        // Check file type
        if (!in_array($_FILES['payment_proof']['type'], $allowed_file_types)) {
            $_SESSION['error_message'] = "File type not allowed. Please upload a JPG, PNG, or PDF file.";
        }
        // Check file size
        elseif ($_FILES['payment_proof']['size'] > $file_size_limit) {
            $_SESSION['error_message'] = "File size exceeds the 5MB limit.";
        } else {
            $upload_dir = 'uploads/payment_proofs/';
            // Ensure the upload directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create the directory if it does not exist
            }
            $file_name = uniqid() . '_' . basename($_FILES['payment_proof']['name']);
            $file_path = $upload_dir . $file_name;

            // Move uploaded file to the server directory
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $file_path)) {
                // Update the transaction with the payment proof path
                $query = "UPDATE transaksi_laundry SET bukti_pembayaran = :payment_proof WHERE id = :id";
                $stmt = $pdo->prepare($query);

                $params = [
                    'payment_proof' => $file_path,
                    'id' => $transaction_id // Use 'id' for the parameter
                ];

                if ($stmt->execute($params)) {
                    $_SESSION['success_message'] = "Bukti pembayaran berhasil diunggah.";
                } else {
                    $_SESSION['error_message'] = "Gagal memperbarui transaksi.";
                }
            } else {
                $_SESSION['error_message'] = "Gagal memindahkan file yang diunggah.";
            }
        }
    } else {
        $_SESSION['error_message'] = "Tidak ada file yang diunggah atau terjadi kesalahan saat mengunggah.";
    }

    header('Location: riwayat_pesan.php'); // Redirect back to the transaction history page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>

<body>

    <?php include 'sidebar_user.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Riwayat Pesan</h1>

        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table id="transactionTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Kode Pembayaran</th>
                        <th>Nota</th>
                        <th>Jenis Laundry</th>
                        <th>Berat(kg)</th>
                        <th>Total</th>
                        <th>Charge</th> <!-- Display the charge column -->
                        <th>Tanggal Transaksi</th>
                        <th>Status</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['payment_code'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($transaction['faktur'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($transaction['package_name'] ?? ''); ?></td>
                            <td><?php echo number_format((float) ($transaction['weight'] ?? 0), 2); ?> KG</td>
                            <td>Rp <?php echo number_format((float) (($transaction['total'] ?? 0) + ($transaction['charge'] ?? 0)), 2); ?></td>
                            <td>Rp <?php echo number_format((float) ($transaction['charge'] ?? 0), 2); ?></td>
                            <td><?php echo htmlspecialchars($transaction['transaction_date'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($transaction['status'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($transaction['alamat'] ?? ''); ?></td>
                            <td>
                                <a href="generate_invoice.php?transaction_id=<?php echo $transaction['id']; ?>" class="btn btn-primary btn-sm" target="_blank">Cetak Nota</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#transactionTable').DataTable();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>