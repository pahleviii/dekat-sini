# 03 — Test Case: DekatSini MVP

**Versi:** 1.0 · **Tanggal:** 2026-10-01
**Status eksekusi:** `PASS` = lulus · `FAIL` = bug (lihat `QA/04-laporan-bug.md`) · `N/A` = belum dieksekusi
**Automated:** ID dengan ✔ dijalankan otomatis di `app/tests/Feature/QA/`.

---

## Modul A — Publik / Discovery (P1)

| ID | Skenario | Prasyarat | Langkah | Ekspektasi | Pri | Auto | Hasil |
|---|---|---|---|---|---|---|---|
| TC-A01 | Landing page tampil | DB terseed | GET `/` | 200, hero search, 8 kategori, ≥1 featured | P2 | ✔ | PASS |
| TC-A02 | Direktori tampil | 12 listing aktif | GET `/services` | 200, kartu listing, total > 0 | P1 | ✔ | PASS |
| TC-A03 | Search "AC" (UAT#1) | seeding | GET `/services?search=AC` | 200, semua hasil mengandung "AC", durasi < 1 s | P1 | ✔ | PASS |
| TC-A04 | Search tanpa hasil | — | GET `/services?search=zzzzz` | 200, 0 hasil, tidak error | P2 | ✔ | PASS |
| TC-A05 | Filter kategori | 8 kategori | GET `/services?category=servis-ac` | hanya listing kategori Servis AC | P1 | ✔ | PASS |
| TC-A06 | Filter rating min | ada review | GET `/services?min_rating=4` | hanya listing ★ ≥ 4; ★ < 4 tidak muncul | P2 | ✔ | PASS |
| TC-A07 | Filter radius (UAT#1) | listing beda kota | GET `/services?lat=-6.26&lng=106.81&radius=5` | hanya listing ≤ 5 km; nilai km tampil di kartu | P1 | ✔ | PASS |
| TC-A08 | Radius > jangkauan | — | `radius=20` dengan lat/lng | listing ≤ 20 km tampil, sort terdekat | P2 | ✔ | PASS |
| TC-A09 | Sort harga naik | harga bervariasi | GET `/services?sort=price_asc` | harga kartu monoton naik | P2 | ✔ | PASS |
| TC-A10 | Sort rating | — | GET `/services?sort=rating` | ★ tertinggi pertama | P2 | ✔ | PASS |
| TC-A11 | Sort default (rekomendasi) | — | GET `/services` tanpa sort | verified teratas dulu, lalu views | P3 | ✔ | PASS |
| TC-A12 | Listing pending TIDAK tampil | 1 pending | GET `/services` + GET detail pending | tidak ada di daftar; detail = 404 | P1 | ✔ | PASS |
| TC-A13 | Detail listing aktif | seeding | GET `/services/{slug}` | 200, judul, harga, ★, badge TERVERIFIKASI, peta | P1 | ✔ | PASS |
| TC-A14 | Detail slug salah | — | GET `/services/tidak-ada` | 404 | P2 | ✔ | PASS |
| TC-A15 | Galeri foto & review tampil | service + photo + review | GET detail | foto galeri tampil, daftar review tampil, "Jasa Terkait" tampil | P2 | ✔ | PASS |
| TC-A16 | Kontrol UI filter (radius/sort) | — | GET `/services` | ada slider radius, dropdown sort, opsi rating 3+/4+/5 | P1 | ✔ | PASS |

## Modul B — Konversi / Lead (P1)

| ID | Skenario | Prasyarat | Langkah | Ekspektasi | Pri | Auto | Hasil |
|---|---|---|---|---|---|---|---|
| TC-B01 | Link WA template (UAT#2) | listing aktif | GET detail | href `https://wa.me/628…?text=Halo%2C+saya+lihat…[Jasa]…DekatSini` | P1 | ✔ | PASS |
| TC-B02 | Log lead WhatsApp (UAT#2) | listing aktif | POST `/leads` channel=whatsapp | 200 JSON ok, baris `leads` tercipta | P1 | ✔ | PASS |
| TC-B03 | Log lead phone & quote | — | POST `/leads` channel=phone / quote (+message) | lead tercipta sesuai channel, message tersimpan | P1 | ✔ | PASS |
| TC-B04 | Lead tampil di dashboard (UAT#2) | provider = pemilik | setelah lead, GET `/dashboard` | kartu "Kontak Baru" ≥ 1 | P1 | ✔ | PASS |
| TC-B05 | Channel tak valid ditolak | — | POST `/leads` channel=telegram | 422, tidak ada baris baru | P2 | ✔ | PASS |
| TC-B06 | service_id tak valid ditolak | — | POST `/leads` service_id=999999 | 422 | P2 | ✔ | PASS |
| TC-B07 | Rate limit 30/menit | — | POST `/leads` × 31 | request ke-31 → 429 | P1 | ✔ | PASS |
| TC-B08 | Tombol telepon benar | listing punya phone | GET detail | `href="tel:…"` + logLead('phone') | P2 | ✔ | PASS |
| TC-B09 | Form quote mengirim message | — | GET detail | ada field pesan pada form quote; JS mengirim message | P2 | ✔ | PASS |

## Modul C — Auth (P1)

| ID | Skenario | Prasyarat | Langkah | Ekspektasi | Pri | Auto | Hasil |
|---|---|---|---|---|---|---|---|
| TC-C01 | Register sukses | — | POST `/register` valid | user dibuat, login, redirect `/dashboard` | P1 | ✔ | PASS |
| TC-C02 | Register email duplikat | user ada | POST `/register` email sama | 422 errors.email, tidak ada user baru | P2 | ✔ | PASS |
| TC-C03 | Login sukses | user ada | POST `/login` valid | 302 → `/dashboard`, sesi terautentikasi | P1 | ✔ | PASS |
| TC-C04 | Login password salah | user ada | POST `/login` salah | 422, tidak terautentikasi, **pesan error ID** | P1 | ✔ | PASS |
| TC-C05 | Halaman login copy ID | — | GET `/login` | teks "Masuk", "Lupa kata sandi?", "Ingat saya", tanpa teks EN | P1 | ✔ | PASS |
| TC-C06 | Halaman register copy ID | — | GET `/register` | teks ID, tanpa teks EN | P1 | ✔ | PASS |
| TC-C07 | Tombol Google OAuth ada | config google | GET `/login` + GET `/register` | ada tombol/link ke `/auth/google` (FR-10) | P1 | ✔ | PASS |
| TC-C08 | Tanpa auth → redirect login | guest | GET `/services-new`, `/dashboard` | 302 → `/login` | P1 | ✔ | PASS |
| TC-C09 | Logout | login | POST `/logout` | sesi habis, redirect `/` | P2 | ✔ | PASS |
| TC-C10 | Tombol logout tersedia di navbar | login | GET halaman publik (layout utama) | ada tombol/form Keluar | P2 | ✔ | PASS |
| TC-C11 | Google callback gagal terkendali | config kosong | GET `/auth/google` | redirect aman + pesan (tidak 500) | P2 | ✔ | PASS |

## Modul D — Form Listing Provider (P1)

| ID | Skenario | Prasyarat | Langkah | Ekspektasi | Pri | Auto | Hasil |
|---|---|---|---|---|---|---|---|
| TC-D01 | Submit listing valid | login provider | POST `/services` lengkap | redirect `/dashboard`, status **pending**, `is_provider=true` (UAT#3) | P1 | ✔ | PASS |
| TC-D02 | Validasi field wajib | login | POST `/services` kosong | 422 pada title, category_id, description, whatsapp, address, city, latitude, longitude | P1 | ✔ | PASS |
| TC-D03 | Validasi deskripsi min 20 | login | description pendek | 422 description | P2 | ✔ | PASS |
| TC-D04 | Validasi lat/lng range | login | latitude=999 | 422 latitude | P2 | ✔ | PASS |
| TC-D05 | Foto > 2 MB ditolak | login | upload 3 MB | 422 photos.0 | P2 | ✔ | PASS |
| TC-D06 | Upload foto valid → WebP | login | upload JPG 100×100 | foto tersimpan di `storage/app/public/services`, `service_photos` baris tercipta, cover = foto pertama | P2 | ✔ | PASS |
| TC-D07 | Pesan error tampil di form | login | submit invalid lalu GET form kembali | pesan error terlihat di halaman form (bukan halaman kosong) | P1 | ✔ | PASS |
| TC-D08 | Input lama (old) dipertahankan | login | submit invalid | nilai diisi kembali (old input) | P2 | ✔ | PASS |
| TC-D09 | Nomor WA dinormalisasi | login | whatsapp `0812…` / `+62 812…` | tersimpan `62812…` sehingga `wa.me` valid (BR-06) | P2 | ✔ | PASS |
| TC-D10 | Copy halaman form ID | login | GET `/services-new` | semua label Indonesia | P2 | ✔ | PASS |

## Modul E — Manajemen Listing / Dashboard (P1)

| ID | Skenario | Prasyarat | Langkah | Ekspektasi | Pri | Auto | Hasil |
|---|---|---|---|---|---|---|---|
| TC-E01 | Dashboard tampil | provider | GET `/dashboard` | 200, 3 kartu metrik, tabel "Jasa Saya" | P1 | ✔ | PASS |
| TC-E02 | Views Today akurat (UAT#4) | service milik provider | 2× GET detail IP sama (1 jam) + 1× IP lain | `Views Today`=2 (unik per IP/jam) | P1 | ✔ | PASS |
| TC-E03 | View kemarin tidak dihitung | ServiceView kemarin | GET `/dashboard` | Views Today tidak termasuk baris kemarin | P1 | ✔ | PASS |
| TC-E04 | New Contacts akurat (UAT#4) | 2 lead hari ini utk service milik; 1 lead utk service orang lain; 1 lead kemarin | GET `/dashboard` | Kontak Baru = 2 (hanya milik & hari ini) | P1 | ✔ | PASS |
| TC-E05 | Total Services akurat | 3 listing milik, 2 milik lain | GET `/dashboard` | Total = 3 | P1 | ✔ | PASS |
| TC-E06 | Edit sendiri boleh | owner | GET `/services/{id}/edit` + PATCH valid | 200; data berubah | P1 | ✔ | PASS |
| TC-E07 | Edit orang lain DITOLAK (IDOR) | user B | GET/PATCH/DELETE listing user A | 403; data tidak berubah (BR-04) | P1 | ✔ | PASS |
| TC-E08 | Delete sendiri | owner | DELETE `/services/{id}` | listing hilang, foto rows cascade | P1 | ✔ | PASS |
| TC-E09 | Toggle status active↔paused | owner (active) | PATCH status=paused | status berubah, listing hilang dari direktori | P2 | ✔ | PASS |
| TC-E10 | Pending TIDAK bisa diaktifkan sendiri | listing pending milik sendiri | PATCH status=active | **ditolak**, status tetap pending (UAT#3, BR-01) | P1 | ✔ | PASS |
| TC-E11 | Detail edit pakai slug/id konsisten | — | klik Edit dari dashboard | halaman edit terbuka (rute `services/{id}/edit`) | P2 | ✔ | PASS |
| TC-E12 | Non-provider boleh akses dashboard | user biasa | GET `/dashboard` | 200 dengan 0 (PRD: dashboard butuh `is_provider` — dicatat jika melenceng) | P3 | ✔ | PASS |

## Modul F — Keamanan & Kualitas Teknis (P1)

| ID | Skenario | Langkah | Ekspektasi | Pri | Auto | Hasil |
|---|---|---|---|---|---|---|
| TC-F01 | XSS search reflection | GET `/services?search=<script>alert(1)</script>` | teks ter-escape, tidak ada tag script aktif | P1 | ✔ | PASS |
| TC-F02 | XSS judul listing | judul mengandung HTML → GET detail/daftar | ter-escape | P1 | ✔ | PASS |
| TC-F03 | CSRF form listing | POST tanpa token | 419 | P1 | ✔ | PASS |
| TC-F04 | Tanpa auth tidak bisa POST lead protected / form | POST `/services` guest | 302 login | P1 | ✔ | PASS |
| TC-F05 | Kata sandi tersimpan ter-hash | register/login | `password` bukan plaintext, sesi aman | P1 | ✔ | PASS |
| TC-F06 | Lead tidak bisa dibuat utk listing pending/paused | POST `/leads` service_id pending | 422/ditolak (data integrity) | P2 | ✔ | PASS |

## Modul G — E2E Manual (browser)

| ID | Alur | Langkah | Ekspektasi | Pri | Hasil |
|---|---|---|---|---|---|
| TC-G01 | Budi: cari → detail | Buka `/` → search "Servis AC" → klik Lihat Detail | hasil < 1 s, detail sesuai desain | P1 | PASS |
| TC-G02 | Budi: klik WhatsApp | Klik tombol WhatsApp | tab wa.me terbuka dgn template; `leads` tercatat | P1 | PASS |
| TC-G03 | Pak Hendra: login → listing baru | Login → Pasang Jasa → isi form + pin peta + foto | sukses, redirect dashboard, status Pending | P1 | PASS |
| TC-G04 | Pak Hendra: metrik dashboard | Buka detail 1× (jendela lain), klik WA 1× | Views Today & Kontak Baru naik | P1 | PASS |
| TC-G05 | Responsif 390px | Ubah viewport mobile di `/services` | filter tetap terjangkau, kartu 1 kolom | P2 | PASS |
