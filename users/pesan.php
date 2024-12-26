<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

include '../database.php';

// Fetch packages
$paket_laundry = [];
$query = "SELECT * FROM paket_laundry";
$stmt = $pdo->query($query);
if ($stmt) {
    $paket_laundry = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Laundry</title>
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

        .form-container {
            margin: 20px auto;
            max-width: 600px;
        }
    </style>
</head>

<body>

    <?php include 'sidebar_user.php'; ?>

    <div class="container form-container">
        <h1 class="text-center">Pesan Laundry</h1>

        <!-- Success/Error Messages -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <form action="process_pesan.php" method="POST" id="pesanLaundryForm">
            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" id="name" name="nama" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Nomor Telepon</label>
                <input type="tel" id="phone" name="no_hp" class="form-control" required pattern="[0-9]{10,15}" title="Nomor telepon harus berisi 10-15 digit angka.">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Alamat Penjemputan</label>
                <textarea id="address" name="alamat" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="order_date" class="form-label">Tanggal Pesan</label>
                <input type="date" id="order_date" name="tanggal_pesan" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="package" class="form-label">Jenis Laundry</label>
                <select id="package" name="id_paket" class="form-select" required>
                    <option value="">Pilih Jenis Laundry</option>
                    <?php foreach ($paket_laundry as $paket) : ?>
                        <option value="<?php echo $paket['id']; ?>" data-price="<?php echo htmlspecialchars($paket['harga']); ?>">
                            <?php echo htmlspecialchars($paket['nama']); ?> - Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="eco_friendly" class="form-label">Pilih Pencucian Ramah Lingkungan</label>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="eco_friendly" name="ramah_lingkungan" value="1">
                    <label class="form-check-label" for="eco_friendly">Ya</label>
                </div>
            </div>
            <div class="mb-3">
                <label for="total" class="form-label">Total yang Harus Dibayar</label>
                <input type="text" id="total" name="total" class="form-control" readonly>
            </div>

            <!-- Hidden field for quantity -->
            <input type="hidden" id="quantity" name="jumlah" value="2">

            <input type="hidden" id="charge" name="charge" value="0">
            <button type="submit" class="btn btn-primary">Pesan</button>
        </form>
    </div>

    <script>
        const packageSelect = document.getElementById('package');
        const ecoFriendlyCheckbox = document.getElementById('eco_friendly');
        const totalField = document.getElementById('total');
        const quantityInput = document.getElementById('quantity');

        function calculateTotal() {
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            let total = selectedOption ? parseFloat(selectedOption.getAttribute('data-price')) : 0;

            if (ecoFriendlyCheckbox.checked) {
                total += 5000; // Example eco-friendly charge
            }

            total *= quantityInput.value; // Multiply by quantity (2 by default)
            totalField.value = 'Rp ' + total.toLocaleString('id-ID');
        }

        packageSelect.addEventListener('change', calculateTotal);
        ecoFriendlyCheckbox.addEventListener('change', calculateTotal);

        document.getElementById('pesanLaundryForm').addEventListener('submit', function(e) {
            if (!totalField.value || totalField.value === 'Rp 0') {
                alert('Total harus dihitung sebelum mengirim form!');
                e.preventDefault();
            }
        });
    </script>
</body>

</html>
