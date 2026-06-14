<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';
$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? null;
$email = $data['email'] ?? null;
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

$used = $pdo->prepare("SELECT id FROM user_232025 WHERE phone = ? OR email = ?");
$used->execute([$phone, $email]);
if ($used->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor/Email sudah terdaftar']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO temp_registration_232025 (phone, email, verification_code) VALUES (?, ?, ?)");
$stmt->execute([$phone, $email, $code]);
echo json_encode(['status' => 'success', 'message' => 'Kode verifikasi dikirim', 'code' => $code]);
?>
