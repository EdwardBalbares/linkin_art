<?php
// Include session configuration before starting session
require_once __DIR__ . '/config/session_config.php';

session_start();

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff' && isset($_SESSION['login_log_id'])) {
    require_once __DIR__ . '/config/dbconnect.php';
    $logout_time = date('Y-m-d H:i:s');
    $log_id = $_SESSION['login_log_id'];
    $stmt = $conn->prepare("UPDATE staff_logins SET logout_time = ? WHERE id = ?");
    $stmt->bind_param("si", $logout_time, $log_id);
    $stmt->execute();
    $stmt->close();
}

// Get logout reason if provided
$logout_reason = $_GET['reason'] ?? 'manual';

// Store logout reason in session for display on login page
$_SESSION['logout_reason'] = $logout_reason;

// Destroy the session
session_destroy();

// Always redirect to the main login page
header("Location: login.php");
exit();
?>
