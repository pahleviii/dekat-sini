# 04 — Laporan Bug: DekatSini MVP

**Versi:** 1.0 · **Tanggal:** 2026-10-01 · **Penulis:** QA Engineer
**Siklus:** R1 eksekusi otomatis (temuan) → fix → R2–R4 retest → UAT manual TC-G01…G05 (lihat `QA/05-hasil-retest.md`)

---

## 1. Ringkasan

| Metrik | Nilai |
|---|---|
| Test otomatis dieksekusi (Round 1) | 89 (64 TC QA + 24 baseline + 1 profil) |
| Lulus Round 1 | 70 |
| Gagal Round 1 | 18 FAIL + 1 error test |
| **Bug aplikasi ditemukan** | **20** (BUG-001 … BUG-020) |
| Defek artefak test (bukan aplikasi) | 2 (TD-001, TD-002) |
| Critical / Major / Minor | 4 / 12 / 4 |
| Bug Fixed & retest PASS | **20 dari 20** |
| Sumber temuan | 17 dari pengujian otomatis Round 1 · 1 dari UAT manual (TC-G05) · 1 dari verifikasi handoff (BUG-019) · 1 dari pengukuran performa B1 (BUG-020) |

**Severity:** **Critical** = merusak alur inti/keamanan · **Major** = melanggar PRD/kebutuhan bisnis · **Minor** = kualitas teknis.

---

## 2. Tabel Bug

