# 📱 List App Prem — Backend API + Dashboard

Backend REST API & Dashboard untuk pencatatan transaksi penjualan App Premium.  
Menggantikan Google Sheets sebagai backend, dan terintegrasi dengan **Telegram Bot** & **WhatsApp Bot**.

**Tech Stack:** Laravel 12 • MySQL • Filament v3 • REST API

---

## 📋 Fitur

### 🖥️ Dashboard Web (Filament)
- **CRUD Pemasukan** — Catat penjualan app (CapCut, Canva, Spotify, dll)
- **CRUD Pengeluaran** — Catat pengeluaran harian
- **CRUD Email** — Catat akun email (password encrypted)
- **API Key Management** — Kelola API key untuk bot
- **Dashboard Widgets:**
  - 💰 Stats: Pemasukan Hari Ini, Bulan Ini, Pengeluaran, Nett Profit
  - 📈 Chart: Revenue 30 hari terakhir
  - 🏆 Chart: Top 5 Aplikasi Terlaris
  - 🕐 Tabel: 10 Transaksi Terbaru

### 🔗 REST API (untuk Bot)
Semua command bot lama (`/add`, `/today`, `/summary`, dll) tersedia sebagai API endpoint.  
Bot tinggal panggil API dan kirim `message` dari response langsung ke chat.

---

## 🚀 Setup

### Prasyarat
- PHP 8.2+
- Composer
- MySQL (XAMPP/Laragon)
- Node.js 18+ (opsional, untuk Telegram Bot)

### 1. Clone & Install

```bash
cd d:\bot-sansrine\be_list_appprem
composer install
```

### 2. Konfigurasi Database

Buka **XAMPP Control Panel** → Start **MySQL**

Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=list_appprem
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Buat Database & Migrate

```bash
# Buat database (via phpMyAdmin atau script PHP)
php create_db.php

# Atau manual di phpMyAdmin:
# CREATE DATABASE list_appprem CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Migrate tabel
php artisan migrate

# Seed sample data + admin user
php artisan db:seed
```

### 4. Jalankan Server

```bash
php artisan serve
```

Buka:
- 🖥️ **Dashboard:** http://localhost:8000/admin
- 🔗 **API:** http://localhost:8000/api/v1/...

### 5. Login Dashboard

| | |
|---|---|
| **Email** | `fionaolivia177@gmail.com` |
| **Password** | `2133qwe1` |

---

## 🔌 Integrasi dengan Telegram Bot

Bot Telegram yang sudah ada di `D:\bot\bot-note-buy` bisa diubah dari Google Sheets ke Laravel API.

### Cara Kerja Integrasi

```
┌──────────────┐     HTTP Request      ┌──────────────────┐     MySQL
│ Telegram Bot │ ──────────────────▶  │  Laravel API     │ ──────────▶  Database
│ (Node.js)    │ ◀──────────────────  │  localhost:8000   │
└──────────────┘     JSON Response     └──────────────────┘
       │                                       │
       │  /add CapCut | 1 bulan | 8000         │
       │  ────────────────────────────▶        │
       │  POST /api/v1/incomes                  │
       │  {aplikasi, jenis, laba}               │
       │                                       │
       │  ◀────────────────────────────        │
       │  {message: "✅ Tercatat #1..."}        │
       │                                       │
       ▼  Bot kirim message ke chat             │
```

### Step 1: Buat API Key di Dashboard

1. Buka http://localhost:8000/admin
2. Login → **Settings** → **API Keys** → **Create**
3. Isi:
   - **Name:** `Telegram Bot`
   - **Key:** (auto-generated, copy ini!)
   - **Platform:** `Telegram`
4. Klik **Create**
5. **Copy API Key** yang muncul

### Step 2: Setup Bot

Buat folder baru untuk bot API, atau modifikasi bot lama:

