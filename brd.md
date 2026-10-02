# Business Requirements Document (BRD): DekatSini

**Version:** 1.0.0
**Date:** 2026-10-01
**Owner:** Reza / DekatSini Team
**Status:** Approved MVP

---

## 1. Latar Belakang
UMKM tukang / jasa lokal (AC, listrik, cleaning, plumbing) sulit ditemukan di luar lingkaran personal. Konsumen mengandalkan WOM / iklan tidak terverifikasi. DekatSini menjadi direktori hyperlocal terverifikasi dengan CTA WhatsApp.

## 2. Tujuan Bisnis
1. MVP 4-6 minggu: 100 listing awal Jabodetabek, 500 WA clicks / bulan.
2. Validasi supply-demand tanpa payment gateway.
3. Siap monetisasi Phase 2: Featured Listing + Komisi booking.

## 3. Scope MVP (Disetujui)
**IN:**
- Landing `/` (ID), Direktori `/services` + filter kategori, rating, radius 0-20km, sort.
- Detail `/services/:id`: badge verified, galeri, review read-only, CTA WA + Telepon + Request Quote (modal, simpan sebagai lead).
- Auth `/auth`: Google OAuth (Socialite) + Email/Password, single role fleksibel (`users.is_provider` boolean).
- Form `/services/new`: nama, kategori, deskripsi, WA, alamat + kota + lat/lng (Leaflet picker), upload foto.
- Dashboard `/dashboard`: Total Services, Views Today, New Contacts (WA clicks), tabel Active/Pending, edit/delete.
- Bahasa: `id-ID` primary. EN nanti.

**OUT (Phase 2/3):**
- Kalender booking, payment QRIS/GoPay/OVO, submit review + foto, aplikasi mobile, tracking real-time.

## 4. Stakeholder & Persona
- Budi (32, homeowner Jaksel): cari tukang same-day, butuh jarak + rating + WA 1-klik.
- Pak Hendra (45, tukang AC): butuh onboarding mudah, portofolio foto, metrik views/contacts.

## 5. Model Bisnis MVP
- Gratis listing + verifikasi manual (WA admin). Pendapatan belum di MVP.
- Phase 2: Featured Rp 50-150rb/bulan, komisi booking 5-10%.

## 6. KPI
| Metrik | Target MVP | Sumber |
|---|---|---|
| Listing aktif | >=100 | `services.status=active` |
| WA clicks | >=500/bln | `leads.channel=whatsapp` |
| Views/listing/hari | >=10 | `service_views` |
| LCP mobile | <2.5s | Lighthouse |

## 7. Asumsi & Ketergantungan
- Stack: Laravel 12 + MySQL 8 + Blade + Tailwind + Alpine.js + Leaflet/OSM (Nominatim).
- Verifikasi manual via admin (tanpa KTP otomatis di MVP).
- Hosting sementara: Railway/Render/VPS (bukan Vercel — Vercel tidak support Laravel PHP).
- Upload: `storage/app/public`, konversi WebP, max 2MB/foto.

## 8. Risiko
| Risiko | Mitigasi |
|---|---|
| Spam listing | Status `pending` default, approve admin |
| Data lokasi tidak akurat | Wajib pin Leaflet + kota, validasi lat/lng |
| Beban OSM rate-limit | Cache geocode, picker client-side |
| Campuran bahasa ID/EN di desain | Unifikasi copy ID di Blade |

## 9. Kriteria Penerimaan (UAT)
1. Cari "AC" -> hasil <1s, filter radius bekerja.
2. Klik WA -> template `Halo, saya lihat [Jasa] di DekatSini...` + tercatat di dashboard provider.
3. Provider tambah jasa -> status Pending, muncul setelah approve.
4. Dashboard angka Views Today & New Contacts akurat.

---
*Referensi: `prd.md` v1.1, `UI Design/` 6 screens.*
