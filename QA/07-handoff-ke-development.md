# PROMPT HANDOFF QA → DEVELOPMENT — DekatSini MVP

Anda menerima hasil crosscheck / QA cycle lengkap proyek **DekatSini MVP** (Laravel). Baca seluruh dokumen ini, lalu:

1. **Review setiap perbaikan** pada kolom "File" — pastikan akar penyebab benar-benar tertangani (bukan sekadar memenuhi test).
2. **Jalankan ulang test:** `php artisan test --compact` dari folder `app/` (butuh MySQL DB `dekatsini_testing`) — wajib **89/89 lulus**.
3. **Tangani bagian "Sisa Tindakan"** — poin yang belum dikerjakan QA.
4. Jika ada temuan yang tidak Anda setujui, batalkan dengan rujukan **ID test case (TC-)**-nya, bukan opini.

---

## 1. Konteks & Hasil Gate

- Siklus: analisis kebutuhan → rencana → test case (**69 TC**: 64 otomatis + 5 manual UAT) → eksekusi → bug → fix → **4 round retest** → UAT manual → quality gate.
- **Quality gate: 🟢 PASS — MVP layak rilis.**
- Test otomatis akhir: **89/89 PASS** (64 TC QA + 25 baseline), **388 asersi**, exit code 0 (Round 3 & Round 4/regression).
- UAT manual TC-G01…G05: **5/5 PASS**.
- Total temuan: **18 bug aplikasi** (Critical 4 / Major 12 / Minor 2) + 2 defek artefak test pada siklus awal. Status: **18/18 CLOSED, 0 terbuka** — ditambah **BUG-019** (temuan verifikasi handoff, Minor) → kini **19/19 CLOSED** (lihat bagian 6).

---

## 2. Tabel Temuan, Akar Penyebab & Perbaikan

