# PROMPT SISA PERBAIKAN QA → DEVELOPMENT (Pasca-Verifikasi V1) — DekatSini MVP

> Salin-tempel prompt di bawah ini ke development. Path dalam dokumen QA relatif ke **root Laravel** (folder `app/`).

---

## PROMPT — UNTUK DEVELOPMENT

**Konteks (2026-10-02, Verifikasi V1 oleh QA):** Gate tetap **PASS — 19/19 bug CLOSED**, suite **89/89 · 388 asersi** (QA jalankan ulang 2×). Review 18 fix Anda diterima, nav hamburger lolos E2E 390px + desktop, data demo bersih. BUG-019 (aset build CSS stale — `md:hidden` hilang → tombol ☰ & ⚙ tampil di desktop) **sudah QA perbaiki** dengan `npm run build` (CSS baru `app-CBzIKGpp.css`) — build lokal sudah sinkron, tidak perlu diulang.

Sisa perbaikan Anda ada di **bagian A (WAJIB sebelum push pertama)** dan **bagian B (prasyarat rilis)**.

> **STATUS EKSEKUSI (2026-10-02, diverifikasi QA — V2):**
> - **A1 ✅ SELESAI** — Opsi A dipilih: repo root = workspace (`pahleviii/dekat-sini`), `.github` dipindah ke root, `app/.github` dihapus, `working-directory: app` konsisten, push bersih (`main` == `origin/main`).
> - **A2 ✅ SELESAI** — step `Setup environment (.env + APP_KEY)` ada persis seperti rekomendasi dan **sukses di CI**.
> - **A3 ✅ SELESAI** — run pertama `44f52dc` gagal (locale `en` → TC-C04/C05/C06/D07), dev memperbaiki dgn mengunci `APP_LOCALE=id` di `phpunit.xml` + menyelaraskan `.env.example` → **run #2 `442b41f` SUCCESS (58s, 12/12 step hijau)**. QA rerun lokal dgn phpunit.xml baru: **89/89 · 388 asersi**. Bukti: `QA/evidence/ci-run-success.png`.
> - **B1 ✅ SELESAI (2026-10-02)** — Lighthouse mobile 3 halaman: home 2656→**1518 ms**, services 3314→**1515 ms**, detail 2908→**1653 ms** (skor 84/83/90 → **99/99/99**). Temuan **BUG-020** (render-blocking + font) diperbaiki QA; regresi 89/89; bukti `QA/evidence/lighthouse-*-before/after.json`.
> - **B2 ⚠️ SEBAGIAN** — smoke otomatis Playwright **60/60 PASS** (Chromium/Firefox/WebKit × 1280px/390px, 0 error JS; bukti `QA/evidence/crossbrowser-smoke-results.json`). **Sisa manual:** Safari resmi (butuh Mac/device iOS) + device fisik nyata — ceklis di `QA/06` bagian 9.

---

### A. WAJIB — Workflow CI Anda belum akan hijau (3 masalah)

#### A1. Lokasi `.github` tidak konsisten + repo git belum dibuat

Saat ini file ada di `app/.github/workflows/tests.yml` tetapi memakai `working-directory: app` — dua asumsi root repo yang berbeda, jadi **salah satu pasti salah**; ditambah repo git belum ada di root mana pun (GitHub bahkan tidak akan menampilkan workflow ini di tab Actions selama ia berada di dalam `app/`).

**Pilih salah satu (Opsi A direkomendasikan — paling sedikit perubahan):**

**Opsi A — root repo = `DekatSini/` (workflow tetap pakai `working-directory: app`):**
```powershell
cd E:\Reza\Project\DekatSini
git init
Move-Item app\.github .\.github      # pindahkan .github ke root workspace
git add .
git commit -m "chore: init repo + CI workflow"
git remote add origin <URL_REPO>
git push -u origin main
```
> Catatan: `app/.gitignore` tetap berlaku untuk subtree `app/` (vendor, node_modules, public/build, .env tetap ter-ignore). Bila folder `QA/` tidak ingin masuk repo, tambahkan baris `QA/` ke `.gitignore` root sebelum `git add`.

**Opsi B — root repo = `app/` (hapus `working-directory: app`):**
```powershell
cd E:\Reza\Project\DekatSini\app
git init
# Edit .github/workflows/tests.yml: hapus KEDUA baris `working-directory: app`
# (step "Install dependencies" dan step "Run tests")
git add .
git commit -m "chore: init repo + CI workflow"
git remote add origin <URL_REPO>
git push -u origin main
```

