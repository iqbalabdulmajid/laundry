<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
require '../database.php'; // Pastikan path benar

// Handle Tambah Bank
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_bank') {
    $nama_bank = $_POST['nama_bank'];
    $nama_akun = $_POST['nama_akun'];
    $nomor_akun = $_POST['nomor_akun'];
    $gambar_qris = null;

    // Handle QRIS file upload
    if (!empty($_FILES['gambar_qris']['name'])) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["gambar_qris"]["name"]);

        // Check if the uploaded file is an image
        $check = getimagesize($_FILES["gambar_qris"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["gambar_qris"]["tmp_name"], $target_file)) {
                $gambar_qris = basename($_FILES["gambar_qris"]["name"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'File upload failed.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Uploaded file is not a valid image.']);
            exit;
        }
    }

    try {
        $sql = "INSERT INTO detail_bank_laundry (nama_bank, nama_akun, nomor_akun, gambar_qris) 
                VALUES (:nama_bank, :nama_akun, :nomor_akun, :gambar_qris)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nama_bank' => $nama_bank,
            ':nama_akun' => $nama_akun,
            ':nomor_akun' => $nomor_akun,
            ':gambar_qris' => $gambar_qris,
        ]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle Edit Bank
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_bank') {
    $id = $_POST['id'];
    $nama_bank = $_POST['nama_bank'];
    $nama_akun = $_POST['nama_akun'];
    $nomor_akun = $_POST['nomor_akun'];

    try {
        $sql = "UPDATE detail_bank_laundry 
                SET nama_bank = :nama_bank, nama_akun = :nama_akun, nomor_akun = :nomor_akun 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nama_bank' => $nama_bank,
            ':nama_akun' => $nama_akun,
            ':nomor_akun' => $nomor_akun,
            ':id' => $id,
        ]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle Delete Bank
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_bank') {
    $id = $_POST['id'];

    try {
        $sql = "DELETE FROM detail_bank_laundry WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Instead of JSON response, redirect back with a success message
        echo "success";
    } catch (PDOException $e) {
        echo "error";
    }
    exit;
}

