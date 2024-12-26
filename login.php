<?php
session_start();
require_once 'database.php';

$errorMessage = ""; // Initialize error message
$successMessage = ""; // Initialize success message
$timeoutDuration = 2 * 60 * 60; // 2 hours in seconds

// Check if the user is already logged in and if the session has timed out
if (isset($_SESSION['user']) && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeoutDuration) {
        // If more than 2 hours have passed, logout and destroy session
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit;
    } else {
        // Update last activity timestamp
        $_SESSION['last_activity'] = time();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // Search for user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // If user found and password matches
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $_SESSION['last_activity'] = time(); // Set initial activity timestamp

            // Redirect based on user role
            switch ($user['role']) {
                case 'admin':
                    header('Location: admin/dashboard_admin.php');
                    break;
                case 'cashier':
                    header('Location: cashier/dashboard_cashier.php');
                    break;
                default:
                    header('Location: users/dashboard_user.php');
                    break;
            }
            exit;
        } else {
            $errorMessage = "Login failed! Incorrect email or password.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        // Handle registration logic
        $username = htmlspecialchars(trim($_POST['username']));
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $retypePassword = $_POST['retype_password'];

        // Validate password and re-type password
        if ($password !== $retypePassword) {
            $errorMessage = "Passwords do not match. Please try again.";
        } else {
            // Hash the password and insert into database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash password

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
                $stmt->execute([$username, $email, $hashedPassword]);
                $successMessage = "Account created successfully! You can now log in.";
            } catch (PDOException $e) {
                $errorMessage = "Error creating account: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Login</title>
    <style>
        body {
            background-color: #f4f6f9; /* Light background */
        }

        .container {
            max-width: 900px; /* Set max width */
            margin: 50px auto; /* Center the container */
            display: flex; /* Use flexbox for layout */
            border-radius: 20px; /* Rounded corners */
            overflow: hidden; /* Hide overflow */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Shadow for depth */
        }

        .left-panel {
            flex: 1; /* Take up remaining space */
            background-color: #cff9ffff; /* Color for left panel */
            color: white; /* White text */
            display: flex; /* Flexbox for center alignment */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
            padding: 30px; /* Padding */
            text-align: center; /* Center text */
        }

        .right-panel {
            flex: 1; /* Take up remaining space */
            padding: 40px; /* Padding */
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: white; /* White background */
        }

        .form-label {
            font-weight: bold; /* Bold labels */
        }

        .login-btn {
            background-color: #6f42c1; /* Button color */
            color: white; /* White text for button */
        }

        .login-btn:hover {
            background-color: #5a31a8; /* Darken on hover */
        }

        .footer-text {
            text-align: center; /* Center footer text */
            margin-top: 20px; /* Top margin */
        }

        .title {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .welcome-text {
            font-size: 18px;
            margin-bottom: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                flex-direction: column; /* Stack panels on small screens */
            }

            .left-panel,
            .right-panel {
                padding: 20px; /* Reduced padding on small screens */
            }

            .title {
                font-size: 24px; /* Smaller title on mobile */
            }

            .welcome-text {
                font-size: 16px; /* Smaller welcome text on mobile */
            }
        }
    </style>
</head>

<body>
    <div class="container">
    <div class="left-panel">
    <div>
        <img src="assets/images/logo.jpeg" alt="logo" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
</div>

        <div class="right-panel">
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            <?php if ($successMessage): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>
            <h1 class="title">Masuk</h1>
                <p class="welcome-text">Selamat Datang, Masuk Menggunakan Akun Kamu.</p>
            <form method="POST" action="login.php">
                <input type="hidden" name="action" value="login">
                <div class="mb-3">
                    <label for="email" class="form-label">E-Mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn login-btn w-100">Login</button>
            </form>
            <p class="text-center mt-3">Belum Memiliki Akun? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Daftar Akun</a></p>

            <!-- Footer -->
            <div class="footer-text">
                <p>E-Laundry</p>
                <p>Build with ❤️</p>
            </div>
        </div>
    </div>

    <!-- Modal for Registering a New Account -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="login.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerModalLabel">Daftar Akun</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="registerUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="registerUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">E-Mail</label>
                            <input type="email" class="form-control" id="registerEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="registerPassword" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerRetypePassword" class="form-label">Re-type Password</label>
                            <input type="password" class="form-control" id="registerRetypePassword" name="retype_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="action" value="register">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Daftar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
