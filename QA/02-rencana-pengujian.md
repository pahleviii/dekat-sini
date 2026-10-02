# 02 — Rencana Pengujian (Test Plan): DekatSini MVP

**Versi:** 1.0 · **Tanggal:** 2026-10-01 · **QA:** OpenCode QA Agent
**Siklus:** Full cycle — analisis → test case → eksekusi → bug report → fix → retest → quality gate.

---

## 1. Objektif
1. Memverifikasi seluruh FR-01…FR-10 dan 4 kriteria UAT (`brd.md` §9).
2. Menemukan, melaporkan, memperbaiki, dan me-retest bug hingga **semua test lulus**.
3. Menetapkan quality gate sebelum rilis MVP.

## 2. Strategi & Jenis Pengujian
| Jenis | Cakupan | Teknik |
|---|---|---|
| Smoke | 6 halaman utama respons 200 | HTTP GET (automated) |
| Functional (FR) | Search/filter/sort, detail, form listing, dashboard, lead, auth | Feature test PHPUnit + verifikasi DOM |
| Negative/Security | IDOR, XSS, validasi input, rate limit, akses tanpa auth | Feature test |
| Data integrity | View unik/IP-jam, metrik dashboard, status `pending` | Feature test + assertion DB |
| Lokalisasi | Copy ID di halaman auth & UI utama | Assert teks (`assertSee`/`assertDontSee`) |
| Performa | Pencarian < 1 s (UAT#1) | Pengukuran durasi request dalam test |
| E2E manual | Alur utama Budi (cari → WA) & Pak Hendra (listing → dashboard) | `php artisan serve` + browser |

## 3. Lingkungan Pengujian
| Item | Nilai |
|---|---|
| OS / PHP | Windows · PHP 8.4.1 (GD + pdo_mysql aktif) |
| Framework | Laravel 13.34 · Breeze · Socialite |
| DB otomatis | MySQL 8 `dekatsini_testing` (RefreshDatabase, phpunit.xml) |
| DB manual/e2e | MySQL 8 `dekatsini` (12 listing seeder, 8 kategori) |
| Frontend | Vite build (`public/build` sudah ada) |
| Server manual | `php artisan serve` → http://localhost:8000 |
| Akun uji | `provider@dekatsini.id` / `password`, `budi@dekatsini.id` / `password` |

## 4. Entry & Exit Criteria
**Entry:** migrasi & seeder sukses; `php artisan test` baseline hijau (25 test lulus); build Vite ada.
**Exit (quality gate):**
- [ ] 100% test suite lulus, termasuk seluruh test QA baru.
- [ ] 0 bug Critical/Major terbuka; seluruh Minor punya disposition (fixed/wontfix + alasan).
- [ ] 4 kriteria UAT terbukti lulus.
- [ ] Tidak ada teks EN tersisa di halaman utama (auth, layout, form).

## 5. Prioritas & Severity
| Prioritas test | Keterangan |
|---|---|
| P1 | UAT, alur konversi WA, keamanan (IDOR/XSS/auth), integritas metrik |
| P2 | Filter/sort, form listing, validasi, lokalisasi |
| P3 | Cosmetik, edge case |

| Severity | Definisi | SLA retest |
|---|---|---|
| Critical | Kerugian data/fundamental (keamanan, metrik salah total) | Sprint ini |
| Major | Fitur utama tidak fungsional / melanggar UAT | Sprint ini |
| Minor | Batas tepi, UX terganggu tapi ada jalan alternatif | Backlog terjadwal |
| Cosmetic | Tidak mempengaruhi fungsi | Backlog |

## 6. Risiko & Mitigasi
| Risiko | Mitigasi |
|---|---|
| Geolocation tidak bisa dipakai di test headless | Uji kontrak server (param lat/lng) + keberadaan kontrol UI |
| Flaky akibat rate limit lead 30/menit | Test rate limit pakai test case terpisah (aplikasi fresh per test) |
| Perubahan fix memicu regresi | Seluruh suite dijalankan ulang setelah tiap fix (regression = retest) |
| Waktu test besar | Prioritas P1 → P2 → P3; automated dulu, E2E manual terakhir |

## 7. Artefak
| Artefak | File |
|---|---|
| Analisis kebutuhan | `QA/01-analisis-kebutuhan.md` |
| Test plan (dokumen ini) | `QA/02-rencana-pengujian.md` |
| Test case | `QA/03-test-case.md` |
| Test otomatis | `app/tests/Feature/QA/*.php` |
| Laporan bug | `QA/04-laporan-bug.md` |
| Hasil retest | `QA/05-hasil-retest.md` |
| Laporan kualitas akhir | `QA/06-laporan-kualitas.md` |

## 8. Jadwal Siklus
| Tahap | Keluaran |
|---|---|
| 1. Analisis kebutuhan | Dok 01 |
| 2. Rencana + test case | Dok 02, 03 |
| 3. Eksekusi Round 1 (automated + manual) | Hasil gagal/lulus |
| 4. Bug report | Dok 04 |
| 5. Fix | Patch kode |
| 6. Retest + regression | Dok 05 |
| 7. Quality gate | Dok 06 + sign-off |
