<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require_once __DIR__ . '/config/session_config.php';
require_once __DIR__ . '/config/site_config.php';
session_start();

// Ensure the user is logged in and is a staff member
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'staff') {
    // Redirect to login page if not logged in or not a staff member
    header("Location: login.php");
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Display notification if there's a message
if (isset($_SESSION['message'])) {
    // Get message and message type
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];  // success or danger (error)

    // Display the notification
    echo '<div class="alert alert-' . $message_type . ' alert-dismissible fade show" role="alert">';
    echo $message;
    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
    echo '<span aria-hidden="true">&times;</span>';
    echo '</button>';
    echo '</div>';

    // Clear the session message after displaying it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

include_once "config/dbconnect.php";

// Get statistics
$sql = "SELECT COUNT(*) AS total_categories FROM category";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$totalCategories = $row['total_categories'];

$sqlProducts = "SELECT COUNT(*) AS total_products FROM materials_and_supplies";
$resultProducts = $conn->query($sqlProducts);
$rowProducts = $resultProducts->fetch_assoc();
$totalProducts = $rowProducts['total_products'];

// Query to get total number of out of stock items
$sqlOutOfStock = "SELECT COUNT(DISTINCT p.mtrl_and_spls_id) AS out_of_stock_count 
                  FROM materials_and_supplies p 
                  LEFT JOIN variations v ON p.mtrl_and_spls_id = v.mtrl_and_spls_id 
                  WHERE v.quantity_in_stock = 0 OR v.quantity_in_stock IS NULL";
$resultOutOfStock = $conn->query($sqlOutOfStock);
$rowOutOfStock = $resultOutOfStock->fetch_assoc();
$outOfStockCount = $rowOutOfStock['out_of_stock_count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="assets/images/linkin.jpg">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
    <style>
    :root {
        --primary-color: #2ecc71;
        --secondary-color: #27ae60;
        --welcome-gradient: linear-gradient(45deg, #2ecc71, #27ae60);
        --background-color: #f5f6fa;
        --card-bg-color: #ffffff;
        --text-primary: #2d3436;
        --text-secondary: #636e72;
        --hover-color: #27ae60;
        --shadow-color: rgba(46, 204, 113, 0.15);
        --logout-btn-bg-light: linear-gradient(135deg, #2ecc71, #27ae60);
        --logout-btn-bg-dark: linear-gradient(135deg, #23272f, #18191a);
        --logout-btn-text-light: #fff;
        --logout-btn-text-dark: #2ecc71;
    }

    body {
        background-color: var(--background-color);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text-primary);
    }

    .navbar {
        background: #000 !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(46, 204, 113, 0.15);
        padding: 0.75rem 1.5rem;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
    }

    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-right: 2rem;
    }

    .navbar-brand img {
        width: 40px;
        height: 40px;
    }

    .navbar-nav {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-nav .nav-link {
        color: #ffffff !important;
        font-weight: 500;
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-nav .nav-link:hover {
        color: var(--secondary-color) !important;
        background-color: rgba(46, 204, 113, 0.1);
    }

    .navbar-nav .nav-link.active {
        background-color: var(--secondary-color);
        color: var(--primary-color) !important;
    }

    .navbar-nav .nav-link i {
        font-size: 1.1rem;
    }

    .user-role {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.2);
    }

    .user-role i {
        font-size: 1rem;
    }

    .logout-btn .btn {
        background: red !important;
        color: #fff !important;
        border: 2px solid #27ae60 !important;
        font-weight: 600;
        padding: 0.6rem 1.5rem;
        border-radius: 25px;
        display: flex;
        align-items: center;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.2);
        transition: background 0.3s, color 0.3s;
    }

    body.dark-theme .logout-btn .btn {
        background: #18191a !important;
        color: #2ecc71 !important;
        border: 2px solid #2ecc71 !important;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.25);
    }

    .logout-btn .btn:hover {
        filter: brightness(0.95);
        box-shadow: 0 6px 18px rgba(46, 204, 113, 0.25);
    }

    .navbar-toggler {
        border: 2px solid var(--secondary-color);
        padding: 0.5rem;
        border-radius: 8px;
    }

    .navbar-toggler:hover {
        background-color: rgba(46, 204, 113, 0.1);
    }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3e%3cpath stroke='rgba(46, 204, 113, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    /* Main content spacing */
    #main {
        padding-top: 90px;
    }

    .container.allContent-section {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
    }

    .welcome-section {
        margin-top: -0.5rem;
        margin-bottom: 2rem;
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: rgba(26, 26, 26, 0.98);
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1rem;
        }

        .navbar-nav {
            gap: 0.75rem;
        }

        .navbar-nav .nav-link {
            padding: 0.75rem 1rem;
        }

        .user-role {
            justify-content: center;
        }

        .logout-btn .btn {
            justify-content: center;
            padding: 0.75rem 1.5rem;
        }

        #main {
            padding-top: 80px;
        }

        .welcome-section {
            margin-top: -0.25rem;
        }
    }

    .card {
        background: var(--card-bg-color);
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 15px var(--shadow-color);
        margin-bottom: 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .card-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1.5rem;
    }

    .card i {
        font-size: 35px;
        color: var(--secondary-color);
        margin-bottom: 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1em;
        height: 1em;
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(46, 204, 113, 0.2);
    }

    .card h4 {
        color: var(--text-primary);
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0.75rem 0;
        text-align: center;
    }

    .card h5 {
        color: var(--secondary-color);
        font-size: 2rem;
        font-weight: bold;
        margin: 0.5rem 0;
        text-align: center;
    }

    .welcome-text {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
        padding: 0.5em 1.2em;
        border-radius: 1em;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        background-clip: padding-box;
        z-index: 1;
        color: #222;
        background-color: rgba(0,0,0,0.15);
        animation: textGlow 3s ease-in-out infinite, slideInFromLeft 1s ease-out forwards;
    }
    body.dark-theme .welcome-text {
        color: #fff;
        background-color: rgba(255,255,255,0.12);
    }

    .welcome-subtitle {
        font-size: 1.2rem;
        color: var(--text-secondary);
        animation: slideInFromRight 1s ease-out 0.3s forwards, floatAnimation 3s ease-in-out infinite;
        opacity: 0;
    }

    .welcome-card {
        position: relative;
        overflow: hidden;
        border-radius: 15px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(46, 204, 113, 0.1);
        transition: all 0.3s ease;
    }

    .welcome-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(46, 204, 113, 0.2);
        border-color: rgba(46, 204, 113, 0.3);
    }

    .welcome-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(46, 204, 113, 0.22) 0%, transparent 70%);
        animation: rotateBg 10s linear infinite;
        z-index: 0;
    }
    body.dark-theme .welcome-card::before {
        background: radial-gradient(circle, rgba(46, 204, 113, 0.12) 0%, transparent 70%);
    }

    @keyframes rotateBg {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .text-muted {
        text-align: center;
    }

    .dropdown-menu {
        background-color: var(--primary-color);
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 0.5rem;
    }

    .dropdown-item {
        color: white !important;
        padding: 0.7rem 1.5rem;
        transition: all 0.3s ease;
        border-radius: 4px;
    }

    .dropdown-item:hover {
        background-color: var(--secondary-color);
        color: var(--primary-color) !important;
        transform: translateX(5px);
    }

    .btn-outline-primary {
        color: var(--secondary-color);
        border-color: var(--secondary-color);
        background-color: transparent;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
        color: var(--primary-color);
        transform: translateY(-2px);
    }

    .logout-btn .btn {
        background-color: var(--secondary-color);
        color: var(--primary-color);
        border: none;
        font-weight: 600;
        padding: 0.5rem 1.2rem;
        border-radius: 25px;
        transition: all 0.3s ease;
        gap: 0.5rem;
        display: flex;
        align-items: center;
    }

    .logout-btn .btn i {
        font-size: 18px;
        margin-right: 0.6rem;
    }

    .logout-btn .btn:hover {
        background-color: #27ae60;
        transform: translateY(-2px);
    }

    #scrollToTopBtn {
        background-color: var(--secondary-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 4px 12px var(--shadow-color);
        transition: all 0.3s ease;
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 99;
        display: none;
    }

    #scrollToTopBtn:hover {
        background-color: var(--hover-color);
        transform: translateY(-3px);
    }

    .quick-actions-card {
        text-align: center;
    }

    .quick-actions-card .btn-group-vertical {
        width: 100%;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .quick-actions-card .btn {
        padding: 0.75rem;
        border-radius: 8px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        border: 1px solid var(--secondary-color);
        white-space: nowrap;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        width: 100%;
        text-align: center;
    }

    .quick-actions-card .btn i {
        font-size: 1rem;
        margin: 0;
        width: auto;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.65rem;
        margin-top: 0.5rem;
    }

    .container.allContent-section {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin: -10px;
    }

    .row > [class*="col-"] {
        padding: 10px;
    }

    /* Center align all icons */
    .card i, .btn i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Ensure consistent heights for cards */
    .col-sm-4 {
        display: flex;
        flex-direction: column;
    }

    .col-sm-4 .card {
        flex: 1;
    }

    /* Adjust navbar alignment */
    .navbar {
        padding: 0.5rem 1rem;
    }

    .navbar-brand {
        margin-right: 1rem;
    }

    .logout-btn {
        margin-left: auto;
    }

    /* Ensure text alignment */
    .text-center {
        text-align: center !important;
    }

    /* Add these styles to your existing CSS */
    .stock-count {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        min-width: 50px;
        display: inline-block;
        text-align: center;
        font-weight: 500;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .btn-outline-success:hover {
        background-color: #28a745;
        color: white;
    }

    .btn-outline-success:disabled,
    .btn-outline-danger:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }

    .text-white {
        color: #fff !important;
    }

    .table thead th {
        background: var(--primary-color);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 1rem;
        white-space: nowrap;
        border: none;
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #dee2e6;
    }

    .btn-group {
        display: inline-flex;
        gap: 0.5rem;
    }

    /* Animation keyframes */
    @keyframes welcomeLoop {
        0% {
            transform: translateY(0);
            color: #1a1a1a;
        }
        50% {
            transform: translateY(-5px);
            color: #2ecc71;
        }
        100% {
            transform: translateY(0);
            color: #1a1a1a;
        }
    }

    @keyframes subtitleGlow {
        0% {
            text-shadow: 0 0 5px rgba(46, 204, 113, 0.2);
        }
        50% {
            text-shadow: 0 0 15px rgba(46, 204, 113, 0.4);
        }
        100% {
            text-shadow: 0 0 5px rgba(46, 204, 113, 0.2);
        }
    }

    /* Initial entrance animations */
    @keyframes initialEntrance {
        0% {
            opacity: 0;
            transform: scale(0.8) translateY(30px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Animation classes */
    .animate-welcome {
        animation: 
            initialEntrance 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards,
            welcomeLoop 3s ease-in-out infinite;
        opacity: 0;
    }

    .animate-subtitle {
        animation: 
            initialEntrance 0.8s ease-out 0.3s forwards,
            subtitleGlow 3s ease-in-out infinite;
        opacity: 0;
    }

    /* Add hover pause effect */
    .card:hover .animate-welcome,
    .card:hover .animate-subtitle {
        animation-play-state: paused;
    }

    /* Icon Animations */
    @keyframes iconPulse {
        0% {
            transform: scale(1);
            color: var(--primary-color);
        }
        50% {
            transform: scale(1.2);
            color: var(--secondary-color);
        }
        100% {
            transform: scale(1);
            color: var(--primary-color);
        }
    }

    @keyframes iconSpin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes iconBounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    /* Apply animations to different icons */
    .card i {
        animation: iconPulse 2s ease-in-out infinite;
    }

    /* Remove navbar animations */
    .navbar-nav .nav-link i {
        transition: color 0.3s ease;
    }

    .navbar-nav .nav-link:hover i {
        color: var(--secondary-color);
    }

    /* Statistics Cards Icon Animation */
    .card .fa-th-large {
        animation: iconPulse 3s ease-in-out infinite;
    }

    .card .fa-cube {
        animation: iconBounce 3s ease-in-out infinite;
    }

    .card .fa-cubes {
        animation: iconPulse 3s ease-in-out infinite;
    }

    /* Quick Action Buttons Icon Animation */
    .quick-actions-card .btn i {
        transition: all 0.3s ease;
    }

    .quick-actions-card .btn:hover i {
        animation: iconBounce 0.5s ease-in-out infinite;
    }

    /* Add animation for tab icons */
    .nav-tabs .nav-link i {
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover i {
        animation: iconBounce 0.5s ease-in-out infinite;
    }

    .nav-tabs .nav-link.active i {
        animation: iconPulse 2s ease-in-out infinite;
    }

    /* Print Button Icon Animation */
    .btn-print i, .icon-spin, .fa-spin {
        animation: iconSpin 2s linear infinite;
        animation-play-state: paused;
        color: #222 !important;
    }
    body.dark-theme .btn-print i, body.dark-theme .icon-spin, body.dark-theme .fa-spin {
        color: #fff !important;
    }
    .btn-print:hover i, .icon-spin:hover, .fa-spin:hover {
        animation-play-state: running;
    }

    /* Pause animations on hover if needed */
    .card:hover i {
        animation-play-state: paused;
    }

    /* Logo Animation */
    @keyframes rotateLogo {
        0% {
            transform: perspective(1000px) rotateY(0deg);
        }
        100% {
            transform: perspective(1000px) rotateY(360deg);
        }
    }

    .rotating-logo {
        animation: rotateLogo 20s linear infinite;
        transform-style: preserve-3d;
    }

    .rotating-logo:hover {
        animation-play-state: paused;
    }

    /* Animated Icon Base Style */
    .animated-icon {
        display: inline-block;
        transition: all 0.3s ease;
    }

    /* Statistics Cards Styling */
    .statistics-row {
        margin: 0 -10px;
    }

    .statistics-row .col-md-4 {
        padding: 10px;
    }

    .statistics-row .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        min-height: 250px;
    }

    .statistics-row .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(46, 204, 113, 0.2);
    }

    .statistics-row .card-body {
        padding: 2rem;
    }

    .statistics-row .card i {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        color: var(--secondary-color);
    }

    .statistics-row h4 {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-primary);
    }

    .statistics-row h5 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .statistics-row .badge {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
        margin-top: 0.5rem;
    }

    .statistics-row .btn-group-vertical {
        width: 100%;
        gap: 0.75rem;
    }

    .statistics-row .btn-group-vertical .btn {
        padding: 0.75rem;
        margin: 0;
        border-radius: 8px !important;
    }

    .statistics-row .quick-actions-card .btn i {
        font-size: 1rem;
        margin: 0 0.5rem 0 0;
    }

    /* Stock Level Indicators */
    .stock-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.9rem;
        margin: 0.25rem;
    }

    .stock-healthy {
        background-color: rgba(46, 204, 113, 0.15);
        color: #27ae60;
    }

    .stock-warning {
        background-color: rgba(241, 196, 15, 0.15);
        color: #f39c12;
    }

    .stock-critical {
        background-color: rgba(231, 76, 60, 0.15);
        color: #c0392b;
    }

    .stock-indicator::before {
        content: '';
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .stock-healthy::before {
        background-color: #2ecc71;
    }

    .stock-warning::before {
        background-color: #f1c40f;
    }

    .stock-critical::before {
        background-color: #e74c3c;
    }

    .stock-status-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .stock-numbers {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .stock-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        font-size: 0.75rem;
        font-weight: 500;
        color: white;
    }

    .stock-number.healthy {
        background-color: #2ecc71;
    }

    .stock-number.warning {
        background-color: #f1c40f;
    }

    .stock-number.critical {
        background-color: #e74c3c;
    }

    .btn-group-vertical .btn {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        text-align: left;
        padding: 0.6rem 0.8rem;
        gap: 0.5rem;
        width: 100%;
        border-radius: 8px !important;
        font-size: 0.9rem;
        line-height: 1.2;
    }

    .btn-group-vertical .btn i {
        font-size: 0.9rem;
        width: 1rem;
        height: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        flex-shrink: 0;
    }

    .btn-group-vertical .btn span {
        flex: 1;
        white-space: normal;
        text-align: left;
    }

    .btn-group-vertical {
        width: 100%;
        gap: 0.5rem;
    }

    /* Add these animation keyframes to the style section */
    @keyframes welcomePulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .animate-welcome {
        animation: welcomePulse 2s ease-in-out infinite;
    }

    /* Add these animation keyframes */
    @keyframes textGlow {
        0%, 100% {
            text-shadow: 0 0 5px rgba(46, 204, 113, 0.2);
        }
        50% {
            text-shadow: 0 0 20px rgba(46, 204, 113, 0.6);
        }
    }

    @keyframes slideInFromLeft {
        0% {
            transform: translateX(-100%);
            opacity: 0;
        }
        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideInFromRight {
        0% {
            transform: translateX(100%);
            opacity: 0;
        }
        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes floatAnimation {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    body.dark-theme {
        --primary-color: #f5f6fa;
        --secondary-color: #2ecc71;
        --welcome-gradient: linear-gradient(45deg, #f5f6fa, #2ecc71);
        --background-color: #18191a;
        --card-bg-color: #23272f;
        --text-primary: #f5f6fa;
        --text-secondary: #b0b3b8;
        --hover-color: #27ae60;
        --shadow-color: rgba(46, 204, 113, 0.10);
    }

    body.dark-theme {
        background-color: var(--background-color);
        color: var(--text-primary);
    }

    body.dark-theme .navbar {
        background: rgba(24, 25, 26, 0.98) !important;
    }

    body.dark-theme .navbar-nav .nav-link {
        color: #f5f6fa !important;
    }

    body.dark-theme .navbar-nav .nav-link.active {
        background-color: var(--secondary-color);
        color: var(--primary-color) !important;
    }

    body.dark-theme .card {
        background: var(--card-bg-color);
        color: var(--text-primary);
    }

    body.dark-theme .dropdown-menu {
        background-color: #23272f;
    }

    body.dark-theme .btn-outline-primary {
        color: var(--secondary-color);
        border-color: var(--secondary-color);
        background-color: transparent;
    }

    body.dark-theme .btn-outline-primary:hover {
        background-color: var(--secondary-color);
        color: var(--primary-color);
    }

    body.dark-theme .logout-btn .btn {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: #18191a;
    }

    body.dark-theme .table thead th {
        background: #23272f;
        color: #fff;
    }

    body.dark-theme .table tbody td {
        color: #f5f6fa;
    }

    body.dark-theme .modal-content {
        background: #23272f;
        color: #f5f6fa;
    }

    body.dark-theme .form-control {
        background: #23272f;
        color: #f5f6fa;
        border-color: #444950;
    }

    body.dark-theme .form-control:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.2rem rgba(46,204,113,0.15);
    }

    body.dark-theme .input-group-text {
        background: #23272f;
        color: #b0b3b8;
    }

    body.dark-theme .badge {
        background: #23272f;
        color: #f5f6fa;
    }

  /* Modern Dashboard Styling */

    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #FF8C00, #FFA500);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #FF8C00, #FFA500);
    }

    .stat-icon.categories {
        background: linear-gradient(135deg, #f093fb, #f5576c);
    }

    .stat-icon.stock-healthy {
        background: linear-gradient(135deg, #4facfe, #00f2fe);
    }

    .stat-icon.stock-warning {
        background: linear-gradient(135deg, #fa709a, #fee140);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3436;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #636e72;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .stat-trend.positive {
        color: #00b894;
    }

    .stat-trend.negative {
        color: #e17055;
    }

    .stat-trend i {
        margin-right: 0.25rem;
    }

    /* Action Cards */
    .action-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
        height: 100%;
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.15);
    }

    .action-card.primary {
        border-left: 4px solid #FF8C00;
    }

  .action-card.secondary {
    border-left: 4px solid #f093fb;
  }

  .action-card.large {
    min-height: 200px;
  }

  .action-card.large .action-header {
    margin-bottom: 2rem;
  }

  .action-card.large .action-title h3 {
    font-size: 1.5rem;
  }

  .action-card.large .action-title p {
    font-size: 1rem;
  }

  .btn-action-primary.large {
    padding: 15px 30px;
    font-size: 1.1rem;
    min-width: 180px;
  }

    .action-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .action-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: white;
        margin-right: 1rem;
        background: linear-gradient(135deg, #FF8C00, #FFA500);
    }

    .action-card.secondary .action-icon {
        background: linear-gradient(135deg, #f093fb, #f5576c);
    }

    .action-title h3 {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2d3436;
        margin-bottom: 0.25rem;
    }

    .action-title p {
        color: #636e72;
        font-size: 0.9rem;
        margin: 0;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-action-primary {
        background: linear-gradient(135deg, #FF8C00, #FFA500);
        border: none;
        color: white;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-action-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 140, 0, 0.4);
    }

    .btn-action-secondary {
        background: rgba(255, 140, 0, 0.1);
        border: 2px solid rgba(255, 140, 0, 0.2);
        color: #FF8C00;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-action-secondary:hover {
        background: rgba(255, 140, 0, 0.2);
        border-color: rgba(255, 140, 0, 0.4);
        transform: translateY(-2px);
    }

    /* Dark theme adjustments */
    body.dark-theme .stat-card,
    body.dark-theme .action-card {
        background: #2d2d2d;
        border-color: rgba(255,255,255,0.1);
    }

    body.dark-theme .stat-number,
    body.dark-theme .action-title h3 {
        color: #ffffff;
    }

    body.dark-theme .stat-label,
    body.dark-theme .action-title p {
        color: #cccccc;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .welcome-content {
            flex-direction: column;
            text-align: center;
        }

        .welcome-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .welcome-actions {
            margin-left: 0;
            margin-top: 1rem;
        }

        .welcome-title {
            font-size: 2rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-action-primary,
        .btn-action-secondary {
            justify-content: center;
        }
    }
    </style>
</head>
<body>
    <!-- Updated Navbar HTML -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="./staff_panel.php">
            <div class="user-role">
                <i class="fas fa-user-tie"></i>
                Staff
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="./staff_panel.php">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center ml-auto">
                <div class="theme-toggle mr-3">
                    <button id="themeToggleBtn" class="btn btn-outline-primary" title="Toggle dark/light mode">
                        <i id="themeIcon" class="fas fa-moon"></i>
                    </button>
                </div>
                <div class="logout-btn">
                    <a href="logout.php" class="btn logout-btn-link">
                        <span>
                            Logout
                            <i class="fas fa-power-off"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="main" class="w-100">
        <!-- Content Container -->
        <div class="container-fluid px-4 py-4">
        <!-- Quick Stats Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card" onclick="showAvailableProducts()">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?= $totalProducts ?></h3>
                        <p class="stat-label">Total Products</p>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                            <span>Active</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon categories">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?= $totalCategories ?></h3>
                        <p class="stat-label">Categories</p>
                        <div class="stat-trend">
                            <i class="fas fa-layer-group"></i>
                            <span>Organized</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon stock-healthy">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">
                            <?php
                                $sqlStockLevels = "SELECT 
                                    SUM(CASE WHEN quantity_in_stock > 10 THEN 1 ELSE 0 END) as healthy_stock
                                    FROM variations";
                                $resultStockLevels = $conn->query($sqlStockLevels);
                                $stockLevels = $resultStockLevels->fetch_assoc();
                                echo $stockLevels['healthy_stock'];
                            ?>
                        </h3>
                        <p class="stat-label">In Stock</p>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>Healthy</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon stock-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">
                            <?php
                                $sqlStockLevels = "SELECT 
                                    SUM(CASE WHEN quantity_in_stock = 0 THEN 1 ELSE 0 END) as out_of_stock
                                    FROM variations";
                                $resultStockLevels = $conn->query($sqlStockLevels);
                                $stockLevels = $resultStockLevels->fetch_assoc();
                                echo $stockLevels['out_of_stock'];
                            ?>
                        </h3>
                        <p class="stat-label">Out of Stock</p>
                        <div class="stat-trend negative">
                            <i class="fas fa-exclamation"></i>
                            <span>Needs Attention</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Primary Action Cards Row -->
        <div class="row">
            <div class="col-lg-8 col-md-12 mb-4">
                <div class="action-card primary large">
                    <div class="action-header">
                        <div class="action-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="action-title">
                            <h3>Inventory Management</h3>
                            <p>Manage your products, stock levels, and categories</p>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-action-primary large" onclick="showProductItems()">
                            <i class="fas fa-box"></i>
                            <span>Manage Inventory</span>
                        </button>
                        <button class="btn btn-action-secondary" onclick="showCategory()">
                            <i class="fas fa-th-large"></i>
                            <span>Manage Categories</span>
                        </button>
                        <button class="btn btn-action-secondary" onclick="showAvailableProducts()">
                            <i class="fas fa-eye"></i>
                            <span>View Products</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 mb-4">
                <div class="action-card secondary">
                    <div class="action-header">
                        <div class="action-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="action-title">
                            <h3>Quick Actions</h3>
                            <p>Access frequently used features</p>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-action-primary" onclick="showStockOut()">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Stock Out Log</span>
                        </button>
                        <button class="btn btn-action-secondary" onclick="showAvailableProducts()">
                            <i class="fas fa-eye"></i>
                            <span>View Products</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Content Loading Section -->
    <div class="allContent-section" style="display: none;"></div>

    <!-- Scroll to Top Button -->
    <button id="scrollToTopBtn" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="./assets/js/ajaxWork.js"></script>    
    <script type="text/javascript" src="./assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Initialize everything after DOM is fully loaded
        $(document).ready(function() {
            // Scroll to Top Button Visibility
        window.onscroll = function() {
            var scrollButton = document.getElementById("scrollToTopBtn");
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                scrollButton.style.display = "block";
            } else {
                scrollButton.style.display = "none";
            }
        };

            // Function to scroll to top
            window.scrollToTop = function() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
            };
        });
    </script>

    <script>
    // Theme toggle logic
    function setTheme(theme) {
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
            document.getElementById('themeIcon').classList.remove('fa-moon');
            document.getElementById('themeIcon').classList.add('fa-sun');
        } else {
            document.body.classList.remove('dark-theme');
            document.getElementById('themeIcon').classList.remove('fa-sun');
            document.getElementById('themeIcon').classList.add('fa-moon');
        }
        localStorage.setItem('theme', theme);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Set theme on load
        const savedTheme = localStorage.getItem('theme') || 'light';
        setTheme(savedTheme);
        document.getElementById('themeToggleBtn').addEventListener('click', function() {
            const isDark = document.body.classList.contains('dark-theme');
            setTheme(isDark ? 'light' : 'dark');
        });
    });
    </script>

    <script>
    // Notification system
    function showNotification(message, type = 'info') {
        // Remove any existing notifications
        const existingNotifications = document.querySelectorAll('.custom-notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `custom-notification alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: none;
            border-radius: 8px;
            animation: slideInRight 0.3s ease-out;
        `;
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'warning' ? 'fa-exclamation-triangle' : type === 'danger' ? 'fa-times-circle' : 'fa-info-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Prevent back navigation to login pages when already logged in
    if (window.history && window.history.pushState) {
        // Add current page to history
        window.history.pushState(null, null, window.location.href);
        
        // Listen for back button clicks
        window.addEventListener('popstate', function() {
            // If trying to go back, push current page again to prevent navigation
            window.history.pushState(null, null, window.location.href);
            
            // Show notification instead of alert
            showNotification('You are already logged in to the staff panel.', 'info');
        });
    }

    // Prevent access to login pages via back button
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Page was loaded from cache (back button)
            window.location.reload();
        }
    });

    // Add CSS for notification animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .custom-notification {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border-left: 4px solid #27ae60;
        }
        
        .custom-notification.alert-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            border-left-color: #e67e22;
        }
        
        .custom-notification.alert-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border-left-color: #c0392b;
        }
        
        .custom-notification.alert-info {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-left-color: #2980b9;
        }
        
        .custom-notification .close {
            color: white;
            opacity: 0.8;
        }
        
        .custom-notification .close:hover {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>
