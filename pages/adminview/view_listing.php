<?php
session_start();
header('Content-Type: application/json');
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

require_once '../../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad request']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $stmt = $conn->prepare("SELECT * FROM reviewlivestocklisting WHERE listing_id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
    }
    echo json_encode($row);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>


