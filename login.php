<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Include session configuration before starting session
require_once __DIR__ . '/config/session_config.php';
require_once __DIR__ . '/config/site_config.php';

session_start();

// Check for timeout logout message
$timeout_message = '';
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $timeout_message = 'You have been automatically logged out due to inactivity. Please log in again to continue.';
}

// Show logout message if redirected from logout.php
$logout_message = '';
if (isset($_SESSION['logout_reason'])) {
    if ($_SESSION['logout_reason'] === 'timeout') {
        $logout_message = 'You have been automatically logged out due to inactivity.';
    } else {
        $logout_message = 'You have been logged out successfully.';
    }
    unset($_SESSION['logout_reason']);
}

// Redirect if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: index.php");
        exit();
    } elseif ($_SESSION['user_role'] === 'staff') {
        header("Location: staff_panel.php");
        exit();
    }
}

// Handle role selection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    if ($role == 'admin') {
        header("Location: admin_login.php");
        exit();
    } elseif ($role == 'staff') {
        header("Location: staff_login.php");
        exit();
    } else {
        $error = "Please select a valid role.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Linkin Art Inventory Login</title>
    <link rel="icon" type="image/png" href="/linkin_art_inventory/assets/images/linkin.jpg">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Work Sans', Arial, sans-serif;
            background: linear-gradient(135deg, #23272f 0%, #18191a 100%);
            color: #f5f6fa;
        }
        .login-container {
            background: rgba(35, 39, 47, 0.98);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(46, 204, 113, 0.18), 0 1.5px 8px rgba(0,0,0,0.18);
            padding: 44px 32px 32px 32px;
            max-width: 410px;
            width: 100%;
            margin: 0 auto;
            text-align: center;
            color: #f5f6fa;
            opacity: 0;
            transform: translateY(40px) scale(0.98);
            animation: cardFadeIn 0.8s cubic-bezier(.23,1.02,.67,1) 0.1s forwards;
            border: 1.5px solid #2ecc71;
        }
        .logo {
            width: 90px;
            margin-bottom: 22px;
            opacity: 0;
            transform: scale(0.7) rotate(-20deg);
            animation: logoPopIn 0.7s cubic-bezier(.23,1.02,.67,1) 0.3s forwards;
            box-shadow: 0 0 18px 2px #2ecc7144;
            border-radius: 50%;
        }
        h2 {
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 10px;
            color: #2ecc71;
            letter-spacing: 1px;
            position: relative;
        }
        h2::after {
            content: '';
            display: block;
            margin: 0.5em auto 0 auto;
            width: 60px;
            height: 3px;
            border-radius: 2px;
            background: linear-gradient(90deg, #2ecc71 60%, #27ae60 100%);
        }
        .mb-3 {
            margin-bottom: 1.5rem !important;
        }
        .select-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5em;
            margin-bottom: 1.2em;
            width: 100%;
        }
        .select-wrapper i {
            color: #2ecc71;
            font-size: 1.2em;
        }
        .select-wrapper select.form-control {
            background: #23272f;
            color: #f5f6fa;
            border: 1.5px solid #444950;
            border-radius: 10px;
            padding: 10px 14px; /* slightly reduced vertical padding */
            width: 100%;
            font-size: 1rem;
            margin-bottom: 0;
            transition: border 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
            min-width: 0;
            overflow: visible;
            height: 48px; /* ensure enough height for text */
            line-height: 1.5; /* improve text vertical alignment */
        }
        .select-wrapper select.form-control:focus {
            border-color: #2ecc71;
            outline: none;
            box-shadow: 0 0 0 2px rgba(46,204,113,0.10);
        }
        .select-wrapper select.form-control option {
            color: #23272f;
            background: #f5f6fa;
        }
        .form-control {
            background: #23272f;
            color: #f5f6fa;
            border: 1.5px solid #444950;
            border-radius: 10px;
            padding: 12px 14px;
            width: 100%;
            font-size: 1rem;
            margin-bottom: 0;
            transition: border 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: #2ecc71;
            outline: none;
            box-shadow: 0 0 0 2px rgba(46,204,113,0.10);
        }
        .form-control::placeholder {
            color: #bbb;
            opacity: 1;
        }
        .btn-custom {
            background: linear-gradient(90deg, #2ecc71 60%, #27ae60 100%);
            color: #fff;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 13px;
            width: 100%;
            margin-top: 10px;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(46,204,113,0.10);
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
        }
        .btn-custom:hover {
            background: linear-gradient(90deg, #27ae60 60%, #2ecc71 100%);
            box-shadow: 0 4px 16px rgba(46,204,113,0.15);
            transform: translateY(-2px) scale(1.03);
        }
        .alert {
            margin-top: 10px;
            font-weight: 500;
            border-radius: 8px;
            background: #b52a37;
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.7em;
            justify-content: center;
        }
        .alert-info {
            background: #23272f;
            color: #2ecc71;
            border: 1.5px solid #2ecc71;
        }
        .alert-danger {
            background: #b52a37;
            color: #fff;
        }
        .footer-links {
            margin-top: 18px;
            color: #888;
            font-size: 0.95rem;
        }
        @media (max-width: 600px) {
            .login-container {
                padding: 18px 6vw 18px 6vw;
                max-width: 98vw;
            }
            .logo {
                width: 64px;
            }
        }
        @keyframes cardFadeIn {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        @keyframes logoPopIn {
            to {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }
    </style>
</head>
<body>
    <!-- Removed theme toggle button -->

<div class="login-container">
    <!-- Logo -->
    <img src="/linkin_art_inventory/assets/images/linkin.jpg" alt="Linkin Art Logo" class="logo">

    <h2>Linkin Art Inventory System</h2>

    <!-- Error Display -->
    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Timeout Message -->
    <?php if (!empty($timeout_message)) : ?>
        <div class="alert alert-info"><?= $timeout_message ?></div>
    <?php endif; ?>

    <!-- Show logout message if redirected from logout.php -->
    <?php if (!empty($logout_message)) : ?>
        <div class="alert alert-info theme-alert" role="alert"><?= $logout_message ?></div>
    <?php endif; ?>

    <!-- Role Selection Form -->
    <form method="POST" action="login.php">
        <div class="mb-3 text-start">
        
            <div class="select-wrapper">
                <select class="form-control" id="role" name="role" required>
                    <option value="" disabled selected>Choose your role</option>
                    <option value="admin" class="admin-option">Admin</option>
                    <option value="staff" class="staff-option">Staff</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-custom">
            Proceed to Login
            <i class="fas fa-arrow-right"></i>
        </button>
    </form>

    <!-- Footer Links -->
    <div class="footer-links">
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<script>
// Prevent logged-in users from accessing login page via back button
if (window.history && window.history.pushState) {
    // Add current page to history
    window.history.pushState(null, null, window.location.href);
    
    // Listen for back button clicks
    window.addEventListener('popstate', function() {
        // If trying to go back and user is logged in, redirect to appropriate dashboard
        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])): ?>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                window.location.href = 'index.php';
            <?php elseif ($_SESSION['user_role'] === 'staff'): ?>
                window.location.href = 'staff_panel.php';
            <?php endif; ?>
        <?php else: ?>
            // If not logged in, stay on login page
            window.history.pushState(null, null, window.location.href);
        <?php endif; ?>
    });
}

// Prevent access to login page via back button when already logged in
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        // Page was loaded from cache (back button)
        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])): ?>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                window.location.href = 'index.php';
            <?php elseif ($_SESSION['user_role'] === 'staff'): ?>
                window.location.href = 'staff_panel.php';
            <?php endif; ?>
        <?php else: ?>
            window.location.reload();
        <?php endif; ?>
    }
});

// Removed theme toggle JS logic
</script>
</body>
</html>
