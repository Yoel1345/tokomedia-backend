<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$userIdentifier = $data['user_identifier'] ?? '';
$fcmToken = $data['fcm_token'] ?? '';

if (!$userIdentifier || !$fcmToken) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$pdo->exec("CREATE TABLE IF NOT EXISTS fcm_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_identifier VARCHAR(255) NOT NULL,
    token TEXT NOT NULL,
    platform VARCHAR(20) DEFAULT 'android',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_identifier)
)");

$stmt = $pdo->prepare("INSERT INTO fcm_tokens (user_identifier, token, platform) VALUES (?, ?, 'android') ON DUPLICATE KEY UPDATE token = ?, platform = 'android'");
$stmt->execute([$userIdentifier, $fcmToken, $fcmToken]);

echo json_encode(['status' => 'success', 'message' => 'Token registered']);