**Kriteria diterima (A1):** file workflow muncul & terbaca GitHub (terlihat di tab Actions repo) — bukan hanya ada di lokal.

#### A2. Workflow saat ini PASTI gagal di langkah test: `APP_KEY` belum tersedia

**Bukti:** `phpunit.xml` tidak menyetel `APP_KEY`; file `.env` tidak di-commit (ter-ignore); `.env.example` berisi `APP_KEY=` (kosong). Di checkout CI yang segar, `php artisan test` akan error di semua test HTTP/session: *"No application encryption key has been specified"* (akar masalah sama dengan pola BUG-007 — konfigurasi hilang, bukan kode test).

**Fix:** tambahkan step berikut **sebelum** step `Run tests (MySQL dekatsini_testing)` di `.github/workflows/tests.yml`:

```yaml
      - name: Setup environment (.env + APP_KEY)
        working-directory: app
        run: |
          cp .env.example .env
          php artisan key:generate
```

**Kriteria diterima (A2):** step `Run tests` mulai berjalan (bukan berhenti di MissingAppKeyException).

#### A3. Verifikasi akhir CI

```powershell
# setelah push:
# buka repo GitHub → tab Actions → job "laravel-tests" harus HIJAU
# ekspektasi keluaran step Run tests: 89 passed, 388 assertions
```

**Kriteria diterima (A3):** run pertama hijau dengan **89 passed**; kirimkan screenshot/log run tersebut ke QA untuk arsip.

---

### B. PRASYARAT RILIS PUBLIK (ditunda bersama, setujui QA)

1. **Lighthouse LCP mobile < 2,5 dtk** — jalankan di staging yang terpublikasi (target ada di `QA/01-analisis`; lokal tidak representatif). Pencarian teramati lokal: 345 ms.
2. **Cross-browser + device nyata** — smoke test Safari & Firefox + satu perangkat nyata (UAT QA baru 1 browser desktop + emulasi 390px).

**Kriteria diterima:** skor Lighthouse mobile LCP < 2,5 dtk tercatat, dan tidak ada defect blocker dari smoke cross-browser.

---

### C. Aturan pencegahan (akar BUG-019 — jangan sampai terulang)

1. **`public/build` di-gitignore** → CI aman (selalu `npm run build` segar). Risiko hanya **lokal/staging yang disajikan tanpa build ulang**.
2. Setiap mengubah **view / kelas Tailwind** (mis. menambah `md:hidden` baru), jalankan `npm run build` sebelum menyajikan halaman secara lokal — Tailwind JIT mem-purge kelas yang belum ada saat build, sehingga CSS basi menyembunyikan/menampilkan elemen secara keliru.
3. Cara cepat memastikan build sinkron: CSS hasil build harus mengandung kelas yang baru ditambahkan, contoh:
   ```powershell
   Select-String -Path public\build\assets\*.css -Pattern 'md\:hidden' -SimpleMatch
   ```

---

### D. Verifikasi akhir sebelum QA tutup siklus ini

Dari folder `app/`:
```powershell
php artisan test --compact        # target: 89/89 PASS, 388 asersi
npm run build                     # bila ada perubahan view/kelas sejak build terakhir
```
Lalu smoke dua titik: **desktop ≥768px** (tanpa tombol ☰ / ⚙) dan **390px** (hamburger buka-tutup + filter terjangkau).

---

**Status gate saat ini: PASS (19/19 CLOSED, 0 Critical/Major terbuka).** Bagian A adalah perbaikan proses/infra CI — tidak memblokir gate aplikasi, tetapi **wajib selesai sebelum push pertama** agar CI memang berfungsi. QA siap memverifikasi ulang hasil run Actions pertama (A3) begitu Anda kirimkan.

---

## Catatan internal QA (arsip)

- Temuan A2 ditemukan QA saat membaca ulang workflow + `phpunit.xml` + `.env.example` pada 2026-10-02 (belum ada di `04-laporan-bug.md` karena bukan bug aplikasi — tercatat di sini dan di `06` bagian 8 sebagai kondisi CI).
- Dokumen terkait: `QA/06-laporan-kualitas.md` (bagian 8), `QA/07-handoff-ke-development.md` (bagian 6), `QA/04-laporan-bug.md` (BUG-019).
