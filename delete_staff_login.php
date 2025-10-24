<?php
require_once __DIR__ . '/config/session_config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit();
}

require_once __DIR__ . '/config/dbconnect.php';
$id = intval($_POST['id']);
$stmt = $conn->prepare('DELETE FROM staff_logins WHERE id = ?');
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete record']);
}
$stmt->close();
$conn->close(); 