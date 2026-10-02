# 05 — Hasil Retest: DekatSini MVP

**Versi:** 1.0 · **Tanggal:** 2026-10-02 · **Penulis:** QA Engineer
**Relasi:** eksekusi → `QA/04-laporan-bug.md` (temuan & fix) · status per TC → `QA/03-test-case.md`

---

## 1. Ringkasan Eksekusi

| Round | Tanggal | Cakupan | Tests | Pass | Fail | Error | Keterangan |
|---|---|---|---|---|---|---|---|
| R1 | 2026-10-01 | Eksekusi awal (64 TC QA + 25 baseline) | 89 | 70 | 18 | 1 | 17 bug aplikasi + 1 defek test (TD-001) |
| R2 | 2026-10-02 | Setelah fix BUG-001…017 (kecuali 2 penyesuaian) | 89 | 87 | 2 | 0 | Sisa: TC-C06 (komentar view), TC-D07 (teknik test TD-002) |
| R3 | 2026-10-02 | Setelah fix sisa + penguatan test | 89 | **89** | 0 | 0 | Hijau penuh, exit code 0 |
| R4 | 2026-10-02 | Regression setelah fix BUG-018 (view `index.blade`) | 89 | **89** | 0 | 0 | Tidak ada regresi |
| M1 | 2026-10-02 | UAT manual E2E (TC-G01…G05) | 5 | **5** | 0 | 0 | 1 temuan (BUG-018) → fix → retest PASS |
| V1 | 2026-10-02 | Verifikasi balasan handoff dev (review 18 fix, nav hamburger, CI, build) | 89 | **89** | 0 | 0 | Dijalankan QA 2× (pra & pasca `npm run build`); 1 temuan (BUG-019) → fix → PASS |

**Angka pendukung:** R1 345 asersi → R3/R4 **388 asersi**, 0 failure. Baseline sebelum QA: 25/25 (61 asersi).
Perintah: `php artisan test --compact` dari `app/` (DB `dekatsini_testing`, `RefreshDatabase`).

---

## 2. Retest Bug Aplikasi (dari `04-laporan-bug.md`)

| ID | TC | R1 | Retest | Status |
|---|---|---|---|---|
| BUG-001 | TC-A07, TC-A08 | FAIL (500 duplikat kolom SQL) | R2 PASS | ✅ CLOSED |
| BUG-002 | TC-A13 | FAIL (harga tak tampil) | R2 PASS | ✅ CLOSED |
| BUG-003 | TC-A15 | FAIL (galeri tak tampil) | R2 PASS | ✅ CLOSED |
| BUG-004 | TC-A16 | FAIL (sort & 3+ hilang) | R2 PASS | ✅ CLOSED |
| BUG-005 | TC-B08 | FAIL (tanpa `tel:`) | R2 PASS | ✅ CLOSED |
| BUG-006 | TC-B09 | FAIL (tanpa field pesan) | R2 PASS | ✅ CLOSED |
| BUG-007 | TC-C04, TC-C05 | C05 FAIL; C04 lemah | R2 PASS (C04 diperkuat) | ✅ CLOSED |
| BUG-008 | TC-C06 | FAIL (copy EN) | R2 FAIL (komentar HTML EN) → **R3 PASS** | ✅ CLOSED |
| BUG-009 | TC-C07 | FAIL (tanpa tombol Google) | R2 PASS | ✅ CLOSED |
| BUG-010 | TC-C10 | FAIL (tanpa tombol Keluar) | R2 PASS | ✅ CLOSED |
| BUG-011 | TC-C11 | FAIL (500 tanpa konfigurasi) | R2 PASS | ✅ CLOSED |
| BUG-012 | TC-E10 | FAIL (pending → active lolos) | R2 PASS | ✅ CLOSED |
| BUG-013 | TC-D06 | FAIL (bukan WebP) | R2 PASS | ✅ CLOSED |
| BUG-014 | TC-D07 | FAIL (pesan error tak tampil) | R2 FAIL (TD-002) → **R3 PASS** | ✅ CLOSED |
| BUG-015 | TC-D08 | FAIL (old input hilang) | R2 PASS | ✅ CLOSED |
| BUG-016 | TC-D09 | FAIL (WA tak ternormalisasi) | R2 PASS | ✅ CLOSED |
| BUG-017 | TC-F06 | FAIL (lead non-aktif diterima) | R2 PASS | ✅ CLOSED |
| BUG-018 | TC-G05 | M1 FAIL (filter 390px tak terjangkau) | fix → **M1 PASS** + **R4 89/89** | ✅ CLOSED |
| BUG-019 | — (review) | n/a — temuan baru di V1 (aset build stale) | fix `npm run build` → **V1 PASS** (89/89 + verifikasi visual desktop & 390px) | ✅ CLOSED |

