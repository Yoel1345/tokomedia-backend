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

$userIdentifier = $_POST['user_identifier'] ?? '';
$name           = $_POST['name'] ?? '';
$price          = $_POST['price'] ?? 0;
$originalPrice  = $_POST['original_price'] ?? null;
$discountPercent = $_POST['discount_percent'] ?? null;
$rating         = $_POST['rating'] ?? 0;
$soldCount      = $_POST['sold_count'] ?? '0';
$location       = $_POST['location'] ?? '';
$storeName      = $_POST['store_name'] ?? null;
$isMall         = $_POST['is_mall'] ?? 0;
$isBisaCod      = $_POST['is_bisa_cod'] ?? 0;
$bonusText      = $_POST['bonus_text'] ?? null;
$isGratisOngkir = $_POST['is_gratis_ongkir'] ?? 0;
$isPinkPrice    = $_POST['is_pink_price'] ?? 0;
$isLokal        = $_POST['is_lokal'] ?? 0;
$bonusTextWeb   = $_POST['bonus_text_web'] ?? null;

// Convert empty strings to null for nullable columns
foreach (['originalPrice', 'discountPercent', 'storeName', 'bonusText', 'bonusTextWeb'] as $var) {
    if ($$var === '') $$var = null;
}

if (!$userIdentifier || !$name) {
    file_put_contents(__DIR__ . '/../debug_upload.log', "VALIDATION FAILED: user=$userIdentifier name=$name\nPOST=" . json_encode($_POST) . "\nFILES=" . json_encode($_FILES) . "\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => 'Nama produk wajib diisi']);
    exit;
}

// Handle image upload
$imagePath = '';
$uploadDebug = [];
if (isset($_FILES['image'])) {
    $uploadDebug['error_code'] = $_FILES['image']['error'];
    $uploadDebug['name'] = $_FILES['image']['name'];
    $uploadDebug['size'] = $_FILES['image']['size'];
}

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_') . '.' . $ext;
    $dest = __DIR__ . '/../uploads/products/' . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $imagePath = "$protocol://$host/uploads/products/$filename";
    } else {
        $uploadDebug['move_failed'] = true;
    }
}

if (!$imagePath) {
    $uploadDebug['post_keys'] = array_keys($_POST);
    $uploadDebug['files_keys'] = array_keys($_FILES);
    file_put_contents(__DIR__ . '/../debug_upload.log', "NO IMAGE: " . json_encode($uploadDebug) . "\n", FILE_APPEND);
}

try {
    $stmt = $pdo->prepare("INSERT INTO products_232025 
         (user_identifier, name, price, original_price, discount_percent, rating, sold_count, 
          location, store_name, is_mall, is_bisa_cod, bonus_text, bonus_text_web, is_gratis_ongkir, is_pink_price, is_lokal, image_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userIdentifier, $name, $price, $originalPrice, $discountPercent, $rating, $soldCount,
        $location, $storeName, $isMall, $isBisaCod, $bonusText, $bonusTextWeb, $isGratisOngkir, $isPinkPrice, $isLokal, $imagePath
    ]);
    $id = $pdo->lastInsertId();
    echo json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan', 'id' => $id, 'image_path' => $imagePath]);
} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/../debug_upload.log', "DB ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan produk: ' . $e->getMessage()]);
}
?>
