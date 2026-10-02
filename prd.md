# Product Requirements Document (PRD) & Project Brief: DekatSini

**Document Version:** 1.1.0 (Laravel MVP Revision)
**Product Name:** DekatSini (Dekat Sini — *"Right Around Here"*)
**Product Type:** Hyperlocal Service Marketplace & Directory Web Platform
**Target Launch Device:** Web Desktop & Responsive Mobile
**Primary Brand Color:** `#2563EB` (Royal Blue)
**Status:** BRD Approved / Ready for Engineering (Laravel)
**Stack:** Laravel 12 + MySQL 8 + Blade + Tailwind + Alpine.js + Leaflet/OSM  

---

## 1. Executive Summary & Vision

**DekatSini** is an on-demand, hyperlocal discovery platform connecting residential and commercial consumers with verified local service professionals (electricians, AC technicians, cleaning staff, handyman services, freelancers, etc.) in Indonesia and Southeast Asian urban neighborhoods.

### Problem Statement
- **Consumers:** Finding reliable, vetted, and transparently priced trade professionals (tukang, service AC, montir) usually depends on fragmented word-of-mouth or unverified classified ads with inconsistent pricing and unknown track records.
- **Service Providers / Freelancers:** Independent tradespeople and micro-agencies struggle to reach customers outside their immediate personal circle and lack digital tools to showcase past work, accept reviews, or track listing performance.

### Product Vision
Become the neighborhood default platform for quick, reliable home and freelance service bookings with 100% verified provider status, clear upfront pricing indications, and direct, low-friction communication (WhatsApp / Direct Call).

---

## 2. Target Personas

### Persona A: "The Homeowner / Urban Tenant" (Budi, 32)
- **Profile:** Busy professional living in Jakarta Selatan.
- **Goal:** Rapidly find an AC technician or plumber who can arrive same-day or next-day.
- **Key Needs:** Distance-based search, verified provider badges, transparent customer reviews, 1-click WhatsApp inquiry.

### Persona B: "The Local Service Provider / Tukang" (Pak Hendra, 45)
- **Profile:** Independent AC repair specialist running a team of 3 technicians.
- **Goal:** Expand service radius and acquire steady inquiries without high advertising fees.
- **Key Needs:** Simple onboarding form, ability to showcase photo portfolios, clear metric tracking (listing views & contact clicks).

---

## 3. Product Architecture & User Journey

The DekatSini web platform is organized around 3 core interaction pillars:

1. **Discovery & Exploration (Public Flow):**
   - **Landing Page (`/`):** Hero search bar, popular categories, step-by-step explainer ("Cara Kerja"), featured verified listings, provider acquisition CTA banner.
   - **Service Directory (`/services`):** Multi-facet filtered listing grid (by category, star rating, geographic distance slider, sort dropdown).
   - **Service Detail (`/services/:id`):** Sticky booking card, high-resolution previous work gallery, verified credentials, direct WhatsApp / Contact modal, related local services, verified customer reviews.

2. **Onboarding & Authentication Flow:**
   - **Split-Screen Auth (`/auth`):** One-click Google OAuth + Email/Password authentication with side-by-side value propositions (Trusted Providers, Instantly Local).
   - **Listing Creation Form (`/services/new`):** 3-step structured single-form covering basic service metadata, precise neighborhood address + map preview, and drag-and-drop portfolio photo uploads.

3. **Provider Portal & Management Flow:**
   - **Provider Dashboard (`/dashboard`):** Real-time performance metrics (Total Services, Views Today, Inbound Contacts/Inquiries), service inventory status table (Active, Pending Review), quick edit/delete controls.

---

## 4. Key Functional Requirements

### 4.1 Discovery & Search
- **FR-01 (Smart Query Search):** Real-time query matching on service title, trade keyword, and category tags (e.g., "AC", "Tukang Listrik", "Cuci Sofa").
- **FR-02 (Facet & Geo Filters):** Filter by category (Home Maintenance, Auto Care, Cleaning, Pet Care, Wellness), minimum star ratings (3+, 4+, 5.0), and distance radius slider (0–20 km).
- **FR-03 (Sorting Options):** Sort by "Recommended", "Top Rated", "Distance: Nearest First", and "Price: Low to High".

### 4.2 Service Detail & Conversion
- **FR-04 (Direct Lead Actions):** Primary CTA buttons for direct contact:
  - *WhatsApp Quick Chat* (pre-populates WhatsApp text template with service title and user query).
  - *Request a Quote Modal* / *Contact Phone Call*.
- **FR-05 (Trust Signals):** "100% Verified Professional" verification pill badge, warranty badge (e.g., 3-month guarantee), licensed tech checkmarks.
- **FR-06 (Social Proof):** Star rating breakdown, verified client review cards, past work visual evidence grid.

### 4.3 Provider Operations & Listing Management
- **FR-07 (Service Submission):** Multi-attribute listing creation with category selector, service name, service area radius, contact WhatsApp, and multiple photo uploads.
- **FR-08 (Analytics Dashboard):** Aggregated metrics tracking impressions ("Views Today") and high-intent leads ("New Contacts").
- **FR-09 (Listing Lifecycle Management):** Ability to toggle status between *Active*, *Paused*, and *Under Review*, with in-line edit and delete capabilities.

---

## 5. Non-Functional & Technical Specifications

