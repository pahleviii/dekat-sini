# 01 — Analisis Kebutuhan Sistem: DekatSini

**Tanggal:** 2026-10-01
**Sumber:** `prd.md` v1.1.0, `brd.md` v1.0.0, `SETUP.md`, `UI Design/` (6 screen)
**Status:** Disetujui untuk pengujian (MVP Laravel)

---

## 1. Ringkasan Sistem

DekatSini adalah marketplace direktori jasa hyperlocal (Indonesia). MVP: Laravel 12/13 + MySQL 8 + Blade + Tailwind + Alpine.js + Leaflet/OSM. Bahasa utama `id-ID`. Auth: Breeze Email/Password + Socialite Google. Model bisnis MVP: listing gratis, verifikasi manual, CTA WhatsApp sebagai kanal konversi utama.

## 2. Batasan Scope (Traceability)

### IN SCOPE (MVP)
| # | Kebutuhan | Rute/Level |
|---|---|---|
| FR-01 | Pencarian query (title, deskripsi, keyword) | `GET /services?search=` |
| FR-02 | Filter facet: kategori, rating min (3+/4+/5), radius jarak 0–20 km | `GET /services?category=&min_rating=&radius=&lat=&lng=` |
| FR-03 | Sorting: Rekomendasi, Rating Tertinggi, Terdekat, Harga Termurah | `GET /services?sort=` |
| FR-04 | CTA konversi: WhatsApp (template prapopulasi), Telepon, Request Quote (tersimpan sebagai lead) | `GET /services/{slug}`, `POST /leads` (throttle 30/menit) |
| FR-05 | Badge trust: TERVERIFIKASI, garansi, teknisi bersertifikat | `services.is_verified` |
| FR-06 | Social proof: rating, review read-only, galeri foto portofolio | `reviews`, `service_photos` |
| FR-07 | Form listing 3 bagian: metadata, alamat + pin Leaflet (lat/lng wajib), upload foto (maks 2 MB, WebP) | `POST /services` → status `pending` |
| FR-08 | Dashboard metrik: Total Jasa, Views Today, New Contacts | `GET /dashboard` |
| FR-09 | Manajemen listing: toggle status Active/Paused, edit, delete | `PATCH /services/{id}`, `DELETE /services/{id}` |
| FR-10 | Auth: Email/Password + Google OAuth, role tunggal `users.is_provider` | `/login`, `/register`, `/auth/google` |

### OUT OF SCOPE (Phase 2/3 — tidak boleh diuji sebagai bug)
Kalender booking · Payment (QRIS/GoPay/OVO) · Submit review oleh user · Aplikasi mobile · Tracking real-time · Admin panel otomatis (verifikasi MVP manual via WA admin).

## 3. Kriteria Penerimaan (UAT) — `brd.md` §9
1. Cari "AC" → hasil < 1 detik, **filter radius bekerja**.
2. Klik WA → template `Halo, saya lihat [Jasa] di DekatSini...` + **tercatat di dashboard provider**.
3. Provider tambah jasa → **status Pending**, muncul setelah approve (tanpa self-approve).
4. Dashboard angka **Views Today** & **New Contacts** akurat.

## 4. KPI (`brd.md` §6)
| Metrik | Target |
|---|---|
| Listing aktif | ≥ 100 (`services.status=active`) |
| WA clicks/bulan | ≥ 500 (`leads.channel=whatsapp`) |
| Views/listing/hari | ≥ 10 (`service_views`) |
| LCP mobile | < 2.5 s |

## 5. Kebutuhan Non-Fungsional
- **Lokalisasi:** seluruh copy UI bahasa Indonesia; pesan error validasi bahasa Indonesia.
- **Keamanan:** sesi aman, hash password, rate-limit endpoint lead 30/menit, proteksi akses antar-user (IDOR), escaping output (XSS), CSRF pada form.
- **Performa:** pencarian < 1 s; Web Vitals LCP < 2.2–2.5 s.
- **Integritas data:** view dihitung 1× per IP/jam; metrik dashboard = agregat milik user sendiri saja.
- **Media:** foto disimpan `storage/app/public`, konversi WebP, maks 2 MB/file.

## 6. Aturan Bisnis Kunci
| ID | Aturan | Implikasi Pengujian |
|---|---|---|
| BR-01 | Listing baru = `pending`, hanya tampil di direktori setelah approve | Self-approve via edit = BUG |
| BR-02 | View unik per IP/jam (cache) | Duplikasi view = BUG |
| BR-03 | Lead hanya untuk kanal whatsapp/phone/quote + throttle 30/menit | Kanal tak valid lolos = BUG |
| BR-04 | Hanya pemilik listing yang boleh edit/hapus | IDOR = BUG (Critical) |
| BR-05 | Metrik dashboard hanya menghitung data milik pemilik | Bocor data user lain = BUG |
| BR-06 | WA number format `628…` untuk `wa.me/{number}` | Nomor `08…` → CTA rusak = BUG |
| BR-07 | Radius hanya berlaku bila lokasi (lat/lng) tersedia | Slider radius tanpa sumber lokasi = BUG (tidak fungsional) |

## 7. Peta Halaman → Desain
| Rute | View | Referensi UI Design |
|---|---|---|
| `/` | `home/index` | dekatsini_landing_page |
| `/services` | `services/index` | service_directory_listings |
| `/services/{slug}` | `services/show` | service_detail_view |
| `/login`, `/register` (`/auth`) | `auth/*` | user_authentication_page |
| `/services-new` (`/services/new`) | `services/create` | add_new_service_form |
| `/dashboard` | `dashboard/index` | provider_dashboard |
| `/profile` | `profile/edit` | (Breeze, tanpa referensi) |

## 8. Risiko Pengujian
- Tidak ada admin panel → approve diuji lewat mekanisme yang tersedia di aplikasi.
- Geolocation bersifat client-side → pengujian radius memakai parameter `lat`/`lng` eksplisit + verifikasi DOM kontrol UI.
- Data seeder (12 listing) dipakai untuk pengujian manual; automated test memakai database `dekatsini_testing` + `RefreshDatabase`.