| ID | Sev | TC | Temuan & akar penyebab | File yang diubah | Status |
|---|---|---|---|---|---|
| BUG-001 | Critical | TC-A07, A08 | Filter radius → HTTP 500 (`Duplicate column name 'id'`): `selectRaw("services.*, … AS distance")` menambah `services.*` dobel di atas `withAvg()` → subquery pagination jadi derived table dgn kolom ganda | `app/Http/Controllers/ServiceController.php` | ✅ FIXED |
| BUG-002 | Major | TC-A13 | Harga tidak tampil di halaman detail — view tidak me-render `price_from` | `resources/views/services/show.blade.php` | ✅ FIXED |
| BUG-003 | Major | TC-A15 | Galeri foto tidak dirender — relasi `photos` dimuat tapi tak ada blok galeri | `resources/views/services/show.blade.php` | ✅ FIXED |
| BUG-004 | Major | TC-A16 | Filter UI tak lengkap: tidak ada dropdown **Urutkan** (backend sudah dukung `?sort=`) dan opsi rating **3+** | `resources/views/services/index.blade.php` | ✅ FIXED |
| BUG-005 | Major | TC-B08 | Tombol "Hubungi" hanya `<button onclick="logLead">` tanpa `href="tel:…"` → tak membuka dial telepon | `resources/views/services/show.blade.php` | ✅ FIXED |
| BUG-006 | Major | TC-B09 | Form "Minta Penawaran" tanpa field pesan; JS `logLead` tidak mengirim `message` → kebutuhan pengguna tak sampai ke penyedia | `resources/views/services/show.blade.php` | ✅ FIXED |
| BUG-007 | Major | TC-C04, C05 | `APP_LOCALE=id` & fallback `id`, tapi **tidak ada `lang/id/*`** → copy login Inggris & pesan error kunci mentah (`auth.failed`) | **Baru:** `lang/id.json`, `lang/id/auth.php`, `lang/id/passwords.php`, `lang/id/validation.php` (lengkap, dgn peta `attributes` ramah-Indonesia) | ✅ FIXED |
| BUG-008 | Major | TC-C06 | Copy register Inggris; komentar HTML view (`<!-- Confirm Password -->` dsb.) masih bahasa Inggris | `resources/views/auth/register.blade.php`, `resources/views/auth/login.blade.php` (komentar → ID) + lang (bareng BUG-007) | ✅ FIXED |
| BUG-009 | Major | TC-C07 | Tombol login Google (PRD FR-10) tidak ada di halaman login & register | `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php` | ✅ FIXED |
| BUG-010 | Major | TC-C10 | Navbar tanpa tombol/form **Keluar** — pengguna login tak bisa logout dari halaman publik | `resources/views/layouts/app.blade.php` | ✅ FIXED |
| BUG-011 | Critical | TC-C11 | `GET /auth/google` → **500** saat konfigurasi kosong: `config/services.php` tanpa blok `google` + controller tanpa guard | `config/services.php`, `app/Http/Controllers/AuthGoogleController.php` (guard `configured()` → redirect `/login` + pesan) | ✅ FIXED |
| BUG-012 | Critical | TC-E10 | Listing **pending bisa diaktifkan sendiri** (`PATCH status=active`) — pelanggaran UAT#3/BR-01; `update()` tanpa guard transisi status | `app/Http/Controllers/ServiceController.php` (tolak `pending→active` via `ValidationException`) | ✅ FIXED |
| BUG-013 | Minor | TC-D06 | Foto tak dikonversi **WebP** — kode memakai API Intervention **v3** (`read()`, `toWebp()`) padahal terpasang **v4** (`decode()`, `encode(new WebpEncoder())`) → `Throwable` diam-diam jatuh ke fallback | `app/Http/Controllers/ServiceController.php` | ✅ FIXED |
| BUG-014 | Major | TC-D07 | Pesan error validasi tidak tampil di form + kunci mentah `validation.*` | `resources/views/services/create.blade.php` & `edit.blade.php` (blok `$errors->any()`) + `lang/id/validation.php` | ✅ FIXED |
| BUG-015 | Major | TC-D08 | **Old input hilang** setelah submit gagal — seluruh input form tanpa `old()` | `resources/views/services/create.blade.php` | ✅ FIXED |
| BUG-016 | Major | TC-D09 | WA `08xx`/`+62` tak dinormalisasi → `wa.me/08…` tak valid (BR-06) | `app/Http/Controllers/ServiceController.php` (helper `normalizeWhatsapp()` di `store` **&** `update`) | ✅ FIXED |
| BUG-017 | Critical | TC-F06 | `POST /leads` diterima utk listing **pending/paused** — `exists:services,id` tanpa filter status | `app/Http/Controllers/LeadController.php` (`Rule::exists(...)->where('status','active')`) | ✅ FIXED |
| BUG-018 | Major | TC-G05 | Responsif 390px: panel filter `hidden md:block` **tanpa toggle** → filter tak terjangkau di mobile (syarat TC-G05) | `resources/views/services/index.blade.php` (tombol "⚙ Buka Filter" `md:hidden`, container `flex-col md:flex-row`, aside `w-full md:w-64`) | ✅ FIXED |

**Defek artefak test (bukan bug aplikasi, sudah diperbaiki di test):** TD-001 (TC-E08: signature `assertDatabaseMissing`), TD-002 (TC-D07: teknik flash-session `assertSessionHasErrors` antar-request → alur direwrite dgn `followingRedirects`).

---

## 3. Daftar File Berubah (cek list review)

**Kontroler & config:** `ServiceController.php` · `LeadController.php` · `AuthGoogleController.php` · `config/services.php`
**Baru (i18n):** `lang/id.json` · `lang/id/auth.php` · `lang/id/passwords.php` · `lang/id/validation.php`
**View:** `services/show.blade.php` · `services/index.blade.php` · `services/create.blade.php` · `services/edit.blade.php` · `layouts/app.blade.php` · `auth/login.blade.php` · `auth/register.blade.php`
**Test:** `tests/Feature/QA/` (6 file `*QaTest.php` + `BuildsQAData.php`); TC-C04 diperkuat (tolak kunci translasi mentah), TC-E08 & TC-D07 diperbaiki (defek test).

> Catatan perilaku: guard `pending→active` memakai `ValidationException` supaya tampil sebagai pesan error di form edit, **bukan** 500. Konversi WebP memakai API v4; fallback `store()` tetap ada bila pemrosesan gambar gagal.
>
> **Konvensi path (menanggapi koreksi review):** seluruh path dalam dokumen QA relatif terhadap **root Laravel** — `lang/id.json` = `app/lang/id.json` dari root workspace. File-nya sama; hanya beda titik awal path.

---

## 4. Bukti Retest

