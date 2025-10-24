<?php
// Include session configuration before starting session
require_once __DIR__ . '/config/session_config.php';

session_start();
include_once "./config/dbconnect.php";
?>

<!-- nav -->
<nav class="navbar navbar-expand-lg navbar-light px-5" style="background-color: #3B3131;">
    <a class="navbar-brand ml-5" href="./index.php">
        <img src="./assets/images/logo.png" width="80" height="80" alt="Linkin Art Collection">
    </a>
    <ul class="navbar-nav mr-auto mt-2 mt-lg-0"></ul>

    <!-- Theme Toggle Button -->
    <button id="theme-toggle-btn" class="btn" style="background: none; border: none; outline: none; font-size: 1.7rem; color: #fff; margin-right: 1.5rem;" title="Toggle dark/light mode">
        <i id="theme-toggle-icon" class="fas fa-moon"></i>
    </button>

    <!-- Session Status Indicator -->
    <div id="session-status" class="d-flex align-items-center" style="margin-right: 1.5rem; color: #fff; font-size: 0.9rem;">
        <i class="fas fa-clock" style="margin-right: 0.5rem;"></i>
        <span id="session-time">Session Active</span>
    </div>

    <div class="user-cart ml-auto">  
        <?php           
        if (isset($_SESSION['user_id'])) {
            // Logged in: Display Logout button
            ?>
            <a href="logout.php" class="logout-btn-link" style="text-decoration:none;">
                <i class="fa fa-sign-out mr-5" style="font-size:30px; color:#fff;" aria-hidden="true"></i> Logout
            </a>
            <?php
        } else {
            // Not logged in: Display Login button
            ?>
            <a href="login.php" style="text-decoration:none;">
                <i class="fa fa-sign-in mr-5" style="font-size:30px; color:#fff;" aria-hidden="true"></i> Login
            </a>
            <?php
        } ?>
    </div>  
</nav>
