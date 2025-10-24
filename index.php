<?php
require_once __DIR__ . '/config/session_config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
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
$sql = "SELECT COUNT(*) AS total_categories FROM category";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$totalCategories = $row['total_categories'];

// Query to get total number of products
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
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="assets/images/linkin.jpg">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
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
    --navbar-bg: rgba(26, 26, 26, 0.95);
    --navbar-text: #ffffff;
    --tab-active-text: #fff;
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
    color: var(--navbar-text) !important;
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
    color: var(--tab-active-text) !important;
    background-color: var(--secondary-color);
  }

  .navbar-nav .nav-link i {
    font-size: 1.1rem;
  }

  .admin-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-left: auto;
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
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
    border: none;
    font-weight: 600;
    padding: 0.6rem 1.5rem;
    border-radius: 25px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 4px 15px rgba(46, 204, 113, 0.2);
  }

  .logout-btn .btn i {
    font-size: 1.2rem;
  }

  .logout-btn .btn span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .logout-btn .btn span i {
    font-size: 1rem;
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

    .admin-section {
      flex-direction: column;
      align-items: stretch;
      gap: 0.75rem;
      margin: 1rem 0;
    }

    .user-role {
      justify-content: center;
    }

    .logout-btn .btn {
      justify-content: center;
      padding: 0.75rem 1.5rem;
    }
  }

  .card {
    background: var(--card-bg-color);
    border-radius: 15px;
    border: none;
    box-shadow: 0 5px 15px var(--shadow-color);
    transition: all 0.3s ease;
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
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1em;
    height: 1em;
  }

  .card:hover {
    transform: translateY(-5px);
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
    font-size: 2rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
    text-align: center;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
  }

  .text-muted {
    color: var(--text-secondary) !important;
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

  .quick-actions-card .btn:hover {
    background-color: var(--secondary-color);
    color: white;
    transform: translateY(-2px);
  }

  .quick-actions-card .btn i {
    font-size: 1rem;
    margin: 0;
    width: auto;
  }

  .quick-actions-card h4 {
    margin: 0;
    color: var(--text-primary);
    font-size: 1.2rem;
    font-weight: 600;
  }

  .quick-actions-card i.fa-cubes {
    font-size: 35px;
    margin-bottom: 0.75rem;
  }

  .quick-actions-card:hover i.fa-cubes {
    transform: scale(1.1);
    color: var(--hover-color);
  }

  .alert {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px var(--shadow-color);
  }

  .alert-success {
    background-color: rgba(46, 204, 113, 0.1);
    color: var(--secondary-color);
  }

  .alert-danger {
    background-color: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
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

  /* Ensure consistent heights for cards */
  .col-sm-4 {
    display: flex;
    flex-direction: column;
  }

  .col-sm-4 .card {
    flex: 1;
  }

  /* Ensure text alignment */
  .text-center {
    text-align: center !important;
  }

  /* Button Group Styling */
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


  /* Stock Level Indicators */
  .stock-numbers {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-top: 1rem;
  }

  .stock-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;  /* Reduced from 24px */
    height: 20px; /* Reduced from 24px */
    border-radius: 50%;
    font-size: 0.7rem; /* Reduced from 0.75rem */
    font-weight: 600;
    color: white;
  }

  .stock-number.healthy {
    background-color: #2ecc71;
    box-shadow: 0 2px 4px rgba(46, 204, 113, 0.2);
  }

  .stock-number.warning {
    background-color: #f1c40f;
    box-shadow: 0 2px 4px rgba(241, 196, 15, 0.2);
  }

  .stock-number.critical {
    background-color: #e74c3c;
    box-shadow: 0 2px 4px rgba(231, 76, 60, 0.2);
  }

  /* Add hover effect */
  .stock-number:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
  }


  #main {
    padding-top: 90px; /* Add padding to account for fixed navbar */
  }

  .container.allContent-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
  }

  /* Force inventory content to full width inside dynamic content host */
  .allContent-section,
  .allContent-section .container,
  .allContent-section .container-fluid {
    max-width: 100% !important;
    width: 100% !important;
  }

  .allContent-section .row > [class*="col-"],
  .allContent-section [class^="col-"],
  .allContent-section [class*=" col-"] {
    flex: 0 0 100% !important;
    max-width: 100% !important;
  }

  .welcome-section {
    margin-top: 1rem;
    margin-bottom: 2rem;
  }

  .welcome-text {
    color: var(--primary-color);
    font-size: 2rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.5rem;
    text-align: center;
  }

  .text-muted {
    color: var(--text-secondary) !important;
    text-align: center;
    font-size: 1.1rem;
  }

  /* Adjust card spacing */
  .card {
    margin-bottom: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(46, 204, 113, 0.15);
  }

  .card-body {
    padding: 1.5rem;
  }

  /* Responsive adjustments */
  @media (max-width: 991.98px) {
    #main {
      padding-top: 80px;
    }

    .welcome-section {
      margin-top: 0.5rem;
    }

    .welcome-text {
      font-size: 1.75rem;
    }
  }


  .welcome-text {
    font-size: 2rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
    text-align: center;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
  }


  /* Welcome section styling */
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
    color:rgb(14, 57, 32);
    background-color: rgba(0,0,0,0.15);
  }
  body.dark-theme .welcome-text {
    color: #fff;
    background-color: rgba(255,255,255,0.12);
  }

  .welcome-subtitle {
    font-size: 1.2rem;
    color: var(--text-secondary);
    opacity: 1;
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
    background: radial-gradient(circle, rgba(35, 48, 41, 0.2) 0%, transparent 70%);
    z-index: 0;
  }
  body.dark-theme .welcome-card::before {
    background: radial-gradient(circle, rgba(46, 204, 113, 0.12) 0%, transparent 70%);
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
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light"> 
    <a class="navbar-brand" href="./index.php">
  
      <div class="user-role">
        <i class="fas fa-user-shield"></i>
        Admin
      </div>
    </a>
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item active">
          <a class="nav-link" href="./index.php">
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
  </nav>

  <!-- Main Content -->
  <div id="main" class="w-100">
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
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="action-title">
                            <h3>Analytics & Reports</h3>
                            <p>Track performance and generate reports</p>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <a href="adminView/analytics.php" class="btn btn-action-primary">
                            <i class="fas fa-chart-bar"></i>
                            <span>View Analytics</span>
                        </a>
                        <button class="btn btn-action-secondary" data-toggle="modal" data-target="#staffLoginsModal">
                            <i class="fas fa-user-clock"></i>
                            <span>Staff Activity</span>
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
  <script type="text/javascript" src="./assets/js/ajaxWork.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="./assets/js/script.js"></script>

  <script>
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
    function scrollToTop() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

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

    if (window.history && window.history.pushState) {
        window.history.pushState('forward', null, window.location.href);
        window.onpopstate = function () {
            window.location.href = window.location.href;
        };
    }
  </script>

  <script>
    // Ensure inventory view keeps full width after edits
    (function ensureInventoryFullWidth() {
      function normalizeInventoryLayout() {
        const contentHost = document.querySelector('.allContent-section');
        if (!contentHost) return;

        // Make host fluid and any nested containers fluid
        contentHost.classList.add('container-fluid');
        contentHost.classList.remove('container');
        contentHost.querySelectorAll('.container').forEach(function(containerEl) {
          containerEl.classList.add('container-fluid');
          containerEl.classList.remove('container');
        });

        // Expand direct child columns of any row to full width
        contentHost.querySelectorAll('.row > [class*="col-"]').forEach(function(col) {
          const classesToRemove = [];
          col.classList.forEach(function(cls) {
            const m = cls.match(/^col(?:-(?:xs|sm|md|lg|xl))?-(\d{1,2})$/);
            if (m && m[1] !== '12') classesToRemove.push(cls);
          });
          if (classesToRemove.length) {
            classesToRemove.forEach(c => col.classList.remove(c));
            col.classList.add('col-12', 'col-sm-12', 'col-md-12', 'col-lg-12', 'col-xl-12');
          }
        });

        // If tables/lists are wrapped deeper inside narrow columns, fix those columns too
        const anchors = contentHost.querySelectorAll('table, .table, .table-responsive, .inventory-table, .inventory-list, #inventoryTable');
        anchors.forEach(function(node) {
          let el = node.parentElement;
          while (el && el !== contentHost) {
            if ([...el.classList].some(c => /^col(?:-(?:xs|sm|md|lg|xl))?-\d{1,2}$/.test(c))) {
              const toRemove = [];
              el.classList.forEach(function(cls) {
                const m = cls.match(/^col(?:-(?:xs|sm|md|lg|xl))?-(\d{1,2})$/);
                if (m && m[1] !== '12') toRemove.push(cls);
              });
              if (toRemove.length) {
                toRemove.forEach(c => el.classList.remove(c));
                el.classList.add('col-12', 'col-sm-12', 'col-md-12', 'col-lg-12', 'col-xl-12');
              }
            }
            el = el.parentElement;
          }
        });
      }

      const contentHost = document.querySelector('.allContent-section');
      if (!contentHost) return;

      // Observe dynamic content changes (AJAX-loaded inventory views)
      const observer = new MutationObserver(function() { normalizeInventoryLayout(); });
      observer.observe(contentHost, { childList: true, subtree: true, attributes: true });

      // Nudge layout fixes around typical edit interactions
      document.addEventListener('click', function(event) {
        const actionable = event.target && (event.target.closest('button, a'));
        if (!actionable) return;
        const btn = actionable;
        const label = (btn.textContent || '').toLowerCase();
        const looksLikeEdit = label.includes('edit') || btn.matches('[data-action="edit"], .btn-edit, .fa-edit, .fa-pen, .fa-pencil');
        if (looksLikeEdit && contentHost.contains(btn)) {
          // Run normalization a few times to catch async DOM updates
          setTimeout(normalizeInventoryLayout, 0);
          setTimeout(normalizeInventoryLayout, 200);
          setTimeout(normalizeInventoryLayout, 500);
        }
      }, true);

      // Initial run in case content is already present
      normalizeInventoryLayout();
    })();
  </script>
</body>
</html>

