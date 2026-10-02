# 06 — Laporan Kualitas & Quality Gate: DekatSini MVP

**Versi:** 1.0 · **Tanggal:** 2026-10-02 · **Penulis:** QA Engineer · **Siklus:** lengkap (analisis → rencana → test case → eksekusi → bug → fix → retest → gate)

**Artefak:** `01-analisis-kebutuhan.md` · `02-rencana-pengujian.md` · `03-test-case.md` · `04-laporan-bug.md` · `05-hasil-retest.md` · `06-laporan-kualitas.md` (dokumen ini) · `evidence/` (5 tangkapan layar UAT)

---

## 1. Keputusan Quality Gate

| # | Kriteria | Target | Aktual | Status |
|---|---|---|---|---|
| G1 | Seluruh TC prioritas **P1** lulus (otomatis + manual) | 100 % | 100 % (semua P1 PASS) | ✅ |
| G2 | Pass rate suite otomatis akhir | 89/89 | **89/89** (R3 & R4), 388 asersi, exit 0 | ✅ |
| G3 | Bug **Critical/Major terbuka** | 0 | 0 (19/19 CLOSED, termasuk temuan V1) | ✅ |
| G4 | Tidak ada regresi baseline | 25/25 tetap lulus | 25/25 pada tiap round | ✅ |
| G5 | UAT manual alur utama (TC-G01…G05) | 5/5 PASS | 5/5 PASS (G05 setelah fix BUG-018) | ✅ |
| G6 | Artefak QA lengkap & test terisi hasil | 01–06 + 69 baris hasil | 01–06 ✓, 69/69 baris `PASS` | ✅ |

### 🟢 VERDICT: **PASS — MVP layak rilis/serah terima**

Semua kriteria terpenuyi; tidak ada residual Critical/Major. Risiko sisa bersifat minor dan terdokumentasi (bagian 5).

---

## 2. Cakupan Pengujian

| Modul | Sumber kebutuhan | TC | Otomatis | Manual | Status |
|---|---|---|---|---|---|
| A — Publik/Discovery | PRD search, filter, radius, sorting, detail | 16 | 16 | — | PASS |
| B — Lead/WhatsApp | FR kontak terarah, klik WA/tel/quote → leads | 9 | 9 | — | PASS |
| C — Auth | FR register/login, copy ID, Google OAuth, logout | 11 | 11 | — | PASS |
| D — Form Pasang Jasa | BR validasi, WebP, normalisasi WA, old input | 10 | 10 | — | PASS |
| E — Dashboard & Metrik | UAT#2, UAT#3 (self-activate), metrik akurat | 12 | 12 | — | PASS |
| F — Keamanan | OWASP-top: IDOR, XSS, CSRF, rate-limit, validasi status | 6 | 6 | — | PASS |
| G — Alur utama pengguna | UAT#1–#3 E2E + responsif 390 px | 5 | — | 5 | PASS |
| *(baseline aplikasi)* | Suite bawaan sebelum QA | 25 | 25 | — | PASS |
| **Total** | | **94 baris hasil** (69 QA + 25 baseline) | 89 | 5 | **Semua PASS** |

Eksplisit tercakup: 4 UAT utama (cari+radius, WA click→lead, listing→dashboard, self-activate blocked), seluruh BR/FR yang terukur pada `01-analisis-kebutuhan.md`, dan 6 kelas keamanan OWASP.

---

## 3. Metrik Eksekusi

| Round | Tanggal | Tests | Pass | Fail | Asersi | Durasi |
|---|---|---|---|---|---|---|
| R1 (eksekusi awal) | 2026-10-01 | 89 | 70 | 18 + 1 error | 345 | 8,1 dtk |
| R2 (retest fix) | 2026-10-02 | 89 | 87 | 2 | 389 | 8,3 dtk |
| R3 (retest final otomatis) | 2026-10-02 | 89 | **89** | 0 | 388 | 7,0 dtk |
| R4 (regresi setelah BUG-018) | 2026-10-02 | 89 | **89** | 0 | 388 | 6,1 dtk |
| M1 (UAT manual G) | 2026-10-02 | 5 | **5** | 0 | — | — |
| V1 (verifikasi handoff dev) | 2026-10-02 | 89 | **89** | 0 | 388 | 20,5 & 5,6 dtk |