```bash
# Buat folder baru (recommended)
mkdir D:\bot\bot-note-api
cd D:\bot\bot-note-api
npm init -y
npm install node-telegram-bot-api axios dotenv
```

Buat file `.env`:
```env
TELEGRAM_BOT_TOKEN=xxxx:xxxxxx
API_BASE_URL=http://localhost:8000/api/v1
API_KEY=paste_api_key_dari_dashboard_disini
TZ=Asia/Jakarta
```

### Step 3: Buat Bot (index.js)

Buat file `index.js` di folder bot. Contoh lengkap ada di:
📄 **`D:\bot-sansrine\be_list_appprem\bot-example\index.js`**

Bot ini menggantikan semua Google Sheets call dengan API call:

| Command Bot | API Endpoint | Method |
|-------------|-------------|--------|
| `/add Capcut \| 1 bulan \| 8000` | `POST /incomes` | POST |
| `/today` | `GET /incomes/today` | GET |
| `/yesterday` | `GET /incomes/yesterday` | GET |
| `/week` | `GET /incomes/week` | GET |
| `/month` | `GET /incomes/month` | GET |
| `/list` | `GET /incomes` | GET |
| `/summary` | `GET /incomes/summary` | GET |
| `/top` | `GET /incomes/top` | GET |
| `/stats` | `GET /incomes/stats` | GET |
| `/edit 3 laba 10000` | `PUT /incomes/3` | PUT |
| `/delete 3` | `DELETE /incomes/3` | DELETE |
| `/undo` | `DELETE /incomes/last` | DELETE |
| `/spend Makan \| Nasi \| 15000` | `POST /expenses` | POST |
| `/spendlist` | `GET /expenses` | GET |
| `/spendtoday` | `GET /expenses/today` | GET |
| `/spendmonth` | `GET /expenses/month` | GET |
| `/spenddelete 3` | `DELETE /expenses/3` | DELETE |
| `/email a@b.com \| pass \| Info` | `POST /emails` | POST |
| `/emaillist` | `GET /emails` | GET |
| `/emailedit 1 akun new@b.com` | `PUT /emails/1` | PUT |
| `/emaildelete 1` | `DELETE /emails/1` | DELETE |

### Step 4: Jalankan

```bash
# Terminal 1 — Laravel API (harus jalan dulu!)
cd D:\bot-sansrine\be_list_appprem
php artisan serve

# Terminal 2 — Bot Telegram
cd D:\bot\bot-note-api
node index.js
```

---

## 📡 API Reference

**Base URL:** `http://localhost:8000/api/v1`  
**Auth:** Header `X-API-Key: {api_key}`

### Response Format

Semua response mengandung field `message` yang **siap dikirim langsung ke chat** (format sama seperti bot lama):

```json
{
  "success": true,
  "message": "✅ Tercatat #1\n11/04/2026\nCapCut | 1 bulan | Rp 8.000",
  "data": { ... },
  "total": 8000
}
```

### Pemasukan (Income)

#### `POST /incomes` — Tambah pemasukan
```json
{
  "aplikasi": "CapCut",
  "jenis": "1 bulan",
  "laba": 8000,
  "source_user": "Ahmad"
}
```

#### `GET /incomes/today` — Transaksi hari ini
#### `GET /incomes/yesterday` — Transaksi kemarin
#### `GET /incomes/week` — Transaksi minggu ini
#### `GET /incomes/month` — Transaksi bulan ini
#### `GET /incomes` — Semua transaksi
#### `GET /incomes/summary` — Ringkasan per aplikasi
#### `GET /incomes/top` — Top 5 aplikasi
#### `GET /incomes/stats` — Statistik lengkap

#### `PUT /incomes/{id}` — Edit transaksi
```json
{
  "laba": 10000
}
```

#### `DELETE /incomes/{id}` — Hapus transaksi
#### `DELETE /incomes/last` — Undo (hapus terakhir)

### Pengeluaran (Expense)

