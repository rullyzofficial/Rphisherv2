
#!/bin/bash
echo "[*] Starting Cloudflared tunnel..."
php -S 127.0.0.1:8080 > /dev/null 2>&1 &
./cloudflared tunnel --url http://localhost:8080
