<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require_once __DIR__ . '/config/session_config.php';
require_once __DIR__ . '/config/site_config.php';
session_start();

// Redirect if already logged in as staff
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff') {
    header("Location: staff_panel.php");
    exit();
}

include_once('config/dbconnect.php');

// One-time password hash updater for staff@gmail.com
if (isset($_GET['fix_staff_password'])) {
    include_once('config/dbconnect.php');
    $newHash = password_hash('staff123', PASSWORD_BCRYPT);
    $sql = "UPDATE users SET password = '" . $newHash . "' WHERE email = 'staff@gmail.com' AND role = 'staff'";
    if (mysqli_query($conn, $sql)) {
        echo "Staff password updated successfully!";
    } else {
        echo "Error updating password: " . mysqli_error($conn);
    }
    exit;
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = ? AND role = 'staff'";

    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Remove debug code
            // var_dump($password, $user['password'], password_verify($password, $user['password']));
            // exit;
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_role'] = 'staff';
                $_SESSION['last_activity'] = time();

                // Log the login event
                $login_time = date('Y-m-d H:i:s');
                $stmt_log = $conn->prepare("INSERT INTO staff_logins (staff_id, login_time) VALUES (?, ?)");
                $stmt_log->bind_param("is", $user['user_id'], $login_time);
                $stmt_log->execute();
                $_SESSION['login_log_id'] = $stmt_log->insert_id; // Store log ID for logout
                $stmt_log->close();

                header("Location: staff_panel.php");
                exit();
            } else {
                $login_error = "Invalid password!";
            }
        } else {
            $login_error = "No user found with this email!";
        }
        mysqli_stmt_close($stmt);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_error = "Please fill in both email and password!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <link rel="icon" type="image/png" href="assets/images/linkin.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
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
        .footer-links a {
            color: #27ae60;
            text-decoration: underline;
            font-weight: 500;
        }
        .footer-links a:hover {
            color: #00e676;
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

<div class="login-container position-relative">
    <a href="login.php" class="icon-back-btn position-absolute" style="left: 1.2rem; top: 1.2rem;" title="Back to Role Selection" aria-label="Back to Role Selection">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="d-flex align-items-center justify-content-center mb-3">
        <h2 class="mb-0">Staff Login</h2>
    </div>

    <?php if (isset($login_error)): ?>
        <div class="alert alert-danger"><?= $login_error ?></div>
    <?php endif; ?>

    <form method="POST" action="staff_login.php">
        <div class="mb-3">
            <label for="username" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-envelope"></i>
                </span>
                <input type="email" class="form-control" id="username" name="username" required autocomplete="username">
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                <span class="input-group-text password-toggle" onclick="togglePassword('password')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-custom">Login</button>
    </form>

    <div class="text-center mt-3">
        <p class="no-account-label">Don't have an account?</p>
        <a href="create_account.php?role=staff" class="create-account-link"><i class="fas fa-user"></i> Create Staff Account</a>
    </div>
    <style>
    .icon-back-btn {
        color: #2ecc71;
        font-size: 1.5rem;
        text-decoration: none;
        transition: color 0.2s;
        z-index: 10;
    }
    .icon-back-btn:hover, .icon-back-btn:focus {
        color: #00e676;
    }
    body.dark-theme .icon-back-btn {
        color: #7fffd4;
    }
    .no-account-label {
        color: #2ecc71;
        margin-bottom: 0.3em;
        font-weight: 500;
    }
    body.dark-theme .no-account-label {
        color: #7fffd4;
    }
    .create-account-link {
        color: #2ecc71;
        text-decoration: underline;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .create-account-link:hover {
        color: #00e676;
        text-shadow: 0 0 10px rgba(0, 255, 0, 0.3);
    }
    body.dark-theme .create-account-link {
        color: #7fffd4;
    }
    </style>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.currentTarget.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
<script>
// Prevent logged-in staff users from accessing staff login page via back button
if (window.history && window.history.pushState) {
    // Add current page to history
    window.history.pushState(null, null, window.location.href);
    
    // Listen for back button clicks
    window.addEventListener('popstate', function() {
        // If trying to go back and user is logged in as staff, redirect to staff panel
        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff'): ?>
            window.location.href = 'staff_panel.php';
        <?php else: ?>
            // If not logged in as staff, stay on staff login page
            window.history.pushState(null, null, window.location.href);
        <?php endif; ?>
    });
}

// Prevent access to staff login page via back button when already logged in as staff
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        // Page was loaded from cache (back button)
        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff'): ?>
            window.location.href = 'staff_panel.php';
        <?php else: ?>
            window.location.reload();
        <?php endif; ?>
    }
});
</script>
</body>
</html>
