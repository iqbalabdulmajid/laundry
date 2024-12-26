<?php
session_start(); // Start the session

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Redirect to login page
    header('Location: login.php'); // Change 'login.php' to your actual login page
    exit; // Stop further execution of the script
}

// Include database connection
require '../database.php'; // Ensure this path is correct

// Fetch customer data from the transaksi_laundry table
$customerData = [];

try {
    // Select relevant fields from the transaksi_laundry table
    $sql = "SELECT p.nama, p.telepon, t.alamat, t.created_at, p.id
            FROM transaksi_laundry t
            JOIN data_customer p ON t.id_pelanggan = p.id";
    $stmt = $pdo->query($sql);

    // Fetch all records as an associative array
    $customerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
}

// Handle AJAX requests (edit and delete user actions)...
// This section remains unchanged
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'edit_user') {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $telepon = $_POST['telepon'];
        $alamat = $_POST['alamat']; // Ambil data alamat dari form
    
        // Validate the data
        if (empty($id) || empty($nama) || empty($telepon) || empty($alamat)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }
    
        try {
            // Update data_customer (nama dan telepon)
            $sqlCustomer = "UPDATE data_customer SET nama = :nama, telepon = :telepon WHERE id = :id";
            $stmtCustomer = $pdo->prepare($sqlCustomer);
            $stmtCustomer->execute(['nama' => $nama, 'telepon' => $telepon, 'id' => $id]);
    
            // Update transaksi_laundry (alamat)
            $sqlTransaction = "UPDATE transaksi_laundry SET alamat = :alamat WHERE id_pelanggan = :id";
            $stmtTransaction = $pdo->prepare($sqlTransaction);
            $stmtTransaction->execute(['alamat' => $alamat, 'id' => $id]);
    
            // Check if any rows were updated
            if ($stmtCustomer->rowCount() > 0 || $stmtTransaction->rowCount() > 0) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'User not found or no changes made.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error updating user: ' . $e->getMessage()]);
        }
        exit;
    }    

    if ($_POST['action'] === 'delete_user') {
        $id = $_POST['id'];

        // Validate ID
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required.']);
            exit;
        }

        try {
            // Prepare delete statement
            $sql = "DELETE FROM data_customer WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);

            // Check if a row was deleted
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting user: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Close the database connection (optional, as PDO will handle it)
$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Customer - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .table-actions {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
    </style>
</head>
<body>
<?php include 'sidebar_admin.php'; ?>
<div class="container">
    <h1 class="mt-3">Data Customer</h1>
    <div class="table-responsive">
        <table id="userTable" class="table text-white table-bordered">
            <thead class="text-black">
            <tr>
                <th>Nama</th>
                <th>No HP</th>
                <th>Alamat</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody class="text-black">
            <?php foreach ($customerData as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['telepon']) ?></td>
                    <td><?= htmlspecialchars($row['alamat']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td class='table-actions'>
                        <button class='btn btn-info btn-sm edit-user-btn' 
                                data-id='<?= $row['id'] ?>' 
                                data-nama='<?= htmlspecialchars($row['nama']) ?>' 
                                data-telepon='<?= htmlspecialchars($row['telepon']) ?>' 
                                data-alamat='<?= htmlspecialchars($row['alamat']) ?>'>Edit User</button>
                        <button class='btn btn-danger btn-sm delete-btn' 
                                data-nama='<?= htmlspecialchars($row['nama']) ?>' 
                                data-id='<?= $row['id'] ?>'>Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Editing User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editUserForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editUserId" name="id">
                    <div class="mb-3">
                        <label for="editnama" class="form-label">nama</label>
                        <input type="text" class="form-control" id="editnama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="editNoHp" class="form-label">No HP</label>
                        <input type="text" class="form-control" id="editNoHp" name="telepon" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAlamat" class="form-label">Alamat</label>
                        <input type="text" class="form-control" id="editAlamat" name="alamat" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#userTable').DataTable();

        // Handle delete button click
        $(document).on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you really want to delete user "${nama}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // AJAX request to delete user
                    $.ajax({
                        url: 'data_user.php',
                        type: 'POST',
                        data: {
                            id: id,
                            action: 'delete_user'
                        },
                        success: function (response) {
                            const result = JSON.parse(response);
                            if (result.status === 'success') {
                                Swal.fire('Deleted!', 'User has been deleted.', 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        },
                        error: function (xhr, status, error) {
                            Swal.fire('Error', 'An error occurred: ' + error, 'error');
                        }
                    });
                }
            });
        });
    });

    // Handle Edit User button click
    $(document).on('click', '.edit-user-btn', function () {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const telepon = $(this).data('telepon');
        const alamat = $(this).data('alamat');

        // Populate modal form with user data
        $('#editUserId').val(id);
        $('#editnama').val(nama);
        $('#editNoHp').val(telepon);
        $('#editAlamat').val(alamat);

        // Show modal
        $('#editUserModal').modal('show');
    });

    // Handle Edit User form submission
    $('#editUserForm').on('submit', function (e) {
        e.preventDefault();

        const formData = {
            action: 'edit_user',
            id: $('#editUserId').val(),
            nama: $('#editnama').val(),
            telepon: $('#editNoHp').val(),
            alamat: $('#editAlamat').val()
        };

        // Send AJAX request to update user
        $.ajax({
            url: 'data_user.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    Swal.fire('Updated!', 'User data has been updated.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'An error occurred: ' + error, 'error');
            }
        });
    });
</script>
</body>
</html>
