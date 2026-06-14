<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function _mailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'yoelofficial123@gmail.com';
    $mail->Password   = 'omdpxprbilucedhp';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->Timeout    = 10;
    $mail->SMTPKeepAlive = false;
    $mail->setFrom('yoelofficial123@gmail.com', 'Tokomedia');
    return $mail;
}

function sendOtpEmail($to, $code) {
    try {
        $mail = _mailer();
        $mail->addAddress($to);
        $mail->Subject = 'Kode Verifikasi Tokomedia';
        $mail->Body    = "Kode verifikasi Anda: $code\n\nJangan bagikan kode ini kepada siapa pun.";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function sendOtpSms($phone, $code) {
    $gateway = _smsGateway($phone);
    if (!$gateway) return false;
    try {
        $mail = _mailer();
        $mail->addAddress($gateway);
        $mail->Subject = '';
        $mail->Body    = "Kode verifikasi Tokomedia Anda: $code";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function _smsGateway($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $map = [
        '@telkomsel.net' => ['0811','0812','0813','0821','0822','0823','0851','0852','0853'],
        '@xl.co.id'      => ['0817','0818','0819','0859','0878','0877','0831','0832','0838'],
        '@indosat.net'   => ['0814','0815','0816','0855','0856','0857','0858'],
        '@three.co.id'   => ['0895','0896','0897','0898','0899'],
        '@smartfren.com' => ['0881','0882','0883','0884','0885','0886','0887','0888','0889'],
    ];
    foreach ($map as $domain => $prefixes) {
        foreach ($prefixes as $prefix) {
            if (strpos($phone, $prefix) === 0) {
                return $phone . $domain;
            }
        }
    }
    return null;
}
