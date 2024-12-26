<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Include database connection
require '../database.php'; // Pastikan path benar

// Initialize variables
$cashiers = [];

try {
    // Fetch data from users and data_cashier tables
    $sql = "SELECT id, username, email FROM users WHERE role = 'cashier'";
    $stmt = $pdo->query($sql);
    $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
}

// Handle form actions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'create_cashier') {
        // Add new cashier
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
        $tanggal_masuk = $_POST['tanggal_masuk'];

        try {
            // Insert into users table
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'cashier', NOW())");
            $stmt->execute([$username, $email, $password]);

            // Get the last inserted user ID
            $userId = $pdo->lastInsertId();

            // Insert into data_cashier table using the user ID
            $stmt = $pdo->prepare("INSERT INTO data_cashier (id, username, tanggal_masuk, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $username, $tanggal_masuk]);

            $_SESSION['message'] = "Cashier added successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding cashier: " . $e->getMessage();
        }
        header('Location: data_cashier.php');
        exit;
    } elseif ($action == 'edit_cashier') {
        // Edit existing cashier
        $id = $_POST['id'];
        $email = $_POST['email'];
        $tanggal_masuk = $_POST['tanggal_masuk'];

        try {
            // Update users table
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $id]);

            // Update data_cashier table
            $stmt = $pdo->prepare("UPDATE data_cashier SET tanggal_masuk = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$tanggal_masuk, $id]);

            $_SESSION['message'] = "Cashier updated successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating cashier: " . $e->getMessage();
        }
        header('Location: data_cashier.php');
        exit;
    } elseif ($action == 'delete_cashier') {
        // Delete cashier
        $id = $_POST['id'];

        try {
            // Delete from data_cashier table
            $stmt = $pdo->prepare("DELETE FROM data_cashier WHERE id = ?");
            $stmt->execute([$id]);

            // Delete from users table
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['message'] = "Cashier deleted successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting cashier: " . $e->getMessage();
        }
        header('Location: data_cashier.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Cashier - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="text-black">
<?php include 'sidebar_admin.php'; ?>
<div class="container">
    <div class="row">

        <!-- Flash messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['message'] ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h1 class="mt-3">Data Cashier</h1>
                <button class="btn btn-primary btn-sm" style="height: auto;" data-bs-toggle="modal" data-bs-target="#createCashierModal">
                    Add Cashier
                </button>
            </div>
        </div>
        <div class="table-responsive table-container">
            <table id="cashierTable" class="table text-black table-bordered">
                <thead class="text-black">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="text-black">
                    <?php foreach ($cashiers as $cashier): ?>
                        <tr>
                            <td><?= htmlspecialchars($cashier['username']) ?></td>
                            <td><?= htmlspecialchars($cashier['email']) ?></td>
                            <td class="table-actions">
                                <button class="btn btn-warning btn-sm edit-cashier-btn" data-id="<?= $cashier['id'] ?>" data-email="<?= htmlspecialchars($cashier['email']) ?>">Edit</button>
                                <form method="post" style="display:inline-block">
                                    <input type="hidden" name="action" value="delete_cashier">
                                    <input type="hidden" name="id" value="<?= $cashier['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Cashier Modal -->
        <div class="modal fade" id="createCashierModal" tabindex="-1" aria-labelledby="createCashierLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-black">
                    <form method="post">
                        <div class="modal-header border-0">
                            <h5 class="modal-title" id="createCashierLabel">Add New Cashier</h5>
                            <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="cashierUsername">Username:</label>
                                <input type="text" class="form-control bg-light text-black" id="cashierUsername" name="username" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="cashierEmail">Email:</label>
                                <input type="email" class="form-control bg-light text-black" id="cashierEmail" name="email" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="cashierPassword">Password:</label>
                                <input type="password" class="form-control bg-light text-black" id="cashierPassword" name="password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="cashierDate">Tanggal Masuk:</label>
                                <input type="date" class="form-control bg-light text-black" id="cashierDate" name="tanggal_masuk" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <input type="hidden" name="action" value="create_cashier">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create Cashier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Cashier Modal -->
        <div class="modal fade" id="editCashierModal" tabindex="-1" aria-labelledby="editCashierLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-black">
                    <form method="post">
                        <div class="modal-header border-0">
                            <h5 class="modal-title" id="editCashierLabel">Edit Cashier</h5>
                            <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="editCashierId">
                            <div class="form-group mb-3">
                                <label for="editCashierEmail">Email:</label>
                                <input type="email" class="form-control bg-light text-black" id="editCashierEmail" name="email" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="editCashierDate">Tanggal Masuk:</label>
                                <input type="date" class="form-control bg-light text-black" id="editCashierDate" name="tanggal_masuk" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <input type="hidden" name="action" value="edit_cashier">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-warning">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function () {
        $('#cashierTable').DataTable();

        // Handle edit button click
        $('.edit-cashier-btn').click(function () {
            var id = $(this).data('id');
            var email = $(this).data('email');

            $('#editCashierId').val(id);
            $('#editCashierEmail').val(email);
            $('#editCashierModal').modal('show');
        });
    });
</script>
</body>
</html>
