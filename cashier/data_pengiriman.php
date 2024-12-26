<?php
// Mulai sesi dan cek peran pengguna
session_start();

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'cashier', 'cabang'])) {
    header('Location: login.php');
    exit;
}

include '../database.php';

// Ambil data nama, telepon, pickup date, dan delivery date
$query = "
    SELECT dc.id, dc.nama AS customer_name, dc.telepon AS phone, 
           tl.pickup_date, tl.delivery_date
    FROM data_customer dc
    LEFT JOIN transaksi_laundry tl ON dc.id = tl.id_pelanggan
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle permintaan POST untuk memperbarui tanggal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = $_POST['customer_id'] ?? null;
    $response = ['success' => false];

    try {
        if (isset($_POST['update_pickup_date'])) {
            // Update pickup date
            $pickupDate = $_POST['pickup_date'];
            $updatePickupDate = $pdo->prepare("
                UPDATE transaksi_laundry
                SET pickup_date = :pickup_date
                WHERE id_pelanggan = :customer_id
            ");
            $updatePickupDate->execute(['pickup_date' => $pickupDate, 'customer_id' => $customerId]);

            $response['success'] = true;
        } elseif (isset($_POST['update_delivery_date'])) {
            // Update delivery date
            $deliveryDate = $_POST['delivery_date'];
            $updateDeliveryDate = $pdo->prepare("
                UPDATE transaksi_laundry
                SET delivery_date = :delivery_date
                WHERE id_pelanggan = :customer_id
            ");
            $updateDeliveryDate->execute(['delivery_date' => $deliveryDate, 'customer_id' => $customerId]);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengiriman - Admin/Cashier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'sidebar_cashier.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center">Data Pengiriman</h1>
        <div class="table-responsive">
            <table id="customerTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Pickup Date</th>
                        <th>Delivery Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td>
                                <input type="date" name="pickup_date" class="form-control"
                                       value="<?php echo htmlspecialchars($customer['pickup_date'] ?? ''); ?>"
                                       id="pickup_date_<?php echo $customer['id']; ?>">
                                <button class="btn btn-primary mt-2 update-date" 
                                        data-id="<?php echo $customer['id']; ?>" 
                                        data-type="pickup">Update</button>
                            </td>
                            <td>
                                <input type="date" name="delivery_date" class="form-control"
                                       value="<?php echo htmlspecialchars($customer['delivery_date'] ?? ''); ?>"
                                       id="delivery_date_<?php echo $customer['id']; ?>">
                                <button class="btn btn-primary mt-2 update-date" 
                                        data-id="<?php echo $customer['id']; ?>" 
                                        data-type="delivery">Update</button>
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
        // Fungsi untuk memperbarui tanggal
        $(document).on('click', '.update-date', function() {
            const customerId = $(this).data('id');
            const dateType = $(this).data('type');
            const dateValue = $('#' + dateType + '_date_' + customerId).val();

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
                    customer_id: customerId,
                    [action]: 1,
                    [`${dateType}_date`]: dateValue
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: `Tanggal ${dateType} berhasil diperbarui.`,
                            confirmButtonText: 'Tutup'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: `Gagal memperbarui tanggal ${dateType}.`,
                            confirmButtonText: 'Tutup'
                        });
                    }
                }
            });
        });
    </script>
</body>

</html>
