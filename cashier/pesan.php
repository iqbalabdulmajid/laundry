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

// Fetch packages from the database
$paket_laundry = [];

// Fetch packages
$query = "SELECT * FROM paket_laundry"; // Adjust the table name if needed
$stmt = $pdo->query($query);
if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $paket_laundry[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
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

    <?php include 'sidebar_cashier.php'; ?>

    <div class="container form-container">
        <h1 class="text-center">Pesan Laundry</h1>

        <!-- Display Success/Error Messages -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']); // Clear the message after displaying
        }

        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']); // Clear the message after displaying
        }
        ?>

        <form action="process_pesan.php" method="POST"> <!-- Processing script -->
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
                    <?php foreach ($paket_laundry as $paket): ?>
                        <option value="<?php echo $paket['id']; ?>" data-price="<?php echo htmlspecialchars($paket['harga']); ?>">
                            <?php echo htmlspecialchars($paket['nama']); ?> - Rp <?php echo htmlspecialchars($paket['harga']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="weight" class="form-label">Berat (kg)</label>
                <input type="number" id="weight" name="berat" class="form-control" step="0.1" min="0.1" required>
            </div>
            <div class="mb-3">
                <label for="distance" class="form-label">Jarak (km)</label>
                <input type="number" id="distance" name="jarak" class="form-control" step="0.1" min="0" required>
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
            <input type="hidden" id="charge" name="charge" value="0">
            <button type="submit" class="btn btn-primary">Pesan</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update the total based on package selection, weight, eco-friendly option, and distance
        document.getElementById('package').addEventListener('change', calculateTotal);
        document.getElementById('weight').addEventListener('input', calculateTotal);
        document.getElementById('eco_friendly').addEventListener('change', calculateTotal);
        document.getElementById('distance').addEventListener('input', calculateTotal);

        function calculateTotal() {
            const packageSelect = document.getElementById('package');
            const weight = parseFloat(document.getElementById('weight').value) || 0;
            const ecoFriendly = document.getElementById('eco_friendly').checked;
            const distance = parseFloat(document.getElementById('distance').value) || 0;
            let total = 0;
            let charge = 0;

            if (packageSelect.value && weight > 0) {
                const selectedOption = packageSelect.options[packageSelect.selectedIndex];
                const packagePrice = parseFloat(selectedOption.getAttribute('data-price'));
                total = packagePrice * weight;

                // Add extra charge for eco-friendly option
                if (ecoFriendly) {
                    total += 5000; // Example charge for eco-friendly option
                }

                // Add distance charge (5000 if more than 5 km)
                if (distance > 5) {
                    charge = 5000; // Add charge if distance is more than 5 km
                }
            }

            total += charge;
            document.getElementById('total').value = 'Rp ' + total.toLocaleString('id-ID');
            // Store the charge value in a hidden input for submission
            document.getElementById('charge').value = charge;
        }
    </script>
</body>

</html>