**Defek artefak test (bukan aplikasi):** TD-001 (TC-E08) → diperbaiki, R2+ PASS · TD-002 (TC-D07) → diperbaiki, R3+ PASS.

---

## 3. Hasil UAT Manual (sesi M1, 2026-10-02)

Lingkungan: `php artisan serve :8000` · DB dev `dekatsini` (12 listing aktif, 8 kategori) · browser desktop.

| TC | Hasil | Observasi |
|---|---|---|
| TC-G01 | **PASS** | Pencarian "Servis AC" dari landing → hasil dalam **345 ms** (< 1 s). Detail sesuai desain: judul, "Mulai Rp 150.000", ★4.8 + TERVERIFIKASI, tombol WA dgn template, peta Leaflet, Ulasan Pelanggan, Jasa Terkait, form Minta Penawaran. `QA/evidence/g01-detail-jasa.png` |
| TC-G02 | **PASS** | Klik WhatsApp membuka tab `wa.me` → `api.whatsapp.com/send?phone=6281234567890&text=Halo, saya lihat layanan Servis AC Cepat Jaksel di DekatSini…` (template benar) dan **lead terekam** — klik terkontrol membuktikan tepat **1 lead per klik** (3 → 4). |
| TC-G03 | **PASS** | Login Pak Hendra → Pasang Jasa → isi form, **pin peta** (-6.244998 / 106.838093 dari klik peta), upload foto → redirect dashboard dgn flash "Jasa berhasil dikirim, menunggu verifikasi admin". Verifikasi DB: `status=pending`, WA `081122334455` → **`6281122334455`** (ternormalisasi sesuai BR-06), foto terkonversi **`.webp`** (16 KB JPG → 4.3 KB WebP, cover). `QA/evidence/g03-dashboard-pending.png` |
| TC-G04 | **PASS** | Sebelum: Dilihat Hari Ini **1**, Kontak Baru **3** → 1× buka detail + 1× klik WA → sesudah: **2** dan **4** (akurat, tanpa duplikasi). `QA/evidence/g04-metrics-naik.png` |
| TC-G05 | **FAIL → PASS** | 390 px: kartu 1 kolom ✓, tetapi panel filter `display:none` tanpa toggle → **BUG-018**. Setelah fix: tombol "⚙ Buka Filter" tampil, panel terbuka penuh (Cari, 8 kategori, Rating Min, Urutkan, Radius, Terapkan) dan bisa ditutup; kartu tetap 1 kolom; ≥768 px tanpa perubahan. `QA/evidence/g05-before-filter-tersembunyi.png`, `QA/evidence/g05-after-filter-terjangkau.png` |

Catatan yang diamati (bukan bug — di luar syarat TC/PRD):
- Lihat `QA/06-laporan-kualitas.md` bagian Risiko — link nav (Beranda/Jelajah/Pasang Jasa) tersembunyi di bawah 768 px tanpa hamburger; direkomendasikan untuk iterasi berikutnya.
- Deduplikasi view per IP/jam bekerja sesuai desain (reload dalam jam yang sama tidak menambah hitungan).

---

## 4. Status Akhir

- **69/69 TC PASS** (64 otomatis + 5 manual) — status per baris di `QA/03-test-case.md`.
- **19/19 bug aplikasi CLOSED** (termasuk BUG-019 dari verifikasi handoff), 0 bug terbuka.
- **5/5 round otomatis hijau** (R3, R4, V1×2: 89/89, 388 asersi; V1 = verifikasi balasan handoff development).
- Data uji sesi M1 dibersihkan dari DB dev (listing "Instalasi CCTV Rumah (Uji QA E2E)", lead & view uji) — artefak bukti tetap di `QA/evidence/`.
- Detail hasil verifikasi atas balasan handoff dev (review fix, nav hamburger, CI, risiko) → `QA/06-laporan-kualitas.md` bagian 8.

➡ Lanjut ke **`QA/06-laporan-kualitas.md`** untuk quality gate.
