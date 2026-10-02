# Deploy DekatSini

> Laravel TIDAK bisa ke Vercel. Pilih salah satu di bawah.

## A. Sementara (pilih 1)

### Railway (recommended sementara)
1. Push folder `app/` ke GitHub.
2. Railway → New Project → Deploy from GitHub.
3. Variables: `DB_CONNECTION=mysql`, `MYSQL_URL` (dari plugin MySQL Railway), `APP_KEY` (copy dari `.env`), `APP_URL=https://xxx.up.railway.app`, `GOOGLE_*`.
4. Start command: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT` (sudah di `Procfile`).
5. Tambah volume untuk `storage/app/public` jika ingin foto permanen.

### Render
- New → Web Service → Docker (pakai `Dockerfile`).
- Env sama seperti Railway. Disk: mount `/var/www/html/storage/app/public`.

## B. Final VPS / Shared (Indonesia)
- VPS: Nginx + PHP-FPM 8.4 + MySQL 8. Clone repo, `composer install`, `npm run build`, `php artisan migrate`, `php artisan config:cache route:cache view:cache`.
- Shared cPanel: upload `public/` ke `public_html`, sisanya di luar root, sesuaikan `index.php` paths. Cron untuk queue tidak perlu di MVP.

## Checklist pre-deploy
```
npm run build
php artisan test
php artisan config:cache route:cache view:cache
```
Seed awal di server: `php artisan db:seed --force` (12 jasa dummy, aman dihapus manual).
