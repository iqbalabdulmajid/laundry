<?php
// Start the session and check user role
session_start();

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'cashier', 'cabang'])) {
    header('Location: login.php');
    exit;
}

include '../database.php';

// Fetch all transactions with pickup and delivery dates
$query = "
    SELECT t.id, t.faktur AS invoice, t.jumlah AS amount, t.biaya AS charge, 
           t.tanggal_transaksi AS transaction_date, t.status, p.nama AS package_name, 
           u.username AS customer_name, t.kode_pembayaran AS payment_code, 
           t.bukti_pembayaran AS payment_proof, t.pickup_date, t.delivery_date
    FROM transaksi_laundry t
    LEFT JOIN paket_laundry p ON t.id_paket = p.id
    LEFT JOIN users u ON t.id_pelanggan = u.id
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transactionId = $_POST['transaction_id'] ?? null;
    $response = ['success' => false];

    try {
        if (isset($_POST['update_status'])) {
            // Update transaction status
            $newStatus = $_POST['status'];
            $updateTransaction = $pdo->prepare("UPDATE transaksi_laundry SET status = :status WHERE id = :id");
            $updateTransaction->execute(['status' => $newStatus, 'id' => $transactionId]);

            $response['success'] = true;
        } elseif (isset($_POST['update_pickup_date'])) {
            // Update pickup date
            $pickupDate = $_POST['pickup_date'];
            $updatePickupDate = $pdo->prepare("
                UPDATE transaksi_laundry
                SET pickup_date = :pickup_date
                WHERE id = :id
            ");
            $updatePickupDate->execute(['pickup_date' => $pickupDate, 'id' => $transactionId]);

            $response['success'] = true;
        } elseif (isset($_POST['update_delivery_date'])) {
            // Update delivery date
            $deliveryDate = $_POST['delivery_date'];
            $updateDeliveryDate = $pdo->prepare("
                UPDATE transaksi_laundry
                SET delivery_date = :delivery_date
                WHERE id = :id
            ");
            $updateDeliveryDate->execute(['delivery_date' => $deliveryDate, 'id' => $transactionId]);

            $response['success'] = true;
        }
    } catch (PDOException $e) {
        $response['error'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Data Transaksi - Admin/Cashier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'sidebar_admin.php'; ?>

    <div class="container mt-3">
        <h1 class="mt-3">Data Transaksi</h1>

        <div class="table-responsive">
            <table id="transactionTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Invoice</th>
                        <th>Jenis Laundry</th>
                        <th>Jumlah</th>
                        <th>Charge</th>
                        <th>Total</th>
                        <th>Tanggal Transaksi</th>
                        <th>Status</th>
                        <th>Pickup Date</th>
                        <th>Delivery Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['invoice']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['package_name'] ?? 'N/A'); ?></td>
                            <td>Rp <?php echo number_format($transaction['amount'], 2); ?></td>
                            <td>Rp <?php echo number_format($transaction['charge'], 2); ?></td>
                            <td>Rp <?php echo number_format($transaction['amount'] + $transaction['charge'], 2); ?></td>
                            <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>

                            <!-- Status Column -->
                            <td id="status-<?php echo $transaction['id']; ?>"><?php echo htmlspecialchars($transaction['status'] ?? ''); ?></td>

                            <!-- Pickup Date Column -->
                            <td>
                                <input type="date" name="pickup_date" class="form-control" value="<?php echo htmlspecialchars($transaction['pickup_date'] ?? ''); ?>" id="pickup_date_<?php echo $transaction['id']; ?>">
                                <button class="btn btn-primary mt-2 update-date" data-id="<?php echo $transaction['id']; ?>" data-type="pickup">Update</button>
                            </td>

                            <!-- Delivery Date Column -->
                            <td>
                                <input type="date" name="delivery_date" class="form-control" value="<?php echo htmlspecialchars($transaction['delivery_date'] ?? ''); ?>" id="delivery_date_<?php echo $transaction['id']; ?>">
                                <button class="btn btn-primary mt-2 update-date" data-id="<?php echo $transaction['id']; ?>" data-type="delivery">Update</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <script>
        // Function to handle the date update
        $(document).on('click', '.update-date', function() {
            const transactionId = $(this).data('id');
            const dateType = $(this).data('type');
            const dateValue = $('#' + dateType + '_date_' + transactionId).val();

            let action = '';
            if (dateType === 'pickup') {
                action = 'update_pickup_date';
            } else if (dateType === 'delivery') {
                action = 'update_delivery_date';
            }

            $.ajax({
                url: 'data_pengiriman.php',
                method: 'POST',
                data: {
                    transaction_id: transactionId,
                    [action]: 1,
                    [`${dateType}_date`]: dateValue
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: `${dateType.charAt(0).toUpperCase() + dateType.slice(1)} date updated successfully.`,
                            confirmButtonText: 'Close'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: `Failed to update the ${dateType} date.`,
                            confirmButtonText: 'Close'
                        });
                    }
                }
            });
        });
    </script>
</body>

</html>