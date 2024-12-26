<?php
require('fpdf/fpdf.php'); // Ensure FPDF is included
include '../database.php'; // Include your database connection

// Get transaction ID from the URL
$transaction_id = $_GET['transaction_id'];

// Fetch transaction details along with customer information
$query = "SELECT t.faktur AS invoice, t.jumlah AS weight, t.biaya AS charge, t.tanggal_transaksi AS transaction_date, t.status, t.alamat, 
              p.nama AS package_name, c.nama AS customer_name, p.harga AS package_price, t.charge AS charge
       FROM transaksi_laundry t 
       LEFT JOIN paket_laundry p ON t.id_paket = p.id 
       LEFT JOIN data_customer c ON t.id_pelanggan = c.id 
       WHERE t.id = ?
";
$stmt = $pdo->prepare($query);
$stmt->execute([$transaction_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if transaction exists
if (!$transaction) {
    die('Transaksi tidak ditemukan.');
}

// Create PDF Invoice
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// **HEADER PERUSAHAAN**
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Quick Wash Laundry', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, 'Jalan Laundry Bersih No. 123', 0, 1, 'C');
$pdf->Cell(0, 6, 'Telepon: 0812-3456-7890 | Email: info@quickwash.com', 0, 1, 'C');
$pdf->Ln(10);

// **JUDUL INVOICE**
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'INVOICE', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(5);

// **INFORMASI PELANGGAN**
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Nomor Invoice', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(80, 8, ': ' . $transaction['invoice'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Nama Pelanggan', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(80, 8, ': ' . $transaction['customer_name'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Alamat', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(80, 8, ': ' . $transaction['alamat'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Tanggal Transaksi', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(80, 8, ': ' . date('d/m/Y', strtotime($transaction['transaction_date'])), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Jatuh Tempo', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(80, 8, ': ' . date('d/m/Y', strtotime($transaction['transaction_date'] . ' + 5 days')), 0, 1);
$pdf->Ln(10);

/// **TABEL DETAIL INVOICE**
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(90, 10, 'Deskripsi', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Jumlah', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Harga', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Sub Total', 1, 1, 'C', true);

// Isi Tabel
$pdf->SetFont('Arial', '', 12);

// Periksa validitas harga paket dan charge
$packagePrice = is_numeric($transaction['package_price']) ? $transaction['package_price'] : 0;
$charge = is_numeric($transaction['charge']) ? $transaction['charge'] : 0;

// Hitung subtotal dan total
$subtotal = $transaction['weight'] * $packagePrice;
$total = $subtotal + $charge;

// Format berat untuk ditampilkan
$weightFormatted = (intval($transaction['weight']) == $transaction['weight']) ? intval($transaction['weight']) : $transaction['weight'];

// Baris untuk paket laundry
$pdf->Cell(90, 10, $transaction['package_name'], 1);
$pdf->Cell(30, 10, $weightFormatted . ' Kg', 1, 0, 'C');
$pdf->Cell(35, 10, 'Rp ' . number_format($packagePrice, 2, ',', '.'), 1, 0, 'R'); // Harga paket
$pdf->Cell(35, 10, 'Rp ' . number_format($subtotal, 2, ',', '.'), 1, 1, 'R'); // Subtotal

// Baris untuk charge
$pdf->Cell(90, 10, 'Biaya Tambahan (Charge)', 1);
$pdf->Cell(30, 10, '-', 1, 0, 'C'); // Tidak ada jumlah untuk charge
$pdf->Cell(35, 10, 'Rp ' . number_format($charge, 2, ',', '.'), 1, 0, 'R'); // Charge
$pdf->Cell(35, 10, 'Rp ' . number_format($charge, 2, ',', '.'), 1, 1, 'R'); // Charge subtotal

// **TOTAL**
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(155, 10, 'TOTAL', 1, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(35, 10, 'Rp ' . number_format($total, 2, ',', '.'), 1, 1, 'R'); // Total (subtotal + charge)
$pdf->Ln(10);

// **FOOTER**
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Terima kasih telah menggunakan layanan kami.', 0, 1, 'C');
$pdf->Cell(0, 5, 'Untuk informasi lebih lanjut, hubungi layanan pelanggan.', 0, 1, 'C');

// **OUTPUT PDF**
$pdf->Output('I', 'invoice_' . $transaction['invoice'] . '.pdf');
?>
