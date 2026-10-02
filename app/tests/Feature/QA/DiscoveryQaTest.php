<?php

namespace Tests\Feature\QA;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modul A — Publik / Discovery (TC-A01 .. TC-A16).
 */
class DiscoveryQaTest extends TestCase
{
    use RefreshDatabase, BuildsQAData;

    private function seedCategories(int $n): void
    {
        for ($i = 1; $i <= $n; $i++) {
            $this->makeCategory(['name' => "Kategori Uji $i", 'slug' => "kategori-uji-$i"]);
        }
    }

    public function test_tc_a01_landing_page_shows_hero_search_categories_featured(): void
    {
        $this->seedCategories(8);
        $owner = $this->makeUser();
        $svc = $this->makeService($owner, Category::first(), [
            'title' => 'Servis AC Unggulan QA',
            'is_verified' => true,
        ]);

        $resp = $this->get('/');

        $resp->assertOk();
        $resp->assertSee('Temukan Layanan Terbaik');           // hero
        $resp->assertSee('name="search"', false);              // hero search form
        $this->assertGreaterThanOrEqual(8, substr_count($resp->getContent(), '/services?category='), 'Halaman landing harus menampilkan 8 kategori (TC-A01).');
        $this->assertSame(8, Category::count());
        $resp->assertSee('Layanan Unggulan');
        $resp->assertSee($svc->title);                         // featured tampil
        $this->assertGreaterThanOrEqual(1, Service::active()->where('is_verified', true)->count());
    }

    public function test_tc_a02_directories_shows_active_listings(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory();
        for ($i = 1; $i <= 12; $i++) {
            $this->makeService($owner, $cat, ['title' => "Jasa Aktif Nomor $i"]);
        }
        $this->makeService($owner, $cat, ['title' => 'Jasa Pending Tidak Tampil', 'status' => 'pending']);

        $resp = $this->get('/services');

        $resp->assertOk();
        $resp->assertSee('12 layanan ditemukan');
        $resp->assertSee('Jasa Aktif Nomor 1');
        $resp->assertSee('Lihat Detail');
        $this->assertStringContainsString('layanan ditemukan', $resp->getContent());
    }

    public function test_tc_a03_search_ac_returns_only_matching_results_fast(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory(['name' => 'Home Service', 'slug' => 'home-service']);
        for ($i = 1; $i <= 5; $i++) {
            $this->makeService($owner, $cat, ['title' => "Servis AC Profesional $i"]);
        }
        for ($i = 1; $i <= 3; $i++) {
            $this->makeService($owner, $cat, [
                'title' => "Tukang Pipa Bocor $i",
                'description' => 'Perbaikan pipa bocor dan mampet, laying-lining rumah.',
            ]);
        }

        $start = microtime(true);
        $resp = $this->get('/services?search=AC');
        $elapsed = microtime(true) - $start;

        $resp->assertOk();
        $this->assertLessThan(1.0, $elapsed, 'Pencarian "AC" harus selesai < 1 detik (TC-A03).');
        $resp->assertSee('5 layanan ditemukan');
        for ($i = 1; $i <= 5; $i++) {
            $resp->assertSee("Servis AC Profesional $i");
        }
        $resp->assertDontSee('Tukang Pipa Bocor 1');
        $resp->assertDontSee('Tukang Pipa Bocor 2');
        $resp->assertDontSee('Tukang Pipa Bocor 3');
    }

    public function test_tc_a04_search_without_result_returns_empty_not_error(): void
    {
        $owner = $this->makeUser();
        $this->makeService($owner, $this->makeCategory());

        $resp = $this->get('/services?search=zzzzz');

        $resp->assertOk();
        $resp->assertSee('0 layanan ditemukan');
    }

