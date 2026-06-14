<?php
$mcConfig = [
    'customerId' => 'C-154957401E7D420',
    'email'      => 'yoelofficial123@gmail.com',
    'password'   => 'Yoel1234',
    'senderId'   => 'TOKOPED',
];

function sendMcSms($phone, $code) {
    global $mcConfig;

    $phone = preg_replace('/[^0-9]/', '', $phone);
    $phone = ltrim($phone, '0');

    $encodedPass = base64_encode($mcConfig['password']);

    // Step 1: Generate auth token
    $authUrl = "https://cpaas.messagecentral.com/auth/v1/authentication/token"
        . "?customerId={$mcConfig['customerId']}"
        . "&key=$encodedPass"
        . "&scope=NEW"
        . "&country=62"
        . "&email={$mcConfig['email']}";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $authUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['accept: */*'],
        CURLOPT_TIMEOUT => 15,
    ]);
    $authRes = curl_exec($ch);
    $authErr = curl_error($ch);
    curl_close($ch);

    if ($authErr) {
        return ['status' => 'error', 'message' => "Auth gagal: $authErr"];
    }

    $authData = json_decode($authRes, true);
    $token = $authData['token'] ?? null;

    if (!$token) {
        return ['status' => 'error', 'message' => 'Token tidak didapat: ' . ($authData['message'] ?? json_encode($authData))];
    }

    // Step 2: Send SMS
    $message = urlencode("Kode verifikasi Tokopedia Anda: $code");
    $sendUrl = "https://cpaas.messagecentral.com/verification/v3/send"
        . "?countryCode=62"
        . "&flowType=SMS"
        . "&mobileNumber=$phone"
        . "&senderId={$mcConfig['senderId']}"
        . "&type=SMS"
        . "&message=$message"
        . "&messageType=OTP";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $sendUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['authToken: ' . $token],
        CURLOPT_TIMEOUT => 15,
    ]);
    $sendRes = curl_exec($ch);
    $sendErr = curl_error($ch);
    curl_close($ch);

    if ($sendErr) {
        return ['status' => 'error', 'message' => "Kirim SMS gagal: $sendErr"];
    }

    $sendData = json_decode($sendRes, true);

    if (isset($sendData['responseCode']) && $sendData['responseCode'] == 200) {
        return ['status' => 'success', 'message' => 'Kode telah dikirim via SMS'];
    }

    return ['status' => 'error', 'message' => $sendData['message'] ?? json_encode($sendData)];
}
?>
