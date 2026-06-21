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
require_once __DIR__ . '/../config/notification_helper.php';

$data = json_decode(file_get_contents('php://input'), true);
$title = $data['title'] ?? 'Produk Baru di Tokomedia';
$body = $data['body'] ?? '';
$sender = $data['sender_identifier'] ?? '';

$result = notifyAllUsers($pdo, $sender, $title, $body);

echo json_encode([
    'status' => 'success',
    'push_sent' => $result['push_sent'],
    'email_sent' => $result['email_sent'],
]);
