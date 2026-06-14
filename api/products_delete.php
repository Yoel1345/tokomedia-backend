<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, GET");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$userIdentifier = $data['user_identifier'] ?? '';

if (!$id || !$userIdentifier) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

try {
    $check = $pdo->prepare("SELECT * FROM products_232025 WHERE id = ?");
    $check->execute([$id]);
    $product = $check->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan']);
        exit;
    }

    if ($product['user_identifier'] !== $userIdentifier) {
        echo json_encode(['status' => 'error', 'message' => 'Anda bukan pemilik produk ini']);
        exit;
    }

    // Delete image file
    $oldFile = __DIR__ . '/../uploads/products/' . basename($product['image_path']);
    if (file_exists($oldFile)) unlink($oldFile);

    $stmt = $pdo->prepare("DELETE FROM products_232025 WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['status' => 'success', 'message' => 'Produk berhasil dihapus']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus produk']);
}
?>
