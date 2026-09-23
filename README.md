# 🌿 Practicum Logbook System

Sistem logbook praktikal untuk 2 jenis user — **Student** & **Company Supervisor**. Theme hijau/emerald, mobile-friendly, ada tab navigation, tandatangan digital, dan boleh print jadi PDF saiz A4 (muka depan design UUM asal tak diubah).

## 📁 Struktur Fail
```
public/            → semua fail yang accessible dari browser (document root)
  login.php, register.php, logout.php, index.php
  student/          → dashboard, profile (Detail of Student), entries, print
  supervisor/        → dashboard, student_view (komen + sign), employer_summary
  assets/css/style.css, assets/js/signature.js, assets/images/cover.jpg
includes/           → db.php, auth.php, header.php, footer.php (dipakai semua page)
database/schema.sql → struktur table MySQL
nixpacks.toml       → config deploy Railway (guna PHP built-in server, tak perlu Composer)
```

## 🚀 Cara Deploy ke Railway (drag & drop GitHub)

1. **Buat repo GitHub baru**, drag semua fail/folder dalam zip ni terus masuk (root repo = folder `logbook-system`, bukan letak dalam sub-folder lain).
2. Dalam Railway, buat **New Project → Deploy from GitHub repo**, pilih repo tu.
3. Tambah **MySQL plugin** dalam project yang sama (macam yang awak dah ada — MYSQLHOST, MYSQLDATABASE, dsb akan auto-generate).
4. Di service PHP awak (bukan service MySQL), pergi tab **Variables** → **Add Variable Reference** → sambungkan semua variable MySQL (`MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`) dari service MySQL. Kod dalam `includes/db.php` guna nama variable yang sama macam dalam screenshot Railway awak.
5. Buka tab **Console/Database** MySQL Railway, **import `database/schema.sql`** (copy-paste isi fail tu dalam Query console, atau guna MySQL client).
6. Railway akan auto-detect `nixpacks.toml` dan run `php -S 0.0.0.0:$PORT -t public` — tak perlu Composer sebab sistem ni pure PHP (tiada dependency luar).
7. Generate domain di Railway (Settings → Networking → Generate Domain), buka link tu.

## 👤 Cara Guna

1. Buka `/register.php` — daftar akaun **Student** (untuk diri sendiri) dan satu lagi akaun **Company Supervisor** (bagi supervisor guna).
2. Student log masuk → isi tab **Detail Student** sekali sahaja (ini akan masuk dalam muka "Detail of Students" PDF nanti).
3. Setiap kali buat kerja, pergi tab **Logbook Entries** → isi tarikh sendiri, upload gambar dari gallery telefon, tulis apa yang dibuat.
4. Supervisor log masuk → tab **Senarai Student** → pilih student → boleh:
   - Tengok semua entri logbook student
   - Isi **komen mingguan** (tarikh minggu isi sendiri, bukan auto) + **tandatangan digital** (lukis terus atas skrin/telefon)
   - Isi **Employer's Summary** di akhir praktikal + tandatangan/cop
5. Student pergi tab **Print PDF** → klik "Print / Save as PDF" → pilih **Save as PDF** pada printer destination, pastikan paper size **A4** → siap, satu PDF lengkap termasuk muka depan UUM (asal, tak berubah), Detail of Students, semua entri ikut minggu + komen & tandatangan supervisor, dan Employer's Summary.

## 🖼️ Nota Penting

- Muka depan (`assets/images/cover.jpg`) adalah cover asal UUM yang awak upload — **tak disentuh langsung**, terus dipaparkan sebagai imej penuh page pertama semasa print.
- Semua gambar entri disimpan sebagai **base64 dalam database** (bukan dalam folder storage), sebab storage di Railway bersifat *ephemeral* (hilang bila redeploy) — ini sama macam workaround yang awak dah guna sebelum ini.
- Tarikh (entri logbook & minggu komen supervisor) **semua manual**, tiada auto-date, supaya boleh isi lewat kalau tak sempat.
- Tandatangan digital dilukis terus atas `<canvas>` guna JavaScript asli (tiada library luar), disimpan sebagai imej PNG base64.

## 🔧 Nak Test Local (XAMPP)

1. Letak folder ni dalam `htdocs/`.
2. Buat database MySQL local, import `database/schema.sql`.
3. Set environment variable local (boleh guna `.env`-style atau edit terus `includes/db.php` fallback default `localhost` / `root` / password kosong untuk XAMPP).
4. Buka `http://localhost/logbook-system/public/login.php`.