#### `POST /expenses` — Tambah pengeluaran
```json
{
  "kategori": "Makan",
  "keterangan": "Nasi padang",
  "nominal": 15000,
  "source_user": "Ahmad"
}
```

#### `GET /expenses` — Semua pengeluaran
#### `GET /expenses/today` — Pengeluaran hari ini
#### `GET /expenses/month` — Pengeluaran bulan ini
#### `DELETE /expenses/{id}` — Hapus pengeluaran

### Email

#### `POST /emails` — Tambah email
```json
{
  "akun": "test@gmail.com",
  "password": "pass123",
  "keterangan": "Email utama",
  "source_user": "Ahmad"
}
```

#### `GET /emails` — Semua email
#### `PUT /emails/{id}` — Edit email
#### `DELETE /emails/{id}` — Hapus email

### Error Response

```json
{
  "success": false,
  "message": "API key is required. Set X-API-Key header."
}
```

| HTTP Code | Arti |
|-----------|------|
| 200 | OK |
| 201 | Created (data baru ditambahkan) |
| 401 | API key salah/tidak ada |
| 404 | Data tidak ditemukan |
| 422 | Validasi gagal (field wajib kurang) |

---

## 🗄️ Database

### Tabel `incomes`
| Column | Type | Keterangan |
|--------|------|------------|
| id | bigint | Auto increment |
| tanggal | date | Tanggal transaksi |
| aplikasi | string | Nama app (CapCut, Canva, dll) |
| jenis | string | Tipe (1 bulan, lifetime, dll) |
| laba | decimal | Laba/keuntungan |
| source | enum | `telegram` / `whatsapp` / `dashboard` |
| source_user | string | Siapa yang input |

### Tabel `expenses`
| Column | Type | Keterangan |
|--------|------|------------|
| id | bigint | Auto increment |
| tanggal | date | Tanggal |
| kategori | string | Makan, Akun, Transport, dll |
| keterangan | string | Deskripsi |
| nominal | decimal | Jumlah |
| source | enum | Asal input |

### Tabel `emails`
| Column | Type | Keterangan |
|--------|------|------------|
| id | bigint | Auto increment |
| akun | string | Email address |
| password | text | Password (encrypted) |
| keterangan | string | Catatan |

### Tabel `api_keys`
| Column | Type | Keterangan |
|--------|------|------------|
| id | bigint | Auto increment |
| name | string | Nama service/bot |
| key | string(64) | API key (unique) |
| platform | enum | `telegram` / `whatsapp` / `other` |
| is_active | boolean | Aktif/tidak |

---

## 📁 Struktur Project

```
be_list_appprem/
├── app/
│   ├── Filament/
│   │   ├── Resources/          # Dashboard CRUD
│   │   │   ├── IncomeResource.php
│   │   │   ├── ExpenseResource.php
│   │   │   ├── EmailResource.php
│   │   │   └── ApiKeyResource.php
│   │   └── Widgets/            # Dashboard widgets
│   │       ├── StatsOverview.php
│   │       ├── RevenueChart.php
│   │       ├── TopAppsChart.php
│   │       └── LatestTransactions.php
│   ├── Http/
│   │   ├── Controllers/Api/V1/ # API Controllers
│   │   │   ├── IncomeController.php
│   │   │   ├── ExpenseController.php
│   │   │   └── EmailController.php
│   │   └── Middleware/
│   │       └── ValidateApiKey.php
│   ├── Models/
│   │   ├── Income.php
│   │   ├── Expense.php
│   │   ├── Email.php
│   │   └── ApiKey.php
│   └── Services/               # Business logic
│       ├── IncomeService.php
│       ├── ExpenseService.php
│       └── EmailService.php
├── bot-example/                 # Contoh bot Telegram
│   ├── index.js
│   ├── package.json
│   └── .env.example
├── database/migrations/
├── routes/api.php
└── .env
```
#   b a c k e n d _ l i s t p r e m  
 