<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"> <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="css/navbar.css">
</head>
<div class="d-flex">
    <button class="toggle-sidebar-btn" onclick="toggleSidebar()">☰</button>
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <h3>Laundry</h3>
        </div>
        <ul class="list-unstyled components">
            <li class="active">
                <a href="dashboard_admin.php" class="nav-link">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="data_user.php" class="nav-link">
                    <i class="bi bi-person"></i> Data Customer
                </a>
            </li>
            <li>
                <a href="data_pengiriman.php" class="nav-link">
                    <i class="bi bi-send"></i> Jadwal Antar Jemput
                </a>
            </li>
            <li>
                <a href="data_cashier.php" class="nav-link">
                    <i class="bi bi-person"></i> Data Cashier
                </a>
            </li>
            <li>
                <a href="data_transaction.php" class="nav-link">
                    <i class="bi bi-cart"></i> Transaksi
                </a>
            </li>
            <li>
                <a href="data_financial.php" class="nav-link">
                    <i class="bi bi-currency-dollar"></i>Finance
                </a>
            </li>
            <li>
                <a href="management_package.php" class="nav-link">
                    <i class="bi bi-gear"></i> Data Harga Paket
                </a>
            </li>
            <li>
                <a href="cek_pembayaran.php" class="nav-link">
                    <i class="bi bi-cash"></i> Cek Pembayaran <!-- New entry with icon -->
                </a>
            </li>
            <li>
                <a href="nota.php" class="nav-link">
                    <i class="bi bi-printer"></i> Cetak Nota <!-- New entry with icon -->
                </a>
            </li>
            <li>
                <a href="data_bank.php" class="nav-link">
                    <i class="bi bi-bank"></i> Data Bank
                </a>
            </li>
            <li>
                <a href="pembayaran.php" class="nav-link">
                    <i class="bi bi-cash-coin"></i> pembayaran <!-- New entry with icon -->
                </a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div class="content flex-grow-1">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar">
            <div class="container-fluid">
                <div class="navbar-collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="bi bi-bell"></i> <span class="badge bg-primary">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" id="adminDropdownToggle">
                                <img src="assets/administrator.png" alt="Admin" class="profile-icon" style="max-width: 30px; max-height: 30px;">
                            </a>
                            <ul class="dropdown-menu" id="adminDropdown">

                                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // JavaScript for dropdown functionality
            document.addEventListener('DOMContentLoaded', function() {
                const dropdownToggle = document.getElementById('adminDropdownToggle');
                const dropdownMenu = document.getElementById('adminDropdown');

                // Toggle dropdown on click
                dropdownToggle.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent default anchor click behavior
                    dropdownMenu.classList.toggle('show'); // Toggle 'show' class
                });

                // Close dropdown if clicked outside
                document.addEventListener('click', function(event) {
                    if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                        dropdownMenu.classList.remove('show'); // Hide dropdown if click outside
                    }
                });
            });
        </script>
        <script>
            function toggleSidebar() {
                var sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('active');
            }
            // Ambil elemen sidebar
            const sidebar = document.querySelector('.sidebar');

            // Tambahkan event saat mouse masuk
            sidebar.addEventListener('mouseenter', function() {
                sidebar.style.overflowY = 'auto'; // Aktifkan scroll saat hover
            });

            // Tambahkan event saat mouse keluar
            sidebar.addEventListener('mouseleave', function() {
                sidebar.style.overflowY = 'hidden'; // Nonaktifkan scroll setelah hover
            });


            // Set the active class on the sidebar based on the current URL
            document.addEventListener('DOMContentLoaded', function() {
                const path = window.location.pathname.split('/').pop(); // Get the current page
                const sidebarItems = document.querySelectorAll('.sidebar ul li');

                sidebarItems.forEach(item => {
                    const link = item.querySelector('a');
                    if (link.getAttribute('href') === path) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
            });
        </script>