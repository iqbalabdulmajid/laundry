<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"> <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="css/navbar.css"> <!-- Custom CSS File -->
</head>

<body>
    <!-- Sidebar -->
    <div class="d-flex">
        <button class="toggle-sidebar-btn" onclick="toggleSidebar()">☰</button>
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <h3>Laundry</h3>
            </div>
            <ul class="list-unstyled components">
                <li class="active">
                    <a href="dashboard_user.php" class="nav-link">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="pesan.php" class="nav-link">
                        <i class="bi bi-cart"></i> Pesan
                    </a>
                </li>
                <li>
                    <a href="riwayat_pesan.php" class="nav-link">
                        <i class="bi bi-clock-history"></i> Riwayat Pemesanan
                    </a>
                </li>
                <li>
                    <a href="pembayaran.php" class="nav-link">
                        <i class="bi bi-cash-stack"></i> Pembayaran
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div class="content flex-grow-1">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg">
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
                                <li><a class="dropdown-item" href="../index.php">kembali ke landingpage</a></li>
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
</body>

</html>

<script>
    function toggleSidebar() {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('active');
    }

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
