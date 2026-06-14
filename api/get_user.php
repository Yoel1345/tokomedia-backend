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
$phoneOrEmail = $data['phoneOrEmail'] ?? null;

if (!$phoneOrEmail) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM user_232025 WHERE phone = ? OR email = ?");
    $stmt->execute([$phoneOrEmail, $phoneOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'phone' => $user['phone'],
                'email' => $user['email'],
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan']);
}
?>
