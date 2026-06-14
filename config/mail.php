<?php

function sendOtpEmail($to, $code) {
    $key  = getenv('SENDGRID_API_KEY') ?: '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';
    $data = json_encode([
        'personalizations' => [['to' => [['email' => $to]]]],
        'from' => ['email' => 'yoelofficial123@gmail.com', 'name' => 'Tokomedia'],
        'subject' => 'Kode Verifikasi Tokomedia',
        'content' => [['type' => 'text/plain', 'value' => "Kode verifikasi Anda: $code\n\nJangan bagikan kode ini kepada siapa pun."]],
    ]);
    $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key, 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $data,
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) {
        error_log("SendGrig cURL error: $error | code: $httpCode | response: $res");
        return false;
    }
    if ($httpCode < 200 || $httpCode >= 300) {
        error_log("SendGrid API error: $httpCode | response: $res");
        return false;
    }
    return true;
}
