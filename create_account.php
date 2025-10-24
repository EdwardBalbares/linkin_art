<?php
// Include session configuration before starting session
require_once __DIR__ . '/config/session_config.php';

session_start();
include_once('config/dbconnect.php');  // Include your database connection

// Get role from URL
$role = $_GET['role'] ?? null;

// Check if the role is valid (either admin or staff)
if (!in_array($role, ['admin', 'staff'])) {
    die("Invalid role parameter. Access denied.");
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_no = $_POST['contact_no'] ?? '';
    $user_address = $_POST['user_address'] ?? '';

    // Basic form validation
    if (empty($first_name) || empty($last_name)) {
        $error_message = "First and last name are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $check_query = "SELECT * FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $check_query)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $error_message = "Email is already registered!";
            } else {
                // Insert the new user into the database
                $insert_query = "INSERT INTO users (first_name, last_name, email, password, contact_no, user_address, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
                if ($insert_stmt = mysqli_prepare($conn, $insert_query)) {
                    $user_role = $role;
                    mysqli_stmt_bind_param($insert_stmt, "sssssss", $first_name, $last_name, $email, $hashed_password, $contact_no, $user_address, $user_role);

                    // Execute the query and check if successful
                    if (mysqli_stmt_execute($insert_stmt)) {
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_role'] = $role;
                        $redirect_url = ($role === 'admin') ? 'admin_login.php' : (($role === 'staff') ? 'staff_login.php' : 'login.php');
                        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
                        echo '<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title" id="successModalLabel">Account Created</h5>
                                    </div>
                                    <div class="modal-body text-center">
                                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>
                                        Your account has been created successfully!
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-success" onclick="window.location.href=\'' . $redirect_url . '\'">OK</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';
                        echo '<script>
                            document.addEventListener("DOMContentLoaded", function() {
                                var modal = new bootstrap.Modal(document.getElementById("successModal"));
                                modal.show();
                            });
                        </script>';
                        exit();
                    } else {
                        $error_message = "Error creating account. Please try again.";
                    }

                    mysqli_stmt_close($insert_stmt);
                } else {
                    $error_message = "Database error. Please try again.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #0e0e0e;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .landscape-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 255, 0, 0.1);
            width: 500px;
            max-width: 98vw;
            color: #fff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 0;
        }
        .card-header {
            background-color: #00c853;
            color: white;
            text-align: center;
            font-size: 1.5rem;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            margin-bottom: 1rem;
        }
        .form-label {
            font-weight: 500;
            color: #e0e0e0;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
        }
        .btn-custom {
            background-color: #00c853;
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-custom:hover {
            background-color: #00b44b;
        }
        .alert-danger {
            margin-top: 10px;
            font-weight: 500;
        }
        .footer-links a {
            color: #00c853;
            text-decoration: underline;
            font-weight: 500;
        }
        .animate-card {
            animation: cardFadeInUp 0.9s cubic-bezier(0.23, 1, 0.32, 1) 0.1s both;
        }
        @keyframes cardFadeInUp {
            0% {
                opacity: 0;
                transform: translateY(40px) scale(0.98);
            }
            60% {
                opacity: 1;
                transform: translateY(-8px) scale(1.01);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        @media (max-width: 576px) {
            .landscape-card {
                padding: 0;
                border-radius: 10px;
                width: 98vw;
            }
        }
    </style>
</head>
<body>

<div class="card landscape-card animate-card">
    <div class="row g-0 align-items-center flex-md-row flex-column justify-content-center">
        <!-- Form Section Only -->
        <div class="col-12 p-4">
            <div class="card-header mb-3">
        Create a <?php echo ucfirst($role); ?> Account
    </div>
            <div class="card-body p-0">
                <form method="POST" action="create_account.php?role=<?php echo $role; ?>" class="mt-2">
            <?php if (isset($error_message)) { echo "<div class='alert alert-danger'>$error_message</div>"; } ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
                    <div class="mb-3">
                        <label for="contact_no" class="form-label">Contact Number (optional)</label>
                        <input type="text" class="form-control" id="contact_no" name="contact_no">
                    </div>
                    <div class="mb-3">
                        <label for="user_address" class="form-label">Address (optional)</label>
                        <input type="text" class="form-control" id="user_address" name="user_address">
                    </div>
                    <button type="submit" class="btn btn-custom w-100">Create Account</button>
        </form>
                <div class="footer-links mt-3 text-center">
                    <p>Already have an account? 
                        <?php if ($role === 'admin') { ?>
                            <a href="admin_login.php">Login here</a>
                        <?php } elseif ($role === 'staff') { ?>
                            <a href="staff_login.php">Login here</a>
                        <?php } else { ?>
                            <a href="login.php">Login here</a>
                        <?php } ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
