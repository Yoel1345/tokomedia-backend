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
$code = $data['code'] ?? null;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM temp_registration_232025 WHERE (phone = ? OR email = ?) AND verification_code = ?");
    $stmt->execute([$phone, $email, $code]);
    $temp = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($temp) {
        $insert = $pdo->prepare("INSERT INTO user_232025 (phone, email, verification_code, is_verified) VALUES (?, ?, ?, 1)");
        $insert->execute([$temp['phone'], $temp['email'], $code]);

        $delete = $pdo->prepare("DELETE FROM temp_registration_232025 WHERE id = ?");
        $delete->execute([$temp['id']]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Verifikasi berhasil']);
    } else {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Kode verifikasi salah']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan']);
}
?>
