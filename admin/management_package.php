<?php
session_start();

// Cek apakah user login dan memiliki peran sebagai admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Include koneksi ke database
include 'database.php';

// Fungsi untuk mengambil daftar paket dari database
function fetchPackagesFromDatabase($pdo) {
    $stmt = $pdo->query("SELECT * FROM paket_laundry");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$packages = fetchPackagesFromDatabase($pdo);

// Menangani penambahan dan pembaruan paket
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $packageId = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $packageName = isset($_POST['nama']) ? $_POST['nama'] : '';
    $packageDescription = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : '';
    $packagePrice = isset($_POST['harga']) ? floatval($_POST['harga']) : 0;

    try {
        if ($action === 'add_package') {
            // Menambahkan paket baru
            $stmt = $pdo->prepare("INSERT INTO paket_laundry (nama, deskripsi, harga) VALUES (?, ?, ?)");
            $stmt->execute([$packageName, $packageDescription, $packagePrice]);

            echo json_encode(['status' => 'success', 'message' => 'Package added successfully.']);
        } elseif ($action === 'edit_package') {
            // Mengedit paket yang sudah ada
            $stmt = $pdo->prepare("UPDATE paket_laundry SET nama = ?, deskripsi = ?, harga = ? WHERE id = ?");
            $stmt->execute([$packageName, $packageDescription, $packagePrice, $packageId]);

            echo json_encode(['status' => 'success', 'message' => 'Package updated successfully.']);
        } elseif ($action === 'delete_package') {
            $stmt = $pdo->prepare("DELETE FROM paket_laundry WHERE id = ?");
            $stmt->execute([$packageId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Package deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Package not found.']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
</head>
<body>
<?php include 'sidebar_admin.php'; ?>
<div class="container mt-4">
    <h1>Data Harga Paket</h1>
    <div class="table-responsive">
        <table id="packageTable" class="table table-striped">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Deskripsi</th>
                    <th>Harga</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                    <tr>
                        <td><?= htmlspecialchars($package['nama']); ?></td>
                        <td><?= htmlspecialchars($package['deskripsi']); ?></td>
                        <td>Rp <?= htmlspecialchars(number_format($package['harga'], 0, ',', '.')); ?></td>
                        <td>
                            <button class="btn btn-info btn-sm edit-package-btn" 
                                    data-id="<?= $package['id']; ?>"
                                    data-name="<?= htmlspecialchars($package['nama']); ?>"
                                    data-description="<?= htmlspecialchars($package['deskripsi']); ?>"
                                    data-price="<?= $package['harga']; ?>">Edit</button>
                            <button class="btn btn-danger btn-sm delete-package-btn" data-id="<?= $package['id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#packageModal">Add Paket</button>
</div>

<!-- Package Modal -->
<div class="modal fade" id="packageModal" tabindex="-1" aria-labelledby="packageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="packageForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="packageModalLabel">Add Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="packageId" name="id">
                    <div class="mb-3">
                        <label for="packageName" class="form-label">Package Name</label>
                        <input type="text" class="form-control" id="packageName" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="packageDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="packageDescription" name="deskripsi" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="packagePrice" class="form-label">Price</label>
                        <input type="number" class="form-control" id="packagePrice" name="harga" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#packageTable').DataTable();

    $('#packageForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const action = $('#packageId').val() ? 'edit_package' : 'add_package';

        $.ajax({
            type: 'POST',
            url: '', 
            data: formData + '&action=' + action, 
            success: function (response) {
                try {
                    const data = JSON.parse(response);
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                } catch (e) {
                    console.error("Error parsing JSON response:", e);
                    Swal.fire('Error', 'An unexpected error occurred. Please try again later.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Request failed. Please try again later.', 'error');
            }
        });
    });

    // Edit Button
    $('.edit-package-btn').click(function () {
        $('#packageModalLabel').text('Edit Package');
        $('#packageId').val($(this).data('id'));
        $('#packageName').val($(this).data('name'));
        $('#packageDescription').val($(this).data('description'));
        $('#packagePrice').val($(this).data('price'));
        $('#packageModal').modal('show');
    });

    // Delete Button
    $('.delete-package-btn').click(function () {
        const packageId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: { action: 'delete_package', id: packageId },
                    success: function (response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', data.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        } catch (e) {
                            console.error("Error parsing JSON response:", e);
                            Swal.fire('Error', 'An unexpected error occurred. Please try again later.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Request failed. Please try again later.', 'error');
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