- **Defect yield:** 19 bug / 69 TC = 28 % temuan akhir (17 R1 + 1 M1 + 1 V1) → **0 bug terbuka** di akhir (burndown 19 → 0).
- **Severitas:** Critical 4 · Major 12 · Minor 3 — semuanya FIXED & terverifikasi (R2/R3/R4/M1/V1).
- **Defek test (bukan aplikasi):** 2 (TD-001 signature API PHPUnit, TD-002 teknik flash-session) — diperbaiki di artefak test.

---

## 4. Kualitas Perbaikan

- Setiap fix diverifikasi minimal oleh TC asalnya; perubahan view controller/route berdampak luas diverifikasi ulang lewat **R4 (regression 89/89)** — terutama perubahan `index.blade.php` (BUG-018) dan `lang/id/*` (BUG-007/008/014).
- Fix memenuhi bentuk akhir yang diharapkan: bukan sekadar memenuhi asersi (mis. normalisasi WA diterapkan di `store` **dan** `update`; guard pending→active memakai `ValidationException` sehingga tampil sebagai pesan error di form, bukan 500).
- Konversi WebP diverifikasi end-to-end: file `.webp` nyata di storage (16 KB → 4,3 KB) pada unggahan E2E — bukan hanya baris DB.

---

## 5. Risiko Sisa & Observasi (di luar gate)

| # | Risiko/Observasi | Dampak | Rekomendasi |
|---|---|---|---|
| R1 | ~~Link nav tanpa hamburger di < 768 px~~ — **DITANGANI development** (menu ☰ Alpine di `layouts/app.blade.php`, `x-cloak` anti-flash, `aria-expanded`), diverifikasi QA di V1 (390px buka/tutup ✓, desktop tersembunyi ✓) | UX mobile teratasi | — (selesai) |
| R2 | Pengujian otomatis hanya pada satu engine (PHPUnit + HTTP-level) & UAT manual pada satu browser desktop; tidak ada uji lintas browser/OS/perangkat nyata | Risiko kompatibilitas kecil | Tambah smoke Safari/Firefox & device nyata sebelum rilis publik |
| R3 | Metrik performa resmi (LCP mobile < 2,5 dtk dari `01`) belum diukur dengan Lighthouse; pencarian teramati **345 ms** (server lokal) | Keyakinan performa parsial | Jalankan Lighthouse pada staging terpublikasi |
| R4 | Data uji E2E pada DB dev (listing "Instalasi CCTV Rumah (Uji QA E2E)", lead & view uji) — **sudah dibersihkan** pasca-sesi | Tidak ada (mitigasi selesai) | Jalankan ulang `db:seed` bila perlu kondisi awal |
| R5 | Suite butuh MySQL `dekatsini_testing` + `RefreshDatabase` → workflow CI **sudah dibuat development** (MySQL 8 service, PHP 8.4, npm build), **namun**: (a) repo git belum di-init di root manapun; (b) file di `app/.github/workflows/` vs `working-directory: app` mengasumsikan root repo berbeda — salah satu pasti salah (lihat bagian 8) | CI belum bisa jalan sampai root repo + lokasi `.github` disepakati | Development memilih root repo: `DekatSini/` → pindahkan `.github/` ke root; atau `app/` → hapus `working-directory: app` |

---

## 6. Bukti

