# Dashboard Pemantauan Energi (Gedung Tunggal)

Stack: PHP (autentikasi + halaman), Node.js (API data energi dummy), MySQL (penyimpanan).

## 1) Persiapan Database
1. Buat database dan tabel:
   ```sql
   SOURCE schema.sql;
   ```
2. Kredensial default: username `admin`, password `admin123`.

## 2) Jalankan API Node.js
```bash
cd node-api
cp .env.example .env   # isi sesuai koneksi MySQL
npm install
npm run seed           # isi data dummy ke tabel energy_readings
npm start              # API di port 3001 (default)
```
API utama:
- `GET /health`
- `GET /energy/summary?days=30`
- `GET /energy/aggregate?mode=daily|weekly|monthly&window=14`
- `GET /energy/readings?startDate=YYYY-MM-DD&endDate=YYYY-MM-DD&limit=200`
- `GET /energy/live?interval=15&window=80` (simulasi feed real-time, polling)
- `GET /energy/live_stream?interval=5` (Server-Sent Events untuk stream real-time)

## 3) Jalankan Halaman PHP
Gunakan server bawaan PHP:
```bash
php -S localhost:8000
```
Lalu buka `http://localhost:8000/login.php`.

## 4) Fitur yang tersedia
- Login sederhana (1 user operasional).
- Dashboard: total konsumsi, status sistem, grafik (harian/mingguan/bulanan).
- Monitoring: tabel data energi + filter tanggal.
- Laporan: ringkasan 30 hari, tabel laporan bulanan.
- Catatan teknis: tambah catatan, ubah status (Not started / In Progress / Completed).

Semua data energi bersifat dummy/simulasi dan disimpan di database SQL. Tidak ada integrasi sensor atau API eksternal.
