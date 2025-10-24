<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require_once __DIR__ . '/config/session_config.php';
require_once __DIR__ . '/config/site_config.php';
session_start();

// Redirect if already logged in as admin
if (isset(['user_id']) && isset(['user_role']) && ['user_role'] === 'admin') {
    header("Location: index.php");
    exit();
}

include_once('config/dbconnect.php');

// One-time password hash updater for admin@gmail.com
if (isset(['fix_admin_password'])) {
    include_once('config/dbconnect.php');
     = password_hash('admin123', PASSWORD_BCRYPT);
     = "UPDATE users SET password = '" .  . "' WHERE email = 'admin@gmail.com' AND role = 'admin'";
    if (mysqli_query(, )) {
        echo "Admin password updated successfully!";
    } else {
        echo "Error updating password: " . mysqli_error();
    }
    exit;
}

if (["REQUEST_METHOD"] == "POST") {
     = ['username'];
     = ['password'];

     = "SELECT * FROM users WHERE email = ? AND role = 'admin'";

    if ( = mysqli_prepare(, )) {
        mysqli_stmt_bind_param(, "s", );
        mysqli_stmt_execute();
         = mysqli_stmt_get_result();

        if () {
             = mysqli_fetch_assoc();
            if ( && password_verify(, ['password'])) {
                ['user_id'] = ['user_id'];
                ['user_role'] = 'admin';
                ['last_activity'] = time();
                
                // Set welcome notification
                ['welcome_message'] = " Welcome back, " . ['first_name'] . "! Ready to manage your inventory?";
                
                header("Location: index.php");
                exit();
            } else {
                 = "Invalid admin password!";
            }
        } else {
             = "Invalid email or not an admin account!";
        }

        mysqli_stmt_close();
    } else {
        echo "Error: " . mysqli_error();
    }
}
