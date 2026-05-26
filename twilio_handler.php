<?php

define('TWILIO_ACCOUNT_SID', getenv('TWILIO_ACCOUNT_SID') ?: 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('TWILIO_AUTH_TOKEN',  getenv('TWILIO_AUTH_TOKEN')  ?: 'your_auth_token_here');
define('TWILIO_FROM_NUMBER', getenv('TWILIO_FROM_NUMBER') ?: '+15550000000');

function sendSmsConfirmation(string $toPhone, string $body): array
{
    $url  = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_ACCOUNT_SID . '/Messages.json';

    $data = http_build_query([
        'To'   => $toPhone,
        'From' => TWILIO_FROM_NUMBER,
        'Body' => $body,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response   = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'error' => 'cURL error: ' . $curlError];
    }

    $decoded = json_decode($response, true);

    if ($httpStatus === 201 && isset($decoded['sid'])) {
        return ['success' => true, 'sid' => $decoded['sid']];
    }

    $errMsg = $decoded['message'] ?? ('HTTP ' . $httpStatus);
    return ['success' => false, 'error' => $errMsg];
}
