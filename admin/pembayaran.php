<?php
session_start(); // Start the session

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Include the database connection
include '../database.php'; // Ensure this connects to the database

// Query untuk mengambil semua data transaksi
$query = "SELECT t.id, t.faktur AS invoice, t.jumlah AS amount, t.biaya AS charge, 
                 t.tanggal_transaksi AS transaction_date, t.status, t.alamat, 
                 t.kode_pembayaran AS payment_code, p.nama AS package_name 
          FROM transaksi_laundry t 
          LEFT JOIN paket_laundry p ON t.id_paket = p.id";

$stmt = $pdo->prepare($query);
$stmt->execute(); // Eksekusi query tanpa filter berdasarkan user_id
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available bank details for payment
$bankQuery = "SELECT id, nama_bank AS bank_name, nama_akun AS account_name FROM detail_bank_laundry";
$bankStmt = $pdo->query($bankQuery);
$bankDetails = $bankStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle file upload and payment proof submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $bank_id = $_POST['bank_id']; // Get the selected bank

    // Check if a file is uploaded
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $allowed_file_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $file_size_limit = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['payment_proof']['type'], $allowed_file_types)) {
            $_SESSION['error_message'] = "Invalid file type. Please upload JPG, PNG, or PDF.";
        } elseif ($_FILES['payment_proof']['size'] > $file_size_limit) {
            $_SESSION['error_message'] = "File size exceeds the 5MB limit.";
        } else {
            $upload_dir = '../uploads/payment_proofs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create the directory if it does not exist
            }
            $file_name = uniqid() . '_' . basename($_FILES['payment_proof']['name']);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $file_path)) {
                // Update the transaction with the payment proof and bank_id
                $updateQuery = "UPDATE transaksi_laundry SET bukti_pembayaran = :payment_proof, id_bank = :bank_id WHERE id = :id";
                $stmt = $pdo->prepare($updateQuery);
                $params = [
                    'payment_proof' => $file_path,
                    'bank_id' => $bank_id, // Save the selected bank ID
                    'id' => $transaction_id
                ];

                if ($stmt->execute($params)) {
                    $_SESSION['success_message'] = "Payment proof uploaded successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to update the transaction.";
                }
            } else {
                $_SESSION['error_message'] = "Failed to move uploaded file.";
            }
        }
    } else {
        $_SESSION['error_message'] = "No file uploaded or there was an upload error.";
    }

    header('Location: pembayaran.php');
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
    <style>
        body {
            background-color: #1e1e2f;
            color: white;
        }

        .card {
            background-color: #2c2c3f;
            border: none;
        }
    </style>
</head>

<body>

    <?php include 'sidebar_admin.php'; ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Upload Bukti Pembayaran</h1>
        <!-- Display success/error message if any -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </div>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Payment Proof Upload Form -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="transaction_id" class="form-label">Pilih Transaksi (Invoice - Kode Pembayaran)</label>
                <select name="transaction_id" id="transaction_id" class="form-control" required>
                    <option value="">Pilih Transaksi</option>
                    <?php foreach ($transactions as $transaction): ?>
                        <option value="<?php echo $transaction['id']; ?>"
                            data-total="<?php echo htmlspecialchars($transaction['amount'] + $transaction['charge']); ?>">
                            <?php echo htmlspecialchars($transaction['invoice'] . ' - ' . $transaction['payment_code']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="total_amount" class="form-label">Total yang Harus Dibayar</label>
                <input type="text" id="total_amount" class="form-control" placeholder="0" readonly>
            </div>

            <div class="mb-3">
                <label for="bank_id" class="form-label">Pilih Bank</label>
                <select name="bank_id" id="bank_id" class="form-control" required>
                    <option value="">Pilih Bank</option>
                    <?php foreach ($bankDetails as $bank): ?>
                        <option value="<?php echo $bank['id']; ?>">
                            <?php echo htmlspecialchars($bank['bank_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="payment_proof" class="form-label">Upload Bukti Pembayaran</label>
                <input type="file" name="payment_proof" id="payment_proof" class="form-control"
                    accept=".jpg, .jpeg, .png, .pdf" required>
            </div>

            <button type="submit" class="btn btn-success">Submit Bukti Pembayaran</button>
        </form>
    </div>

    <script>
        document.getElementById('transaction_id').addEventListener('change', function() {
            const totalField = document.getElementById('total_amount');
            const selectedOption = this.options[this.selectedIndex];

            // Ambil nilai dari atribut data-total
            const totalAmount = selectedOption.getAttribute('data-total');
            totalField.value = totalAmount ? `Rp ${parseInt(totalAmount).toLocaleString('id-ID')}` : '0';
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>