<?php
session_start();

// Periksa jika user tidak login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Pastikan ID user tersedia di sesi
if (!isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
    die("Session tidak mengandung ID pengguna. Pastikan user telah login.");
}

// Include database connection
include '../database.php';

// Periksa apakah form telah di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $tanggal_pesan = trim($_POST['tanggal_pesan']);
    $id_paket = intval($_POST['id_paket']);
    $ramah_lingkungan = isset($_POST['ramah_lingkungan']) ? 1 : 0;
    $total = floatval(str_replace(['Rp', '.', ','], '', $_POST['total'])); // Bersihkan format rupiah
    $no_hp = trim($_POST['no_hp']); // Ambil nomor telepon
    $id_akun = $_SESSION['user']['id']; // ID user yang sedang login

    // Validasi data
    if (empty($nama) || empty($alamat) || empty($tanggal_pesan) || empty($id_paket) || $total <= 0) {
        $_SESSION['error_message'] = 'Form tidak valid. Pastikan semua field terisi dengan benar.';
        header('Location: pesan.php');
        exit;
    }

    // Validasi nomor telepon
    if (empty($no_hp) || !preg_match('/^[0-9]{10,15}$/', $no_hp)) {
        $_SESSION['error_message'] = 'Nomor telepon tidak valid. Harus berisi 10-15 digit angka.';
        header('Location: pesan.php');
        exit;
    }

    // Mulai transaksi database
    try {
        $pdo->beginTransaction();

        // Query untuk menyimpan data pelanggan baru
        $queryCustomer = "INSERT INTO data_customer (nama, telepon, created_at, id_user) 
VALUES (:nama, :telepon, NOW(), :id_user)";
        $stmtCustomer = $pdo->prepare($queryCustomer);
        $stmtCustomer->execute([
            'nama' => $nama,
            'telepon' => $no_hp,
            'id_user' => $id_akun
        ]);

        // Ambil ID pelanggan yang baru saja dibuat
        $id_pelanggan = $pdo->lastInsertId();


        // Validasi paket laundry
        $query = "SELECT * FROM paket_laundry WHERE id = :id_paket";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id_paket' => $id_paket]);
        $paket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$paket) {
            throw new Exception('Paket laundry yang dipilih tidak ditemukan.');
        }

        // Buat faktur dan kode unik
        $faktur = 'INV-' . time();
        $kode_unik = strtoupper(uniqid());
        $kode_pembayaran = 'PAY-' . time();

        // Simpan data transaksi ke tabel transaksi_laundry
        $query = "INSERT INTO transaksi_laundry 
                    (uuid, id_pelanggan, id_akun, faktur, jenis, jumlah, biaya, kode_unik, tanggal_transaksi, status, created_at, kode_pembayaran, id_paket, alamat) 
                  VALUES 
                    (UUID(), :id_pelanggan, :id_akun, :faktur, 'order', :jumlah, :biaya, :kode_unik, NOW(), 'pending', NOW(), :kode_pembayaran, :id_paket, :alamat)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'id_pelanggan' => $id_pelanggan,
            'id_akun' => $id_akun,
            'faktur' => $faktur,
            'jumlah' => 2, // Misalnya, jumlahnya selalu 1
            'biaya' => $total,
            'kode_unik' => $kode_unik,
            'kode_pembayaran' => $kode_pembayaran,
            'id_paket' => $id_paket,
            'alamat' => $alamat
        ]);

        // Commit transaksi
        $pdo->commit();

        // Redirect dengan pesan sukses
        $_SESSION['success_message'] = 'Pesanan berhasil dibuat!';
        header('Location: pesan.php');
        exit;
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $pdo->rollBack();
        $_SESSION['error_message'] = 'Terjadi kesalahan: ' . $e->getMessage();
        header('Location: pesan.php');
        exit;
    }
}
