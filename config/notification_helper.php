<?php

function getFirebaseAccessToken(): ?string {
    $encoded = getenv('FIREBASE_SERVICE_ACCOUNT');
    if (!$encoded) return null;

    $account = json_decode(base64_decode($encoded), true);
    if (!$account || !isset($account['client_email'], $account['private_key'])) return null;

    $now = time();
    $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
    $payload = rtrim(strtr(base64_encode(json_encode([
        'iss' => $account['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now,
    ])), '+/', '-_'), '=');

    openssl_sign("$header.$payload", $signature, $account['private_key'], 'sha256WithRSAEncryption');
    $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => "$header.$payload.$signature",
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) return null;
    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

function sendFcmNotification(string $token, string $title, string $body): bool {
    $accessToken = getFirebaseAccessToken();
    if (!$accessToken) return false;

    $projectId = 'tokomedia-d60b7';
    $payload = json_encode([
        'message' => [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'tokomedia_notifications',
                    'sound' => 'default',
                ],
            ],
        ],
    ]);

    $ch = curl_init("https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json; charset=utf-8',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200;
}

function sendEmailNotification(string $email, string $title, string $body): bool {
    $apiKey = getenv('SENDGRID_API_KEY');
    if (!$apiKey) return false;

    $payload = json_encode([
        'personalizations' => [[
            'to' => [['email' => $email]],
            'subject' => $title,
        ]],
        'from' => ['email' => 'yoelofficial123@gmail.com', 'name' => 'Tokomedia'],
        'content' => [[
            'type' => 'text/plain',
            'value' => "$body\n\n— Tokomedia",
        ]],
    ]);

    $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
}

function notifyAllUsers(string $senderIdentifier, string $title, string $body): array {
    require_once __DIR__ . '/database.php';

    $stmt = $pdo->query("SELECT phone, email FROM user_232025 WHERE is_verified = 1");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pushSent = 0;
    $emailSent = 0;
    $notified = [];

    foreach ($users as $user) {
        $phone = $user['phone'];
        $email = $user['email'];
        $key = $phone ?: $email;

        if (!$key || $key === $senderIdentifier) continue;
        if (in_array($key, $notified)) continue;
        $notified[] = $key;

        // Check FCM token by phone or email
        $st = $pdo->prepare("SELECT token FROM fcm_tokens WHERE user_identifier = ?");
        $st->execute([$key]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (sendFcmNotification($row['token'], $title, $body)) $pushSent++;
        } elseif ($email && str_contains($email, '@')) {
            if (sendEmailNotification($email, $title, $body)) $emailSent++;
        }
    }

    return ['push_sent' => $pushSent, 'email_sent' => $emailSent];
}