| Parameter | Specification |
|---|---|
| **Form Factors** | Responsive Desktop (1440px grid) and Mobile Web (390px viewport) |
| **Styling Tokens** | Tailwind CSS / Design System Tokens (`#2563EB` primary, neutral zinc/slate greys, `#10B981` success emerald, `#F59E0B` rating gold) |
| **Typography** | Modern sans-serif (`Inter` or `Plus Jakarta Sans`) with clean structural weights (400, 500, 600, 700) |
| **Performance Target** | Core Web Vitals LCP < 2.2s, CLS < 0.05, image optimization with modern web formats (WebP/AVIF) |
| **Localization** | Bahasa Indonesia (`id-ID`) primary di MVP. `en-US` disiapkan tapi non-aktif. Semua copy Blade baru wajib ID (unifikasi dari campur ID/EN di UI Design) |
| **Security & Privacy** | Secure auth session cookies, hashed passwords, rate-limited public inquiry endpoints |
| **Backend (MVP Lock)** | Laravel 12 + MySQL 8. Auth: Breeze + Socialite Google. Single role fleksibel (`users.is_provider` bool). Upload `storage/app/public` + WebP. Maps: Leaflet + OSM Nominatim (tanpa Google Maps billing) |
| **Hosting** | Sementara: Railway / Render / VPS. Final: VPS/Shared ID. NOT Vercel (tidak support PHP Laravel) |

---

## 8. Laravel MVP Implementation Spec (v1.1 Addition)

### 8.1 Routes (Blade)
```
GET / -> home.index (dekatsini_landing_page/code.html)
GET /services -> services.index (service_directory_listings)
GET /services/{slug} -> services.show (service_detail_view)
GET /auth (guest) -> auth.split (user_authentication_page) — login+register tab
POST /logout, POST /auth/google/callback
GET /services/new (auth) -> services.create (add_new_service_form)
POST /services (auth) -> store, status=pending
GET /dashboard (auth, is_provider) -> dashboard.index (provider_dashboard)
PATCH /services/{id}/status, DELETE /services/{id}
POST /leads (wa_click / quote) -> leads.store (rate-limited 30/min)
```

### 8.2 DB Schema MVP
- `users`: id, name, email unique, password, google_id nullable, is_provider bool default false, phone, timestamps
- `categories`: id, name unique [Listrik, Servis AC, Laundry, Kebersihan, Plumbing, Auto Care, Pet Care, Wellness], slug, icon
- `services`: id, user_id FK, category_id FK, title, slug unique, description, whatsapp, phone, address, city, latitude decimal(10,7), longitude decimal(10,7), price_from integer, status enum(active,pending,paused) default pending, is_verified bool default false, views_count integer default 0, timestamps + FULLTEXT(title,description)
- `service_photos`: id, service_id FK, path, is_cover bool
- `reviews`: id, service_id FK, user_id FK nullable, rating tinyint 1-5, comment, is_verified bool default false, timestamps (read-only di MVP, submit di Phase 2)
- `service_views`: id, service_id FK, viewed_at date, ip_hash, index(service_id, viewed_at)
- `leads`: id, service_id FK, user_id FK nullable, channel enum(whatsapp,phone,quote), message text nullable, created_at (untuk metrik New Contacts)

### 8.3 Key Logic
- Radius filter: Haversine SQL `WHERE ... HAVING distance <= :radius (0-20km)`. Lat/lng wajib dari Leaflet picker di form.
- Views Today: `COUNT service_views WHERE viewed_at=today`. Increment sekali per IP/jam (cache).
- New Contacts: `COUNT leads WHERE created_at=today AND service.user_id=auth`.
- WA template: `https://wa.me/62xxx?text=Halo,%20saya%20lihat%20[TITLE]%20di%20DekatSini...` + log ke `leads` via JS fetch sebelum redirect.
- Unifikasi bahasa: semua label EN di UI Design (`View Details`, `Welcome back`) diterjemahkan ke ID di Blade.

---

## 6. Current Design Inventory & Screen Map

The design team has produced 6 core high-fidelity screens:
1. **Screen 10 (`DekatSini Landing Page`):** High-converting SaaS landing page with localized Indonesian copy, categorized icon chips, workflow guide, and dual-sided CTA.
2. **Screen 8 (`Service Directory Listings`):** Faceted search UI with left filter rail, distance slider, provider rating badges, and grid cards.
3. **Screen 6 (`Service Detail View`):** Hero photo banner, trust badges, WhatsApp quick-CTA, service portfolio gallery, and review stream.
4. **Screen 4 (`Add New Service Form`):** 3-section card form with file upload zone and map location preview.
5. **Screen 3 (`User Authentication Page`):** Split-screen layout highlighting brand perks with Google SSO and credential forms.
6. **Screen 1 (`Provider Dashboard`):** Metrics counter cards, service inventory management table, and partner status bar.

---

## 7. Roadmap & Future Scope (Phase 2 & 3)

- **Phase 2 (Transaction & Scheduling):**
  - Integrated booking calendar for provider availability slots.
  - In-app payment escrow & local payment gateways (QRIS, GoPay, OVO, Bank Transfer).
- **Phase 3 (Community & Expansion):**
  - Native iOS / Android provider and customer apps.
  - Real-time location dispatch tracking for emergency on-demand repairs.
  - Customer review submission modal with photo proof verification.