| Bukti | Lokasi |
|---|---|
| Round 1 → 4 (JSON phpunit mentah) | `app/qa_round1.txt` … `app/qa_round4.txt` |
| Screenshot UAT (detail, dashboard pending, metrik naik, 390px before/after) | `QA/evidence/*.png` |
| Laporan bug lengkap (akar penyebab + perbaikan per bug) | `QA/04-laporan-bug.md` |
| Hasil retest per round & per bug | `QA/05-hasil-retest.md` |
| Quality gate + risiko sisa | `QA/06-laporan-kualitas.md` |
| Test case & status per TC (69 baris `PASS`) | `QA/03-test-case.md` |

Perintah verifikasi: dari folder `app/` → `php artisan test --compact` (target 89/89).

---

## 5. Sisa Tindakan (belum dikerjakan QA — untuk Development)

| # | Poin | Status (setelah balasan dev + V1) |
|---|---|---|
| 1 | **Nav mobile:** link `hidden md:flex` tanpa hamburger di < 768px | ✅ **Dikerjakan dev** — hamburger Alpine di `layouts/app.blade.php` (`x-cloak` anti-flash, `aria-expanded`, cabang `@auth` + form logout `@csrf`); diverifikasi QA E2E di 390px & desktop (lihat bagian 6) |
| 2 | **Performa:** Lighthouse LCP mobile < 2,5 dtk | ⏳ Ditunda — butuh staging terpublikasi (prasyarat rilis publik) |
| 3 | **Cross-browser:** smoke test Safari/Firefox + device nyata | ⏳ Ditunda — prasyarat rilis publik |
| 4 | **CI:** MySQL service agar `php artisan test` jalan otomatis | ⚠️ Sebagian — workflow dibuat dev, **tapi lokasi `.github` vs `working-directory` tidak konsisten** + repo git belum di-init (lihat bagian 6) |
| 5 | **Review data demo:** 12 listing / 0 lead di DB dev | ✅ Bersih — diverifikasi QA (12 jasa / 0 lead / 0 view); `db:seed` hanya bila perlu kondisi awal |

**Gate: PASS.** 19/19 bug CLOSED (18 awal + BUG-019 temuan verifikasi), 0 Critical/Major terbuka — poin 2–4 di atas bersifat minor/di luar gate.

---

## 6. Balasan Development & Verifikasi QA (V1 — 2026-10-02)

**Balasan dev:** review 18/18 fix (dgn rujukan baris, 1 koreksi notasi path), test 89/89 3× run, Sisa Tindakan: nav ✅, CI ✅, data ✅, Lighthouse & cross-browser ⏳.

**Verifikasi QA (independen, tanpa menerima klaim apa pun tanpa cek):**

| Klaim | Hasil verifikasi | Status |
|---|---|---|
| Review fix 18/18 akar penyebab tertangani | Konsisten dgn verifikasi QA R2–R4 | ✅ |
| Test 89/89 · 388 asersi | QA jalankan ulang 2× (pra & pasca build ulang) → **89/89 PASS, 388 asersi** | ✅ |
| Nav hamburger Alpine | Review kode ✓ + E2E 390px: panel buka/tutup, ikon ☰→✕, link lengkap; desktop: tersembunyi. (Kegagalan interaksi awal = race sebelum init Alpine + salah selector QA — bukan defect) | ✅ |
| **Temuan baru BUG-019** (QA): aset build CSS stale — `md:hidden` hilang → tombol ☰ & ⚙ tampil di desktop | Fix: `npm run build` (CSS `app-CBzIKGpp.css`); retest visual desktop + 390px + 89/89 | ✅ CLOSED |
| CI `app/.github/workflows/tests.yml` | Isi ✓ (MySQL 8 + health, PHP 8.4, `npm ci`+build, env sinkron dgn phpunit.xml, lockfiles ada). **Masalah:** repo git belum ada; `.github` di dalam `app/` vs `working-directory: app` asumsi root repo beda — pilih salah satu: root `DekatSini/` → pindah `.github/` ke root; root `app/` → hapus `working-directory: app` | ⚠️ sisa aksi |
| Data demo bersih (12/0/0) | Sesuai cleanup QA | ✅ |
| Lighthouse & cross-browser ditunda | Disetujui — tetap prasyarat rilis publik | ⏳ |

**Kesimpulan V1: tidak ada veto.** Detail lengkap: `QA/06-laporan-kualitas.md` bagian 8. Bukti screenshot: `QA/evidence/handoff-*.png`.
