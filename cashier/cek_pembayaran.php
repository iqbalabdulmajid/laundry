<?php
// Start the session
session_start();

// Check if user is logged in and is either an admin or a cashier
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'cashier'])) {
    header('Location: login.php'); // Change 'login.php' to your actual login page
    exit; // Stop further execution of the script
}

// Include the database connection
include '../database.php';

// Fetch all transactions with user and bank details from the database
$query = "SELECT t.id, t.faktur, t.tanggal_transaksi, t.bukti_pembayaran, 
                 c.nama AS customer_name, b.nama_bank
          FROM transaksi_laundry t
          LEFT JOIN data_customer c ON t.id_pelanggan = c.id
          LEFT JOIN detail_bank_laundry b ON t.id_bank = b.id";

$stmt = $pdo->prepare($query);
$stmt->execute();

// Check for SQL execution errors
if ($stmt->errorCode() !== '00000') {
    $errorInfo = $stmt->errorInfo();
    die("SQL Error: " . $errorInfo[2]);
}

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Transaksi - Cabang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Styling as provided */
    </style>
</head>

<body>

    <?php include 'sidebar_cashier.php'; ?>

    <div class="container mt-4">
                <h4 class="card-title">Data Transaksi</h4>
                <table id="transactionsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer Name</th>
                            <th>Transaction Date</th>
                            <th>Bank Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo $transaction['faktur']; ?></td>
                                <td><?php echo $transaction['customer_name']; ?></td>
                                <td><?php echo $transaction['tanggal_transaksi']; ?></td>
                                <td><?php echo $transaction['nama_bank']; ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm view-proof-btn" data-bs-toggle="modal"
                                        data-bs-target="#proofModal" data-payment-proof="<?php echo $transaction['bukti_pembayaran']; ?>">
                                        View Payment Proof
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Payment Proof -->
    <div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title" id="proofModalLabel">Payment Proof</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="paymentProofImage" src="" alt="Payment Proof" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#transactionsTable').DataTable();

            // Handle showing the payment proof in the modal
            $('.view-proof-btn').on('click', function() {
                var paymentProof = $(this).data('payment-proof');
                var imageUrl = './' + paymentProof; // Adjust path as needed
                $('#paymentProofImage').attr('src', imageUrl);
            });
        });
    </script>
</body>
</html>
