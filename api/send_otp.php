<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$phoneOrEmail = $input['phoneOrEmail'] ?? '';
$method       = $input['method'] ?? '';
$code         = $input['code'] ?? '';

if ($method === 'email') {
    require_once __DIR__ . '/../config/mail.php';
    $sent = sendOtpEmail($phoneOrEmail, $code);
    if ($sent) {
        echo json_encode(['status' => 'success', 'message' => "Kode telah dikirim ke email"]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim email']);
    }
} elseif ($method === 'whatsapp') {
    $target = preg_replace('/^0/', '62', $phoneOrEmail);
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'target' => $target,
            'message' => "Kode verifikasi Tokopedia Anda: $code\n\nJangan bagikan kode ini kepada siapa pun.",
        ],
        CURLOPT_HTTPHEADER => ['Authorization: i5Ni5JgzrUNX8Dd7b5yG'],
    ]);
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    if ($error) {
        echo json_encode(['status' => 'error', 'message' => "Gagal kirim WA: $error"]);
    } else {
        echo $response;
    }
} elseif ($method === 'sms') {
    require_once __DIR__ . '/../config/sms.php';
    $result = sendMcSms($phoneOrEmail, $code);
    echo json_encode($result);
} else {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $log = date('Y-m-d H:i:s') . " | $method | $phoneOrEmail | Code: $code\n";
    file_put_contents("$logDir/otp.log", $log, FILE_APPEND);
    echo json_encode(['status' => 'success', 'message' => "Kode telah dikirim via $method (simulasi)"]);
}
