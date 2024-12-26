<?php
require('fpdf/fpdf.php'); // Pastikan FPDF sudah di-include
include '../database.php'; // Koneksi ke database

// Query untuk mengambil semua data transaksi
$query = "SELECT 
            t.faktur AS invoice, 
            t.jumlah AS weight, 
            t.biaya AS charge, 
            t.tanggal_transaksi AS transaction_date, 
            t.status, 
            t.alamat, 
            p.nama AS package_name, 
            c.nama AS customer_name, 
            p.harga AS package_price
          FROM transaksi_laundry t 
          LEFT JOIN paket_laundry p ON t.id_paket = p.id 
          LEFT JOIN data_customer c ON t.id_pelanggan = c.id";

$stmt = $pdo->prepare($query);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Periksa apakah data transaksi ditemukan
if (!$transactions) {
    die('Tidak ada transaksi ditemukan.');
}

// Buat PDF untuk daftar transaksi
$pdf = new FPDF('L', 'mm', 'A4'); // Landscape, ukuran mm, kertas A4
$pdf->AddPage();

// **HEADER PERUSAHAAN**
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Quick Wash Laundry', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, 'Gg. Abimanyu No.1088, Sorosutan, Kec. Umbulharjo, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55162', 0, 1, 'C');
$pdf->Cell(0, 6, 'Telepon: 082225988878 | Email: info@quickwash.com', 0, 1, 'C');
$pdf->Ln(10);

// **JUDUL**
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'DAFTAR TRANSAKSI', 0, 1, 'C');
$pdf->Ln(5);

// **TABEL HEADER**
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(30, 10, 'Invoice', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Nama Pelanggan', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Alamat', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Paket', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Jumlah', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Harga', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Biaya', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Tanggal', 1, 1, 'C', true);

// **TABEL ISI**
$pdf->SetFont('Arial', '', 10);

foreach ($transactions as $transaction) {
    $subtotal = $transaction['weight'] * $transaction['package_price'];
    $total = $subtotal + $transaction['charge'];

    $pdf->Cell(30, 10, $transaction['invoice'], 1);
    $pdf->Cell(40, 10, $transaction['customer_name'], 1);
    $pdf->Cell(50, 10, $transaction['alamat'], 1);
    $pdf->Cell(25, 10, $transaction['package_name'], 1);
    $pdf->Cell(20, 10, $transaction['weight'] . ' Kg', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Rp ' . number_format($transaction['package_price'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(30, 10, 'Rp ' . number_format($transaction['charge'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(30, 10, date('d/m/Y', strtotime($transaction['transaction_date'])), 1, 1);
}

// **FOOTER**
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Terima kasih telah menggunakan layanan kami.', 0, 1, 'C');
$pdf->Cell(0, 5, 'Untuk informasi lebih lanjut, hubungi layanan pelanggan.', 0, 1, 'C');

// Output PDF ke browser
$pdf->Output('I', 'daftar_transaksi.pdf');
?>