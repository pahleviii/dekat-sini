# Setup Opencode — DekatSini Laravel MVP

Dokumen ini untuk eksekusi `Setup: Opencode` + `Coding: Opencode`.
Jalankan berurutan di folder ini.

## 0. Prasyarat
- PHP 8.3+, Composer, MySQL 8, Node 20+, Git
- Akun Google OAuth (untuk Socialite)

## 1. Init Project (di `E:\Reza\Project\DekatSini`)
```powershell
composer create-project laravel/laravel app
cd app
composer require laravel/breeze laravel/socialite intervention/image
php artisan breeze:install blade
npm install; npm run build
```

Copy referensi (jangan coding dari nol):
- `../UI\ Design/*/code.html` -> jadikan Blade awal
- `../prd.md` §8, `../brd.md` sebagai sumber requirement

## 2. Env `.env`
```
DB_CONNECTION=mysql
DB_DATABASE=dekatsini
DB_USERNAME=root
DB_PASSWORD=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URL=http://localhost:8000/auth/google/callback
FILESYSTEM_DISK=public
```

```powershell
php artisan storage:link
php artisan migrate
```

## 3. Struktur Blade (wajib)
```
resources/views/layouts/app.blade.php (navbar + footer dari landing)
resources/views/home/index.blade.php <- UI Design/dekatsini_landing_page
resources/views/services/index.blade.php <- service_directory_listings
resources/views/services/show.blade.php <- service_detail_view
resources/views/auth/split.blade.php <- user_authentication_page
resources/views/services/create.blade.php <- add_new_service_form
resources/views/dashboard/index.blade.php <- provider_dashboard
```

Aturan: Tailwind CDN diganti Vite build, font Inter, primary `#2563EB`, semua copy EN -> ID.

## 4. Migration Urutan
1. `create_categories_table` + seeder 8 kategori
2. `create_services_table` (lihat prd.md §8.2, sertakan lat/lng + FULLTEXT)
3. `create_service_photos_table`
4. `create_reviews_table` (seed 2 review dummy untuk detail)
5. `create_service_views_table`
6. `create_leads_table`
7. `add_google_id_is_provider_to_users`

## 5. Verifikasi Coding Selesai
```powershell
php artisan test
php artisan serve
npm run dev
```
Checklist UAT (dari brd.md §9):
- [ ] `/services?search=AC&radius=5` <1s
- [ ] Klik WA log ke `leads`
- [ ] Submit jasa -> `pending`
- [ ] Dashboard angka benar

## 6. Deploy
- Sementara: Railway (`railway up`) atau Render PHP — JANGAN Vercel.
- Final: VPS (Nginx + PHP-FPM + MySQL) / Shared cPanel. `php artisan config:cache; route:cache; view:cache`.

## 7. Prompt Siap Tempel ke Opencode
```
Baca brd.md, prd.md §8, dan UI Design/*/code.html.
Scaffold Laravel 12 + Breeze + Socialite sesuai SETUP.md §3-4.
Prioritas: routes, migrations, Blade ID, Leaflet picker, WA leads logging.
Jangan tambah booking/payment (Phase 2).
```