// Fetch Data for Display
$banks = [];
try {
    $sql = "SELECT id, nama_bank, nama_akun, nomor_akun, gambar_qris FROM detail_bank_laundry";
    $stmt = $pdo->query($sql);
    $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Bank</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'sidebar_admin.php'; ?>
    <div class="container mt-5">
        <h1 class="mt-3">Data Bank</h1>
        <!-- Tambah Bank Button -->
        <button id="createBankBtn" class="btn btn-primary mb-3">Tambah Bank</button>

        <!-- Tabel Bank -->
        <div class="table-responsive">
            <table id="bankTable" class="table text-black table-bordered">
                <thead>
                    <tr>
                        <th>Nama Bank</th>
                        <th>Nama Akun</th>
                        <th>Nomor Akun</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banks as $bank): ?>
                        <tr>
                            <td><?= htmlspecialchars($bank['nama_bank']) ?></td>
                            <td><?= htmlspecialchars($bank['nama_akun']) ?></td>
                            <td><?= htmlspecialchars($bank['nomor_akun']) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-bank-btn"
                                    data-id="<?= $bank['id'] ?>"
                                    data-nama_bank="<?= htmlspecialchars($bank['nama_bank']) ?>"
                                    data-nama_akun="<?= htmlspecialchars($bank['nama_akun']) ?>"
                                    data-nomor_akun="<?= htmlspecialchars($bank['nomor_akun']) ?>">Edit</button>
                                <form method="post" style="display:inline-block">
                                    <input type="hidden" name="action" value="delete_bank">
                                    <input type="hidden" name="id" value="<?= $bank['id'] ?>">
                                    <button type="button" class="btn btn-danger btn-sm delete-bank-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#bankTable').DataTable();

            // Handle Tambah Bank
            $('#createBankBtn').click(function() {
                Swal.fire({
                    title: 'Tambah Bank Baru',
                    html: ` 
                    <input id="namaBank" class="swal2-input" placeholder="Nama Bank">
                    <input id="namaAkun" class="swal2-input" placeholder="Nama Akun">
                    <input id="nomorAkun" class="swal2-input" placeholder="Nomor Akun">
                    <input id="gambarQris" type="file" class="swal2-file">
                `,
                    confirmButtonText: 'Tambah',
                    showCancelButton: true,
                    preConfirm: () => {
                        const namaBank = document.getElementById('namaBank').value;
                        const namaAkun = document.getElementById('namaAkun').value;
                        const nomorAkun = document.getElementById('nomorAkun').value;
                        const gambarQris = document.getElementById('gambarQris').files[0];

                        if (!namaBank || !namaAkun || !nomorAkun) {
                            Swal.showValidationMessage('Semua field harus diisi!');
                            return false;
                        }

                        const formData = new FormData();
                        formData.append('action', 'create_bank');
                        formData.append('nama_bank', namaBank);
                        formData.append('nama_akun', namaAkun);
                        formData.append('nomor_akun', nomorAkun);
                        if (gambarQris) formData.append('gambar_qris', gambarQris);

                        return fetch('data_bank.php', {
                                method: 'POST',
                                body: formData
                            }).then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    Swal.fire('Success', 'Bank baru berhasil ditambahkan!', 'success');
                                    location.reload();
                                } else {
                                    Swal.fire('Error', result.message, 'error');
                                }
                            });
                    }
                });
            });

            // Handle Edit Bank
            $('.edit-bank-btn').click(function() {
                const id = $(this).data('id');
                const namaBank = $(this).data('nama_bank');
                const namaAkun = $(this).data('nama_akun');
                const nomorAkun = $(this).data('nomor_akun');

                Swal.fire({
                    title: 'Edit Bank',
                    html: ` 
                    <input id="namaBank" class="swal2-input" value="${namaBank}" placeholder="Nama Bank">
                    <input id="namaAkun" class="swal2-input" value="${namaAkun}" placeholder="Nama Akun">
                    <input id="nomorAkun" class="swal2-input" value="${nomorAkun}" placeholder="Nomor Akun">
                `,
                    confirmButtonText: 'Update',
                    showCancelButton: true,
                    preConfirm: () => {
                        const updatedNamaBank = document.getElementById('namaBank').value;
                        const updatedNamaAkun = document.getElementById('namaAkun').value;
                        const updatedNomorAkun = document.getElementById('nomorAkun').value;

                        if (!updatedNamaBank || !updatedNamaAkun || !updatedNomorAkun) {
                            Swal.showValidationMessage('Semua field harus diisi!');
                            return false;
                        }

                        return fetch('data_bank.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `action=edit_bank&id=${id}&nama_bank=${updatedNamaBank}&nama_akun=${updatedNamaAkun}&nomor_akun=${updatedNomorAkun}`
                            }).then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    Swal.fire('Success', 'Bank berhasil diperbarui!', 'success');
                                    location.reload();
                                } else {
                                    Swal.fire('Error', result.message, 'error');
                                }
                            });
                    }
                });
            });

            // Handle Delete Bank
            $('.delete-bank-btn').click(function() {
                const form = $(this).closest('form');
                const id = form.find('[name="id"]').val();

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: "Apakah Anda yakin ingin menghapus bank ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'data_bank.php',
                            method: 'POST',
                            data: {
                                action: 'delete_bank',
                                id: id
                            },
                            success: function(response) {
                                if (response.trim() === "success") {
                                    Swal.fire('Deleted!', 'Bank berhasil dihapus!', 'success');
                                    location.reload(); // Reload page to reflect changes
                                } else {
                                    Swal.fire('Error', 'Gagal menghapus bank. Coba lagi!', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Terjadi kesalahan pada server. Coba lagi!', 'error');
                            }
                        });
                    }
                });
            });

        });
    </script>
</body>

</html>