    public function test_tc_a05_filter_by_category_slug(): void
    {
        $owner = $this->makeUser();
        $catAc = $this->makeCategory(['name' => 'Servis AC', 'slug' => 'servis-ac']);
        $catLaundry = $this->makeCategory(['name' => 'Laundry', 'slug' => 'laundry']);
        $ac = $this->makeService($owner, $catAc, ['title' => 'Servis AC Rumah Tangga']);
        $laundry = $this->makeService($owner, $catLaundry, [
            'title' => 'Laundry Kiloan Kilat',
            'description' => 'Cuci kering kiloan antar jemput gratis.',
        ]);

        $resp = $this->get('/services?category=servis-ac');

        $resp->assertOk();
        $resp->assertSee($ac->title);
        $resp->assertDontSee($laundry->title);
        $resp->assertSee('1 layanan ditemukan');
    }

    public function test_tc_a06_filter_min_rating(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory();
        $good = $this->makeService($owner, $cat, ['title' => 'Jasa Rating Tinggi']);
        $this->makeReview($good, 5);
        $this->makeReview($good, 4);
        $bad = $this->makeService($owner, $cat, ['title' => 'Jasa Rating Rendah']);
        $this->makeReview($bad, 2);

        $resp = $this->get('/services?min_rating=4');

        $resp->assertOk();
        $resp->assertSee($good->title);
        $resp->assertDontSee($bad->title);
        $resp->assertSee('1 layanan ditemukan');
    }

    public function test_tc_a07_filter_radius_5km_shows_distance_on_card(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory();
        $near = $this->makeService($owner, $cat, [
            'title' => 'Jasa Dekat Sekitar Monas',
            'latitude' => -6.260,
            'longitude' => 106.810,
        ]);
        $far = $this->makeService($owner, $cat, [
            'title' => 'Jasa Jauh Di Yogyakarta',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        $resp = $this->get('/services?lat=-6.26&lng=106.81&radius=5');

        $resp->assertOk();
        $resp->assertSee($near->title);
        $resp->assertDontSee($far->title);
        $this->assertMatchesRegularExpression('/[\d.,]+\s*km/', $resp->getContent(), 'Kartu harus menampilkan jarak dalam km (TC-A07).');
    }

    public function test_tc_a08_radius_20km_sorted_by_nearest(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory();
        $near = $this->makeService($owner, $cat, [
            'title' => 'Jasa Paling Dekat',
            'latitude' => -6.260,
            'longitude' => 106.810,
        ]);
        $mid = $this->makeService($owner, $cat, [
            'title' => 'Jasa Agak Jauh',
            'latitude' => -6.360, // ±11 km
            'longitude' => 106.810,
        ]);
        $far = $this->makeService($owner, $cat, [
            'title' => 'Jasa Sangat Jauh',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        $resp = $this->get('/services?lat=-6.26&lng=106.81&radius=20');
        $html = $resp->getContent();

        $resp->assertOk();
        $resp->assertSee($near->title);
        $resp->assertSee($mid->title);
        $resp->assertDontSee($far->title);
        $posNear = strpos($html, $near->title);
        $posMid = strpos($html, $mid->title);
        $this->assertNotFalse($posNear);
        $this->assertNotFalse($posMid);
        $this->assertLessThan($posMid, $posNear, 'Urutan harus terdekat lebih dulu (TC-A08).');
    }

    public function test_tc_a09_sort_price_ascending(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory();
        $cheap = $this->makeService($owner, $cat, ['title' => 'Jasa Termurah', 'price_from' => 100000]);
        $mid = $this->makeService($owner, $cat, ['title' => 'Jasa Sedang', 'price_from' => 200000]);
        $expensive = $this->makeService($owner, $cat, ['title' => 'Jasa Termahal', 'price_from' => 300000]);

        $html = $this->get('/services?sort=price_asc')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, $mid->title), strpos($html, $cheap->title), 'Harga termurah harus lebih dulu (TC-A09).');
        $this->assertLessThan(strpos($html, $expensive->title), strpos($html, $mid->title), 'Harga harus monoton naik (TC-A09).');
    }

    public function test_tc_a10_sort_by_rating_desc(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory();
        $top = $this->makeService($owner, $cat, ['title' => 'Jasa Bintang Lima']);
        $this->makeReview($top, 5);
        $low = $this->makeService($owner, $cat, ['title' => 'Jasa Bintang Tiga']);
        $this->makeReview($low, 3);

        $html = $this->get('/services?sort=rating')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, $low->title), strpos($html, $top->title), 'Rating tertinggi harus pertama (TC-A10).');
    }

