# Cara Integrasi Bot Telegram dengan Laravel API

## 1. Buka Dashboard, Buat API Key

1. Buka http://localhost:8000/admin
2. Login: `fionaolivia177@gmail.com` / `2133qwe1`
3. Klik **API Keys** di sidebar → **Create**
4. Isi **Name**: `Telegram Bot`, **Platform**: `Telegram`
5. **Copy API Key** yang muncul

## 2. Setup Bot

```bash
cd d:\bot-sansrine\be_list_appprem\bot-example
npm install
```

## 3. Isi .env

Copy `.env.example` jadi `.env`, lalu isi:

```env
TELEGRAM_BOT_TOKEN=token_bot_kamu
API_BASE_URL=http://localhost:8000/api/v1
API_KEY=api_key_yang_dicopy_dari_dashboard
TZ=Asia/Jakarta
```

## 4. Jalankan

Buka 2 terminal:

**Terminal 1 — Laravel (harus jalan duluan):**
```bash
cd d:\bot-sansrine\be_list_appprem
php artisan serve
```

**Terminal 2 — Bot Telegram:**
```bash
cd d:\bot-sansrine\be_list_appprem\bot-example
node index.js
```

Kalau muncul `✅ Bot is running and waiting for messages...` berarti sudah jalan.

## 5. Test di Telegram

Kirim ke bot:
```
/add Capcut | 1 bulan | 8000
/today
/summary
```

Data otomatis masuk ke MySQL dan muncul di dashboard.
