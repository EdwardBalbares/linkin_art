<?php
require_once __DIR__ . '/config/dbconnect.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit;
}

// Get the last checked login time from the request
$lastChecked = isset($_GET['since']) ? $_GET['since'] : '1970-01-01 00:00:00';

$sql = "SELECT u.first_name, u.last_name, u.email, l.login_time
        FROM staff_logins l
        JOIN users u ON l.staff_id = u.user_id
        WHERE l.login_time > ?
        ORDER BY l.login_time DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lastChecked);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'name' => $row['first_name'] . ' ' . $row['last_name'],
        'email' => $row['email'],
        'login_time' => $row['login_time']
    ]);
} else {
    echo json_encode(null);
}
$stmt->close();
$conn->close(); 