| Bukti | Berkas |
|---|---|
| Detail jasa sesuai desain (TC-G01) | `QA/evidence/g01-detail-jasa.png` |
| Dashboard: flash sukses + status pending (TC-G03) | `QA/evidence/g03-dashboard-pending.png` |
| Metrik naik 1→2 & 3→4 (TC-G04) | `QA/evidence/g04-metrics-naik.png` |
| 390 px sebelum fix — filter tak terjangkau (BUG-018) | `QA/evidence/g05-before-filter-tersembunyi.png` |
| 390 px setelah fix — filter terbuka penuh (TC-G05) | `QA/evidence/g05-after-filter-terjangkau.png` |
| Nav hamburger mobile terbuka (V1, fix dev) | `QA/evidence/handoff-nav-mobile-terbuka.png` |
| Desktop bersih tanpa tombol mobile (V1, fix BUG-019) | `QA/evidence/handoff-desktop-tanpa-tombol-mobile.png` |
| Hasil mentah tiap round (JSON phpunit) | `app/qa_round1.txt` … `app/qa_round4.txt` |
| Suite otomatis | `app/tests/Feature/QA/*QaTest.php` (64 TC) |

---

## 7. Sign-off

| Peran | Pernyataan | Tanggal |
|---|---|---|
| QA Engineer | 69/69 TC QA PASS (64 otomatis + 5 manual), 19/19 bug CLOSED, kriteria gate G1–G6 terpenuhi (termasuk verifikasi handoff V1) | 2026-10-02 |
| — | **Gate: PASS — layak rilis MVP** (risiko sisa minor, terdokumentasi bagian 5 & 8) | 2026-10-02 |

---

## 8. Verifikasi Balasan Handoff Development (V1 — 2026-10-02)

QA memverifikasi **independen** atas balasan development (prinsip: tidak ada klaim diterima tanpa cek ulang):

| # | Klaim development | Verifikasi QA | Status |
|---|---|---|---|
| 1 | Review 18/18 fix — akar penyebab tertangani (dgn rujukan baris kode) | Konsisten dgn hasil verifikasi QA sendiri (R2–R4); tidak ada selisih penilaian | ✅ |
| 2 | Test 89/89 · 388 asersi · 3× run | Dijalankan ulang QA 2× (V1, pra & pasca build ulang): **89/89 PASS, 388 asersi** | ✅ |
| 3 | Nav hamburger Alpine (`layouts/app.blade.php`) | Review kode: Alpine sudah di `app.js` + ter-build, `[x-cloak]` inline anti-flash, `:aria-expanded`, form logout `@csrf`, cabang `@auth` lengkap. E2E 390px: panel buka/tutup ✓, ikon ☰→✕ ✓, link lengkap ✓; desktop ≥768: tersembunyi ✓. Catatan: interaksi QA awal sempat "gagal" — murni race sebelum Alpine init + salah selector, **bukan defect** | ✅ |
| 4 | (Temuan QA saat verifikasi) aset build CSS stale | `md:hidden` hilang dari CSS → tombol ☰ & ⚙ tampil di desktop (**BUG-019**) → `npm run build` → retest visual desktop & 390px + 89/89 | ✅ CLOSED |
| 5 | CI `app/.github/workflows/tests.yml` | **Isi workflow benar**: MySQL 8 + healthcheck, PHP 8.4 (gd/pdo_mysql), `npm ci` + `npm run build`, env `DB_PASSWORD=root` menang atas `<env>` phpunit (force=false) & sinkron dgn `MYSQL_ROOT_PASSWORD`, lockfiles ada. **Masalah lokasi:** repo git belum ada di root manapun, DAN `.github` di dalam `app/` vs `working-directory: app` mengasumsikan root repo berbeda — salah satu pasti salah (lihat R5) | ⚠️ PERBAIKAN LOKASI |
| 6 | Data demo sudah bersih (12 jasa / 0 lead / 0 view) | Sesuai hasil cleanup QA sendiri | ✅ |
| 7 | Lighthouse LCP & cross-browser ditunda | Disetujui — tetap prasyarat rilis publik (R2, R3) | ⏳ sesuai rencana |

**Kesimpulan V1:** tidak ada veto — 1 temuan baru (BUG-019) sudah diperbaiki dan ditutup. Satu-satunya sisa aksi development: **keputusan root repo agar lokasi workflow CI valid** (di luar gate aplikasi).