| ID | TC | Modul | Sev | Ringkasan | Status |
|---|---|---|---|---|---|
| BUG-001 | TC-A07, TC-A08 | A Discovery | Critical | Filter radius/lokasi → HTTP 500 (SQL `Duplicate column name 'id'`) | ✅ Fixed, retest PASS |
| BUG-002 | TC-A13 | A Discovery | Major | Harga tidak ditampilkan di halaman detail listing | ✅ Fixed, retest PASS |
| BUG-003 | TC-A15 | A Discovery | Major | Galeri foto tidak tampil di halaman detail | ✅ Fixed, retest PASS |
| BUG-004 | TC-A16 | A Discovery | Major | UI filter tidak lengkap: tidak ada dropdown **Urutkan**, opsi rating **3+** hilang | ✅ Fixed, retest PASS |
| BUG-005 | TC-B08 | B Lead | Major | Tombol "Hubungi" tidak membuka dial telepon (tidak ada `href="tel:…"`) | ✅ Fixed, retest PASS |
| BUG-006 | TC-B09 | B Lead | Major | Form "Minta Penawaran" tanpa field pesan; JS `logLead` tidak mengirim `message` | ✅ Fixed, retest PASS |
| BUG-007 | TC-C05 (TC-C04*) | C Auth | Major | Halaman login tidak berbahasa Indonesia; pesan error memakai kunci translasi mentah (`auth.failed`) | ✅ Fixed, retest PASS |
| BUG-008 | TC-C06 | C Auth | Major | Halaman register berbahasa Inggris; teks Inggris masih tersisa di halaman | ✅ Fixed, retest PASS |
| BUG-009 | TC-C07 | C Auth | Major | Tombol login Google (FR-10) tidak ada di halaman login & register | ✅ Fixed, retest PASS |
| BUG-010 | TC-C10 | C Auth | Major | Tidak ada tombol/form **Keluar** di navbar (pengguna login tidak bisa logout dari halaman publik) | ✅ Fixed, retest PASS |
| BUG-011 | TC-C11 | C Auth | Critical | `GET /auth/google` → **500** saat konfigurasi Google kosong (tidak gagal aman) | ✅ Fixed, retest PASS |
| BUG-012 | TC-E10 | E Dashboard | Critical | Listing **pending** bisa diaktifkan sendiri oleh provider (pelanggaran UAT#3/BR-01) | ✅ Fixed, retest PASS |
| BUG-013 | TC-D06 | D Form | Minor | Foto tidak dikonversi **WebP** — tersimpan JPG apa adanya (API Intervention v3 dipakai di instalasi v4) | ✅ Fixed, retest PASS |
| BUG-014 | TC-D07 | D Form | Major | Pesan error validasi **tidak tampil** di halaman form; pesan berupa kunci mentah `validation.*` | ✅ Fixed, retest PASS |
| BUG-015 | TC-D08 | D Form | Major | Input lama (**old input**) hilang setelah submit gagal — pengguna mengulang isi form | ✅ Fixed, retest PASS |
| BUG-016 | TC-D09 | D Form | Major | Nomor WhatsApp tidak dinormalisasi `08xx`/`+62` → `628xx` sehingga `wa.me` bisa tidak valid (BR-06) | ✅ Fixed, retest PASS |
| BUG-017 | TC-F06 | F Security | Critical | Lead bisa dibuat untuk listing **pending/paused** (pelanggaran integritas data) | ✅ Fixed, retest PASS |
| BUG-018 | TC-G05 | G UAT (manual) | Major | Responsif 390px: panel filter tersembunyi tanpa tombol toggle — filter tidak terjangkau di mobile | ✅ Fixed, retest PASS |
| BUG-019 | — (review handoff) | Build/aset | Minor | Aset build CSS stale — kelas `md:hidden` hilang → tombol mobile (☰ nav & ⚙ Buka Filter) tampil juga di desktop | ✅ Fixed, retest PASS |
| BUG-020 | — (ukuran B1) | Performa | Major | LCP mobile melebihi target 2,5 dtk: home 2656 ms · services 3314 ms · detail 2908 ms — rantai render-blocking (CSS Google Fonts + Leaflet & JS sync di semua/ halaman peta) | ✅ Fixed, retest PASS |

\* TC-C04 lolos di Round 1 dengan asersi lemah (pesan mentah `auth.failed` lolos pemeriksaan bahasa); test diperkuat dan bug digabung ke lingkup BUG-007.

---

## 3. Detail Bug & Perbaikan

### BUG-001 — Filter radius memicu HTTP 500 (Critical)
- **TC:** TC-A07, TC-A08 · **Modul:** A — Discovery
- **Gejala:** `GET /services?lat=…&lng=…&radius=…` mengembalikan 500. SQL error: `SQLSTATE[42S21]: Duplicate column name 'id'` pada subquery COUNT pagination.
- **Akar penyebab:** `ServiceController::index()` menambahkan `selectRaw("services.*, … AS distance")` padahal `withAvg('reviews','rating')` sudah menambahkan `services.*` ke daftar kolom → `services.*` terpilih dua kali → `paginate()` membungkus query menjadi derived table → kolom `id` ganda.
- **File:** `app/Http/Controllers/ServiceController.php` (membaca ulang dari index())
- **Perbaikan:** Hapus `services.*,` dari `selectRaw` (kolom sudah tersedia); sisipkan komentar penjelas.
- **Retest:** TC-A07, TC-A08 → PASS.

### BUG-002 — Harga tidak tampil di detail listing (Major)
- **TC:** TC-A13 · **Gejala:** Halaman detail tidak menampilkan `price_from` sama sekali; pengguna tidak tahu harga sebelum menghubungi penyedia.
- **Akar penyebab:** `resources/views/services/show.blade.php` tidak me-render `price_from`.
- **Perbaikan:** Tambahkan baris "Mulai Rp {price}" di hero detail (`number_format`).
- **Retest:** TC-A13 → PASS.

### BUG-003 — Galeri foto tidak tampil di detail (Major)
- **TC:** TC-A15 · **Gejala:** Foto listing (sudah diunggah & tersimpan di `service_photos`) tidak pernah ditampilkan; `$service->photos` dimuat tetapi tidak dirender.
- **Akar penyebab:** `show.blade.php` tidak memiliki blok galeri.
- **Perbaikan:** Tambahkan blok "Galeri Foto" (`@foreach($service->photos … <img src="/storage/…">)`), hanya bila ada foto.
- **Retest:** TC-A15 → PASS.

### BUG-004 — Kontrol filter direktori tidak lengkap (Major)
- **TC:** TC-A16 · **Gejala:** Sidebar filter di `/services` tidak memiliki dropdown **Urutkan** (meski backend mendukung `?sort=`), dan opsi **3+ Bintang** tidak ada (hanya 4+/5).
- **Akar penyebab:** `resources/views/services/index.blade.php` — kontrol belum ditambahkan saat fitur sorting dibuat.
- **Perbaikan:** Tambahkan `<select name="sort">` (Rekomendasi/Harga Terendah/Rating Tertinggi/Terdekat) dan `<option value="3">3+ Bintang</option>`.
- **Retest:** TC-A16 → PASS.

### BUG-005 — Tombol Hubungi tidak menelpon (Major)
- **TC:** TC-B08 · **Gejala:** Tombol "Hubungi" hanya `<button onclick="logLead('phone')">` tanpa `href="tel:…"` — tidak memanggil aplikasi telepon; TC mengharapkan `tel:` + logging.
- **Akar penyebab:** `show.blade.php`.
- **Perbaikan:** Ganti menjadi `<a href="tel:{{ $service->phone }}" onclick="logLead('phone')">`.
- **Retest:** TC-B08 → PASS.

### BUG-006 — Form quote tidak mengirim pesan (Major)
- **TC:** TC-B09 · **Gejala:** Form "Minta Penawaran" hanya tombol; tidak ada field pesan dan `logLead` hanya mengirim `{service_id, channel}` — kebutuhan pengguna tidak sampai ke penyedia.
- **Akar penyebab:** `show.blade.php` (markup form + fungsi JS `logLead`).
- **Perbaikan:** Tambahkan `<textarea name="message" id="quote-message">`; `logLead` membaca nilai field tersebut dan menyertakannya dalam body JSON `POST /leads`.
- **Retest:** TC-B09 → PASS.

### BUG-007 — Copy halaman login & pesan auth tidak berbahasa Indonesia (Major)
- **TC:** TC-C05 (+ TC-C04 diperkuat) · **Gejala:** Label halaman login berbahasa Inggris/mentah ("Log in", "Forgot your password?", "Remember me"); pesan login gagal berupa kunci mentah `auth.failed` (karena `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=id`, tetapi **tidak ada file `lang/id`** — fallback pun `id`, sehingga kunci translasi tampil apa adanya).
- **Akar penyebab:** Tidak ada `lang/id.json` & `lang/id/auth.php`.
- **Perbaikan:** Buat `lang/id.json` (string UI Breeze), `lang/id/auth.php`, `lang/id/passwords.php`, `lang/id/validation.php` (lengkap, termasuk `attributes` ramah-Indonesia: deskripsi, judul jasa, nomor WhatsApp, dst.).
- **Retest:** TC-C04, TC-C05 → PASS.

### BUG-008 — Copy halaman register & komentar HTML Inggris (Major)
- **TC:** TC-C06 · **Gejala:** Label register Inggris; teks Inggris tersisa di halaman (komentar HTML `<!-- Confirm Password -->` ikut terdeteksi "teks EN" oleh pengujian).
- **Akar penyebab:** `lang/id` tidak ada + komentar view belum diterjemahkan.
- **Perbaikan:** File `lang/id.json` (BUG-007) + terjemahkan komentar di `auth/register.blade.php` & `auth/login.blade.php`.
- **Retest:** TC-C06 → PASS.

### BUG-009 — Tombol login Google tidak ada (Major)
- **TC:** TC-C07 (PRD FR-10) · **Gejala:** Halaman login & register tidak memiliki akses ke `/auth/google` meski rute sudah ada.
- **Akar penyebab:** View auth tidak menyertakan tombol Google.
- **Perbaikan:** Tambahkan pemisah "atau" + tombol "Masuk/Daftar dengan Google" (`route('auth.google')`) di `auth/login.blade.php` dan `auth/register.blade.php`.
- **Retest:** TC-C07 → PASS.

### BUG-010 — Tidak ada tombol Keluar di navbar (Major)
- **TC:** TC-C10 · **Gejala:** Setelah login, layout utama hanya menampilkan link Dashboard; tidak ada cara logout dari halaman publik.
- **Akar penyebab:** `resources/views/layouts/app.blade.php` tidak punya form logout.
- **Perbaikan:** Tambahkan form `POST /logout` dengan tombol **Keluar** pada cabang `@auth`.
- **Retest:** TC-C10 → PASS.

### BUG-011 — Google OAuth tanpa konfigurasi → 500 (Critical)
- **TC:** TC-C11 · **Gejala:** `GET /auth/google` melempar exception (Socialite menerima config `null`) → halaman 500/stacktrace, bukan redirect aman dengan pesan.
- **Akar penyebab:** `config/services.php` tidak punya blok `google`; `AuthGoogleController::redirect()` tanpa guard.
- **Perbaikan:** Tambahkan konfigurasi `services.google` (env `GOOGLE_CLIENT_ID/SECRET/REDIRECT_URI`); guard `configured()` di `redirect()` & `callback()` → redirect ke `/login` dengan pesan "Login Google belum dikonfigurasi…".
- **Retest:** TC-C11 → PASS (302 + pesan, tanpa 500).

### BUG-012 — Listing pending bisa diaktifkan sendiri (Critical)
- **TC:** TC-E10 · **Gejala:** `PATCH /services/{id}` dengan `status=active` pada listing **pending** diterima → status berubah aktif tanpa verifikasi admin (melanggar UAT#3/BR-01).
- **Akar penyebab:** `ServiceController::update()` menerima `status in:active,paused` tanpa memeriksa status saat ini.
- **Perbaikan:** Tolak transisi `pending → active` dengan `ValidationException` ("menunggu verifikasi admin…"); tampilkan pesan error di form edit.
- **Retest:** TC-E10 → PASS (status tetap `pending`).

### BUG-013 — Foto tidak dikonversi WebP (Minor)
- **TC:** TC-D06 · **Gejala:** Upload foto berhasil, tetapi file tersimpan sebagai JPG apa adanya; `try/catch` diam-diam jatuh ke fallback `store()` (tanpa konversi).
- **Akar penyebab:** `composer.json` memasang `intervention/image ^4.3`, tetapi kode memakai API v3 (`ImageManager::read()`, `->toWebp()`) yang sudah tidak ada di v4 → `Throwable` → fallback.
- **Perbaikan:** Sesuaikan API v4: `decode()` + `scaleDown()` + `encode(new WebpEncoder(80))`; fallback tetap ada jika pemrosesan gagal.
- **Retest:** TC-D06 → PASS (file `.webp`, cover = foto pertama, baris `service_photos` tercipta).

### BUG-014 — Pesan error validasi tidak tampil di form (Major)
- **TC:** TC-D07 · **Gejala:** Submit invalid → redirect kembali ke form → halaman bersih tanpa indikasi kesalahan; dan jika pesan tampil pun masih berupa kunci mentah (`validation.min.string`).
- **Akar penyebab:** `services/create.blade.php` tidak merender `$errors`; `lang/id/validation.php` tidak ada.
- **Perbaikan:** Blok ringkasan error (`$errors->any()` → daftar merah di atas form) + `lang/id/validation.php` lengkap (BUG-007); form edit juga diberi blok serupa.
- **Retest:** TC-D07 → PASS (pesan "Bidang deskripsi harus setidaknya 20 karakter." tampil).

### BUG-015 — Old input hilang setelah submit gagal (Major)
- **TC:** TC-D08 · **Gejala:** Semua isian (judul, WA, alamat, dll.) kosong kembali setelah submit gagal.
- **Akar penyebab:** Input `create.blade.php` tidak memakai `old()`.
- **Perbaikan:** `value="{{ old('…') }}"` / isi textarea `{{ old('…') }}` / `@selected(old('category_id'))` untuk seluruh field (termasuk lat/lng readonly & default kota/harga).
- **Retest:** TC-D08 → PASS.

### BUG-016 — Nomor WhatsApp tidak dinormalisasi (Major)
- **TC:** TC-D09 · **Gejala:** `0812…` / `+62 812…` tersimpan apa adanya → link `wa.me/0812…` tidak valid (harus `628…`) — BR-06.
- **Akar penyebab:** `ServiceController::store()` & `update()` tanpa normalisasi.
- **Perbaikan:** Metode `normalizeWhatsapp()` (strip non-digit; `0…` → `62…`) dipanggil di `store()` dan `update()`.
- **Retest:** TC-D09 → PASS (kedua format → `6281234567890`).

### BUG-017 — Lead diterima untuk listing non-aktif (Critical)
- **TC:** TC-F06 · **Gejala:** `POST /leads` dengan `service_id` listing pending/paused → 200 & tersimpan (integritas data rusak: lead untuk listing yang tidak bisa dilihat publik).
- **Akar penyebab:** `LeadController::store()` hanya memvalidasi `exists:services,id` tanpa filter status.
- **Perbaikan:** `Rule::exists('services','id')->where('status','active')` → 422 & tidak ada baris tersimpan.
- **Retest:** TC-F06 → PASS (pending & paused sama-sama ditolak).

### BUG-018 — Panel filter tidak terjangkau di viewport 390px (Major)
- **TC:** TC-G05 (UAT manual) · **Modul:** G — Alur Utama Pengguna · **Sumber:** pengujian manual E2E
- **Gejala:** Pada viewport 390px, kartu hasil tampil 1 kolom dengan benar, tetapi panel filter (`<aside>`) ber-class `hidden md:block` → `display:none` dan **tidak ada tombol toggle** apa pun. Pengguna HP tidak dapat mengakses pencarian, kategori, rating, urutkan, maupun radius — padahal TC-G05 mensyaratkan "filter tetap terjangkau".
- **Akar penyebab:** `resources/views/services/index.blade.php` — panel filter disembunyikan di bawah breakpoint `md` tanpa alternatif akses mobile.
- **Perbaikan:** (1) tombol **"⚙ Buka Filter"** (`md:hidden`) yang meng-toggle class `hidden` pada `#filterPanel`; (2) container diubah menjadi `flex flex-col md:flex-row` agar panel muncul di atas hasil saat dibuka di mobile; (3) lebar aside `w-full md:w-64` agar melebar penuh di mobile.
- **Retest:** TC-G05 → PASS — di 390px tombol toggle tampil, panel terbuka (Cari, 8 kategori, Rating Min, Urutkan, Radius, Terapkan) dan bisa ditutup; kartu tetap 1 kolom (`grid-template-columns: 343px`). Desktop ≥768px tidak berubah. Verifikasi otomatis Round 4: **89/89 PASS**.
- **Bukti:** `QA/evidence/g05-before-filter-tersembunyi.png`, `QA/evidence/g05-after-filter-terjangkau.png`

### BUG-019 — Aset build CSS stale: kelas `md:hidden` hilang (Minor)
- **Sumber:** verifikasi handoff development (2026-10-02) · **Modul:** build/aset · **Tanpa TC** (temuan review)
- **Gejala:** Pada viewport desktop, tombol **☰ hamburger** (nav buatan development) dan tombol **⚙ Buka Filter** (fix BUG-018) ikut tampil — keduanya seharusnya `md:hidden` (tersembunyi ≥ 768px). Keduanya menampilkan gejala sama → satu akar penyebab.
- **Akar penyebab:** Tailwind CSS dibangun (JIT) dari sumber pada waktu build terakhir; literal `md:hidden` belum ada di sumber mana pun saat build → kelas **di-purge** dari `public/build/assets/app-A-tb9K9u.css`. Sumber view sudah benar; yang basi hanya artefak build (hash CSS tidak berubah sejak sebelum fix BUG-018).
- **Deteksi:** pemeriksaan `Contains('md\:hidden')` pada CSS hasil build → **HILANG**, lalu diverifikasi di browser (1296px): kedua tombol `display:block` (seharusnya `none`).
- **Perbaikan:** `npm run build` (CSS baru `app-CBzIKGpp.css` — kelas `md:hidden` kini ADA; JS tidak berubah).
- **Retest:** desktop ≥768px → ☰ & ⚙ `display:none`, nav link tampil normal · 390px → hamburger terbuka (panel link + Masuk/Keluar, ikon ☰→✕, `aria-expanded` benar) & filter tetap terjangkau · suite otomatis **89/89 PASS, 388 asersi**.
- **Catatan koreksi dokumen:** klaim "desktop ≥768px tidak berubah" pada catatan retest BUG-018 baru **benar setelah** perbaikan ini — sebelumnya tombol filter sebenarnya ikut tampil di desktop (tidak terverifikasi saat itu).
- **Bukti:** `QA/evidence/handoff-desktop-tanpa-tombol-mobile.png`, `QA/evidence/handoff-nav-mobile-terbuka.png`

### BUG-020 — LCP mobile melebihi target 2,5 dtk (Major)
- **Sumber:** pengukuran B1 (Lighthouse mobile, simulated throttling) · **Modul:** performa/aset · **Tanpa TC** (metrik dari `QA/01-analisis`)
- **Gejala (sebelum fix):** home **2656 ms** (skor 84), `/services` **3314 ms** (skor 83), detail jasa **2908 ms** (skor 90) — ketiga > target 2500 ms. FCP ≈ LCP → catatan pertama terblokir.
- **Akar penyebab:** rantai render-blocking di `<head>` layout: CSS `fonts.googleapis.com` (823–1482 ms) + `unpkg leaflet.css` (823–866 ms) **di semua halaman**, serta `leaflet.js` sinkron — padahal Leaflet hanya dipakai 2 halaman (`show`, `create`). Bonus: CLS home 0.172 akibat swap font Inter (metrik ≠ fallback).
- **Perbaikan (QA):**
  1. Google Fonts → async (`media="print" onload` + `preconnect` + fallback `<noscript>`);
  2. Leaflet dikeluarkan dari layout → diinjeksikan **dinamis oleh halaman yang memakainya** (CSS+JS via `createElement` setelah paint, map di-init setelah keduanya siap);
  3. Fallback font `Inter Fallback` dgn `size-adjust/ascent-override` agar swap tak menggeser layout (CLS).
- **Retest B1:** home **1518 ms** (skor 99, CLS 0.001) · services **1515 ms** (skor 99, CLS 0) · detail **1653 ms** (skor 99, CLS 0.000) — **semua PASS < 2500 ms**, render-blocking bersih. Peta tetap jalan (browser: `leaflet-container` + tile; Playwright: cek `detail-map` lolos di 3 mesin). Regresi otomatis **89/89 PASS · 388 asersi**.
- **Catatan:** pengukuran di server lokal dgn simulated throttling — saat staging terpublikasi, ukur ulang (TTFB produksi menambah ~100–300 ms; margin saat ini ±850 ms masih longgar).
- **Bukti:** `QA/evidence/lighthouse-{home,services,detail}-{before,after}.json`

---

## 4. Defek Artefak Test (bukan bug aplikasi)

| ID | TC | Keterangan | Penanganan |
|---|---|---|---|
| TD-001 | TC-E08 | Penggunaan `assertDatabaseMissing($table, $data, $message)` — parameter ke-3 adalah **koneksi**, bukan pesan → error fatal saat eksekusi Round 1. | Diperbaiki: `assertTrue(ServicePhoto::…->exists(), pesan)`. Bukan bug aplikasi (alur cascade sendiri benar). |
| TD-002 | TC-D07 | Teknik: `assertSessionHasErrors()` memanggil `TestResponse::session()` → `Store::start()` di tengah alur. Pada serialisasi session JSON, `marshalErrorBag()` berjalan dua kali (objek `ViewErrorBag` di-iterasi ulang → 0 properti publik) sehingga flash **errors kosong** saat request GET berikutnya — padahal di produksi session tidak pernah di-start dua kali antar-request. | Diperbaiki: alur test dirapikan dengan `followingRedirects()` (POST → redirect → GET dalam satu rantai). Bukan bug aplikasi. |

---

## 5. Status

Seluruh **20 bug aplikasi** (BUG-001 … BUG-020) telah diperbaiki dan diverifikasi ulang. Ringkasan retest: `QA/05-hasil-retest.md`.
