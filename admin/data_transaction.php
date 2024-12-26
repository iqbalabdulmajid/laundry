<?php
// Start the session
session_start();

// Check if user is logged in and has the correct role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'cashier', 'cabang'])) {
    header('Location: login.php');
    exit;
}

// Include the database connection
include '../database.php';

// Handle AJAX requests for updating status or editing transactions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $status = $_POST['status'] ?? '';
        $transactionId = $_POST['transaction_id'] ?? 0;

        // Update transaction status in the database
        $stmt = $pdo->prepare("UPDATE transaksi_laundry SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $transactionId]);

        echo json_encode(['success' => true]);
        exit;
    }

    if ($_POST['action'] === 'edit_transaction') {
        $transactionId = $_POST['transaction_id'] ?? 0;
        $weight = $_POST['weight'] ?? 0;
        $distance = $_POST['distance'] ?? 0;
        $charge = $_POST['charge'] ?? 0;

        // Update transaction details in the database
        $stmt = $pdo->prepare("UPDATE transaksi_laundry SET jumlah = :weight, charge = :charge WHERE id = :id");
        $stmt->execute(['weight' => $weight, 'charge' => $charge, 'id' => $transactionId]);

        echo json_encode(['success' => true]);
        exit;
    }
}

// Fetch transactions with pickup and delivery dates
$query = "SELECT t.id, t.faktur AS invoice, t.jumlah AS weight, t.biaya AS total, t.tanggal_transaksi AS transaction_date, 
                 t.status, p.nama AS package_name, u.username AS customer_name, t.kode_pembayaran AS payment_code, 
                 t.bukti_pembayaran AS payment_proof, t.pickup_date AS pickup_date, t.delivery_date AS delivery_date, 
                 t.charge AS charge
          FROM transaksi_laundry t
          LEFT JOIN paket_laundry p ON t.id_paket = p.id
          LEFT JOIN users u ON t.id_pelanggan = u.id";

$statuses = [
    'pending',
    'menunggu pembayaran',
    'laundry sedang di ambil',
    'sedang proses laundry',
    'laundry selesai',
    'laundry sedang di antar',
    'selesai',
    'gagal'
];

$stmt = $pdo->prepare($query);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Transaksi - Admin/Cashier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php include 'sidebar_admin.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center">Data Transaksi</h1>

        <div class="d-flex justify-content-end mb-3">
            <a href="generate_data.php" class="btn btn-warning" target="_blank">Cetak Semua Transaksi</a>
            <a href="pesan.php" class="btn btn-primary ms-2">Tambah Transaksi</a>
        </div>

        <div class="table-responsive">
            <table id="transactionTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Invoice</th>
                        <th>Jenis Laundry</th>
                        <th>Berat (kg)</th>
                        <th>Charge</th>
                        <th>Total</th>
                        <th>Tanggal Transaksi</th>
                        <th>Status</th>
                        <th>Pickup Date</th>
                        <th>Delivery Date</th>
                        <th>Bukti Pembayaran</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['id'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($transaction['invoice'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($transaction['package_name'] ?? ''); ?></td>
                            <td><?php echo number_format((float) ($transaction['weight'] ?? 0), 2); ?> KG</td>
                            <td>Rp <?php echo number_format((float) ($transaction['charge'] ?? 0), 2); ?></td>
                            <td>Rp <?php echo number_format((float) (($transaction['total'] ?? 0) + ($transaction['charge'] ?? 0)), 2); ?></td>
                            <td><?php echo htmlspecialchars($transaction['transaction_date'] ?? ''); ?></td>
                            <td id="status-<?php echo $transaction['id']; ?>"><?php echo htmlspecialchars($transaction['status'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($transaction['pickup_date'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($transaction['delivery_date'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($transaction['payment_proof'])): ?>
                                    <a href="./<?php echo htmlspecialchars($transaction['payment_proof']); ?>" target="_blank" class="btn btn-info">Lihat Bukti</a>
                                <?php else: ?>
                                    <span class="text-muted">Belum Diupload</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <select class="form-select" onchange="updateStatus(<?php echo $transaction['id']; ?>, this.value)">
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php if ($transaction['status'] === $status) echo 'selected'; ?>>
                                            <?php echo ucfirst($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-warning mt-2" onclick="editTransaction(<?php echo $transaction['id']; ?>, <?php echo $transaction['weight'] ?? 0; ?>, <?php echo $transaction['charge'] ?? 0; ?>)">Edit</button>
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

        // Function to update the transaction status
        function updateStatus(transactionId, newStatus) {
            $.post('data_transaction.php', {
                action: 'update_status',
                transaction_id: transactionId,
                status: newStatus
            }, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    document.getElementById("status-" + transactionId).innerText = newStatus;
                    Swal.fire('Sukses!', 'Status berhasil diupdate.', 'success');
                } else {
                    Swal.fire('Error!', response.error, 'error');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                Swal.fire('Error!', 'Terjadi kesalahan saat mengupdate status.', 'error');
            });
        }

        // Function to edit the transaction details
        function editTransaction(transactionId, currentWeight, currentCharge) {
            // Handling missing data for new transactions
            if (currentWeight === undefined) currentWeight = 0;
            if (currentCharge === undefined) currentCharge = 0;

            Swal.fire({
                title: 'Edit Transaksi',
                html: `
                    <label for="weight">Berat (kg):</label>
                    <input type="number" id="weight" class="swal2-input" placeholder="Masukkan Berat (kg)" value="${currentWeight}" required>
                    <label for="distance">Jarak (km):</label>
                    <input type="number" id="distance" class="swal2-input" placeholder="Masukkan Jarak (km)" required>
                    <div id="chargeField" style="display: ${currentCharge > 0 ? 'block' : 'none'};">
                        <label for="charge">Charge (Rp):</label>
                        <input type="number" id="charge" class="swal2-input" placeholder="Masukkan Charge" value="${currentCharge}">
                    </div>
                `,
                confirmButtonText: 'Simpan',
                focusConfirm: false,
                preConfirm: () => {
                    const weight = document.getElementById('weight').value;
                    const distance = document.getElementById('distance').value;
                    const charge = document.getElementById('charge') ? document.getElementById('charge').value : 0;

                    if (!weight || !distance || (distance > 5 && !charge)) {
                        Swal.showValidationMessage('Harap isi semua field yang diperlukan!');
                        return false; // Prevent submission
                    }

                    return { weight, distance, charge };
                },
                didOpen: () => {
                    const distanceInput = document.getElementById('distance');
                    distanceInput.addEventListener('input', () => {
                        const chargeField = document.getElementById('chargeField');
                        chargeField.style.display = distanceInput.value > 5 ? 'block' : 'none';
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const { weight, distance, charge } = result.value;

                    $.post('data_transaction.php', {
                        action: 'edit_transaction',
                        transaction_id: transactionId,
                        weight: weight,
                        distance: distance,
                        charge: charge
                    }, function(response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            Swal.fire('Sukses!', 'Transaksi berhasil diupdate.', 'success').then(() => {
                                location.reload(); // Refresh page to show updated data
                            });
                        } else {
                            Swal.fire('Error!', response.error, 'error');
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        Swal.fire('Error!', 'Terjadi kesalahan saat mengupdate transaksi.', 'error');
                    });
                }
            });
        }
    </script>
</body>

</html>
