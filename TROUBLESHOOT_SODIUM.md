# Troubleshooting: Sodium Extension Tidak Aktif

Jika Anda sudah menghapus tanda `;` di depan `extension=sodium` tapi masih belum aktif, ikuti langkah-langkah berikut:

## ✅ Langkah 1: Verifikasi dengan check_extensions.php

1. Buka browser dan akses:
   ```
   http://localhost/itera4_/check_extensions.php
   ```

2. Lihat bagian "Sodium Extension"
   - Jika menunjukkan "❌ Sodium Tidak Aktif", lanjut ke langkah berikutnya
   - Jika menunjukkan "✅ Sodium Aktif", berarti sudah berhasil!

## ✅ Langkah 2: Cek File php.ini yang Benar

**PENTING:** Pastikan Anda mengedit file php.ini yang benar!

1. Buka browser dan akses:
   ```
   http://localhost/dashboard/phpinfo.php
   ```
   Atau buka XAMPP Control Panel → klik "Admin" di sebelah Apache

2. Di halaman phpinfo, cari (Ctrl+F): **"Loaded Configuration File"**
   - Ini adalah path file php.ini yang **benar-benar digunakan** oleh PHP
   - Contoh: `C:\xampp\php\php.ini`

3. Edit file tersebut (bukan file php.ini lainnya!)

## ✅ Langkah 3: Cek Format di php.ini

Pastikan formatnya **tepat** seperti ini:

```
extension=sodium
```

**JANGAN:**
- ❌ `;extension=sodium` (masih ada tanda ;)
- ❌ `extension = sodium` (ada spasi)
- ❌ `extension=sodium.dll` (jangan tambahkan .dll)
- ❌ `extension="sodium"` (jangan pakai tanda kutip)

## ✅ Langkah 4: Cek File php_sodium.dll

1. Buka folder: `C:\xampp\php\ext\`
2. Cari file: `php_sodium.dll`
3. Jika **TIDAK ADA**:
   - Untuk PHP 7.2+: Sodium seharusnya built-in, cek di phpinfo apakah ada
   - Jika tidak ada, kemungkinan PHP version Anda tidak support Sodium built-in
   - **Solusi:** Gunakan OpenSSL sebagai fallback (sudah tersedia)

## ✅ Langkah 5: Cek Error Log Apache

1. Buka XAMPP Control Panel
2. Klik tombol **"Logs"** di sebelah Apache
3. Atau buka file: `C:\xampp\apache\logs\error.log`
4. Cari error terkait "sodium" atau "php_sodium.dll"
5. Error umum:
   - `Unable to load dynamic library 'php_sodium.dll'` → File tidak ada atau architecture tidak match
   - `The specified module could not be found` → File tidak ada di folder ext

## ✅ Langkah 6: Cek Architecture (x86 vs x64)

1. Buka phpinfo
2. Cari "Architecture"
3. Pastikan:
   - Jika PHP x64, file `php_sodium.dll` juga harus x64
   - Jika PHP x86, file `php_sodium.dll` juga harus x86

## ✅ Langkah 7: Restart Apache dengan Benar

1. Di XAMPP Control Panel:
   - Klik **"Stop"** pada Apache
   - **TUNGGU** sampai status benar-benar "Stopped" (bukan hanya klik sekali)
   - Tunggu 5 detik
   - Klik **"Start"** pada Apache
   - Tunggu sampai status "Running"

2. **PENTING:** Jangan hanya refresh halaman, harus restart Apache!

## ✅ Langkah 8: Verifikasi Lagi

1. Refresh halaman `check_extensions.php`
2. Atau buka phpinfo dan cari "sodium"
3. Jika masih tidak aktif, lanjut ke langkah berikutnya

## ✅ Langkah 9: Alternatif - Gunakan OpenSSL

**TIDAK MASALAH!** Jika Sodium tidak bisa diaktifkan, sistem akan otomatis menggunakan OpenSSL sebagai fallback.

**Keuntungan OpenSSL:**
- ✅ Sudah tersedia di XAMPP
- ✅ Cukup aman untuk enkripsi API key
- ✅ Tidak perlu konfigurasi tambahan
- ✅ Sistem sudah support fallback ke OpenSSL

**Untuk memastikan OpenSSL aktif:**
1. Buka phpinfo
2. Cari "openssl"
3. Jika tidak ada, uncomment `extension=openssl` di php.ini
4. Restart Apache

## ✅ Langkah 10: Cek PHP Version

Sodium built-in tersedia mulai PHP 7.2+.

1. Buka phpinfo
2. Cek "PHP Version"
3. Jika < 7.2:
   - Sodium tidak tersedia secara built-in
   - Perlu download dari PECL
   - **Atau gunakan OpenSSL** (lebih mudah)

## 📋 Checklist

Gunakan checklist ini untuk memastikan semua langkah sudah dilakukan:

- [ ] Sudah cek file php.ini yang benar (via phpinfo)
- [ ] Format `extension=sodium` sudah benar (tanpa ;, tanpa spasi)
- [ ] File `php_sodium.dll` ada di folder `ext` (atau Sodium built-in)
- [ ] Sudah cek error log Apache
- [ ] Sudah restart Apache dengan benar (Stop → tunggu → Start)
- [ ] Sudah verifikasi dengan check_extensions.php
- [ ] Jika masih gagal, sudah cek apakah OpenSSL aktif sebagai fallback

## 🆘 Masih Tidak Bisa?

Jika setelah semua langkah di atas Sodium masih tidak aktif:

1. **Gunakan OpenSSL** - Sistem sudah support dan cukup aman
2. **Upgrade PHP** ke versi 7.4+ (Sodium built-in)
3. **Cek kompatibilitas** antara PHP version dan php_sodium.dll

**Ingat:** Tidak masalah jika menggunakan OpenSSL! Sistem dirancang untuk fallback otomatis.