    public function test_tc_a11_default_sort_verified_first_then_views(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory();
        $unverified = $this->makeService($owner, $cat, [
            'title' => 'Jasa Belum Terverifikasi Banyak Views',
            'is_verified' => false,
            'views_count' => 1000,
        ]);
        $verifiedLow = $this->makeService($owner, $cat, [
            'title' => 'Jasa Terverifikasi Views Sedikit',
            'is_verified' => true,
            'views_count' => 10,
        ]);
        $verifiedHigh = $this->makeService($owner, $cat, [
            'title' => 'Jasa Terverifikasi Views Banyak',
            'is_verified' => true,
            'views_count' => 50,
        ]);

        $html = $this->get('/services')->assertOk()->getContent();

        $posVerifiedHigh = strpos($html, $verifiedHigh->title);
        $posVerifiedLow = strpos($html, $verifiedLow->title);
        $posUnverified = strpos($html, $unverified->title);
        $this->assertLessThan($posVerifiedLow, $posVerifiedHigh, 'Verified + views tinggi harus lebih dulu (TC-A11).');
        $this->assertLessThan($posUnverified, $posVerifiedLow, 'Verified harus di atas unverified (TC-A11).');
    }

    public function test_tc_a12_pending_listing_hidden_and_detail_404(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory();
        $active = $this->makeService($owner, $cat, ['title' => 'Jasa Aktif Tampil']);
        $pending = $this->makeService($owner, $cat, [
            'title' => 'Jasa Pending Tersembunyi',
            'status' => 'pending',
        ]);

        $this->get('/services')
            ->assertOk()
            ->assertSee($active->title)
            ->assertDontSee($pending->title);

        $this->get('/services/'.$pending->slug)->assertNotFound();
    }

    public function test_tc_a13_active_listing_detail_shows_key_info(): void
    {
        $owner = $this->makeUser();
        $svc = $this->makeService($owner, $this->makeCategory(), ['is_verified' => true]);
        $this->makeReview($svc, 5);

        $resp = $this->get('/services/'.$svc->slug);

        $resp->assertOk();
        $resp->assertSee($svc->title);
        $resp->assertSee('Rp 150,000');
        $resp->assertSee('★ 5.0');
        $resp->assertSee('TERVERIFIKASI');
        $resp->assertSee('id="map"', false);   // peta Leaflet
        $resp->assertSee('L.map', false);
    }

    public function test_tc_a14_unknown_slug_returns_404(): void
    {
        $this->get('/services/tidak-ada')->assertNotFound();
    }

    public function test_tc_a15_detail_shows_photo_gallery_reviews_related(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory();
        $svc = $this->makeService($owner, $cat);
        $this->makePhoto($svc, 'services/qa-gallery-cover.webp');
        $this->makeReview($svc, 5, 'Hasil kerja rapi dan cepat');
        $related = $this->makeService($owner, $cat, ['title' => 'Servis AC Terkait QA']);

        $resp = $this->get('/services/'.$svc->slug);

        $resp->assertOk();
        $resp->assertSee('Hasil kerja rapi dan cepat');    // daftar review
        $resp->assertSee('Jasa Terkait');
        $resp->assertSee($related->title);
        $resp->assertSee('services/qa-gallery-cover.webp', false); // foto galeri tampil
    }

    public function test_tc_a16_filter_ui_has_radius_sort_and_rating_controls(): void
    {
        $resp = $this->get('/services');

        $resp->assertOk();
        $resp->assertSee('name="radius"', false);   // slider radius
        $resp->assertSee('name="sort"', false);     // dropdown sort (rekomendasi/harga/rating)
        $resp->assertSee('3+ Bintang');             // opsi rating 3+
        $resp->assertSee('4+ Bintang');             // opsi rating 4+
        $resp->assertSee('5 Bintang');              // opsi rating 5
    }
}
