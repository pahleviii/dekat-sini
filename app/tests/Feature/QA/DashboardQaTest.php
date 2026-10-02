<?php

namespace Tests\Feature\QA;

use App\Models\Lead;
use App\Models\Service;
use App\Models\ServiceView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modul E — Manajemen Listing / Dashboard (TC-E01 .. TC-E12).
 */
class DashboardQaTest extends TestCase
{
    use RefreshDatabase, BuildsQAData;

    public function test_tc_e01_dashboard_shows_metrics_and_my_listings_table(): void
    {
        $owner = $this->makeUser();
        $this->makeService($owner, $this->makeCategory());

        $resp = $this->actingAs($owner)->get('/dashboard');

        $resp->assertOk();
        $resp->assertSee('Total Jasa');
        $resp->assertSee('Dilihat Hari Ini');
        $resp->assertSee('Kontak Baru');
        $resp->assertSee('Jasa Saya');
    }

    public function test_tc_e02_views_today_counts_unique_ip_per_hour(): void
    {
        $owner = $this->makeUser();
        $svc = $this->makeService($owner, $this->makeCategory());
        $url = '/services/'.$svc->slug;

        $this->get($url)->assertOk();                       // IP A, jam ke-n
        $this->get($url)->assertOk();                       // IP A lagi (dalam 1 jam) → tidak dihitung
        $this->withServerVariables(['REMOTE_ADDR' => '10.2.2.2'])
            ->get($url)->assertOk();                        // IP B → dihitung

        $this->assertSame(2, ServiceView::where('service_id', $svc->id)->count(), 'View unik per IP/jam (TC-E02).');

        $resp = $this->actingAs($owner)->get('/dashboard');
        $this->assertSame(2, $this->metricFrom($resp->getContent(), 'Dilihat Hari Ini'), 'Views Today = 2 (UAT#4, TC-E02).');
    }

    public function test_tc_e03_views_yesterday_not_counted_today(): void
    {
        $owner = $this->makeUser();
        $svc = $this->makeService($owner, $this->makeCategory());
        $this->makeView($svc, now()->subDay()->toDateString());   // view kemarin

        $resp = $this->actingAs($owner)->get('/dashboard');

        $resp->assertOk();
        $this->assertSame(0, $this->metricFrom($resp->getContent(), 'Dilihat Hari Ini'), 'View kemarin tidak boleh dihitung (TC-E03).');
    }

    public function test_tc_e04_new_contacts_counts_own_services_today_only(): void
    {
        $owner = $this->makeUser();
        $otherOwner = $this->makeUser();
        $mine = $this->makeService($owner, $this->makeCategory());
        $theirs = $this->makeService($otherOwner, $this->makeCategory());

        Lead::create(['service_id' => $mine->id, 'channel' => 'whatsapp']);     // hari ini, milik sendiri
        Lead::create(['service_id' => $mine->id, 'channel' => 'phone']);        // hari ini, milik sendiri
        Lead::create(['service_id' => $theirs->id, 'channel' => 'whatsapp']);   // hari ini, milik orang lain
        $yesterdayId = Lead::create(['service_id' => $mine->id, 'channel' => 'quote'])->id;
        Lead::where('id', $yesterdayId)->update(['created_at' => now()->subDay()]); // kemarin

        $resp = $this->actingAs($owner)->get('/dashboard');

        $resp->assertOk();
        $this->assertSame(2, $this->metricFrom($resp->getContent(), 'Kontak Baru'), 'Kontak Baru = 2 (hanya milik sendiri & hari ini, UAT#4, TC-E04).');
    }

    public function test_tc_e05_total_services_counts_own_only(): void
    {
        $owner = $this->makeUser();
        $otherOwner = $this->makeUser();
        $cat = $this->makeCategory();
        for ($i = 1; $i <= 3; $i++) {
            $this->makeService($owner, $cat, ['title' => "Jasa Milik Sendiri $i"]);
        }
        $this->makeService($otherOwner, $cat, ['title' => 'Jasa Orang Lain 1']);
        $this->makeService($otherOwner, $cat, ['title' => 'Jasa Orang Lain 2']);

        $resp = $this->actingAs($owner)->get('/dashboard');

        $resp->assertOk();
        $this->assertSame(3, $this->metricFrom($resp->getContent(), 'Total Jasa'), 'Total Services hanya milik sendiri (TC-E05).');
    }

    public function test_tc_e06_owner_can_edit_own_listing(): void
    {
        $owner = $this->makeUser();
        $svc = $this->makeService($owner, $this->makeCategory());

        $this->actingAs($owner)->get('/services/'.$svc->id.'/edit')->assertOk();

        $resp = $this->actingAs($owner)->patch('/services/'.$svc->id, [
            'title' => 'Judul Hasil Edit',
            'category_id' => $svc->category_id,
            'description' => 'Deskripsi hasil edit yang cukup panjang minimal dua puluh karakter.',
            'whatsapp' => $svc->whatsapp,
            'price_from' => 175000,
            'status' => 'active',
        ]);

        $resp->assertRedirect(route('dashboard'));
        $this->assertSame('Judul Hasil Edit', $svc->fresh()->title, 'Data listing harus berubah setelah edit (TC-E06).');
    }

    public function test_tc_e07_cannot_edit_others_listing_idor(): void
    {
        $owner = $this->makeUser();
        $intruder = $this->makeUser();
        $svc = $this->makeService($owner, $this->makeCategory());
        $payload = [
            'title' => 'Judul Diretas',
            'category_id' => $svc->category_id,
            'description' => 'Deskripsi diretas yang cukup panjang minimal dua puluh karakter.',
            'whatsapp' => $svc->whatsapp,
            'status' => 'active',
        ];

        $this->actingAs($intruder)->get('/services/'.$svc->id.'/edit')->assertForbidden();
        $this->actingAs($intruder)->patch('/services/'.$svc->id, $payload)->assertForbidden();
        $this->actingAs($intruder)->delete('/services/'.$svc->id)->assertForbidden();

        $this->assertSame('active', $svc->fresh()->status, 'Status tidak boleh berubah (TC-E07).');
        $this->assertNotSame('Judul Diretas', $svc->fresh()->title, 'Judul tidak boleh berubah (BR-04, TC-E07).');
        $this->assertTrue(Service::whereKey($svc->id)->exists(), 'Listing tidak boleh terhapus (TC-E07).');
    }

    public function test_tc_e08_owner_can_delete_own_listing_with_cascade(): void
    {
        $owner = $this->makeUser();
        $svc = $this->makeService($owner, $this->makeCategory());
        $this->makePhoto($svc, 'services/foto-akan-hilang.webp');

        $resp = $this->actingAs($owner)->delete('/services/'.$svc->id);

        $resp->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('services', ['id' => $svc->id]);
        $this->assertDatabaseMissing('service_photos', ['service_id' => $svc->id]);
        $this->assertTrue(
            ! \App\Models\ServicePhoto::where('service_id', $svc->id)->exists(),
            'Baris foto harus ikut terhapus (cascade, TC-E08).'
        );
    }

    public function test_tc_e09_toggle_status_active_paused(): void
    {
        $owner = $this->makeUser();
        $svc = $this->makeService($owner, $this->makeCategory(), ['title' => 'Jasa Toggle Status']);
        $payload = [
            'title' => $svc->title,
            'category_id' => $svc->category_id,
            'description' => $svc->description,
            'whatsapp' => $svc->whatsapp,
        ];

        $this->actingAs($owner)->patch('/services/'.$svc->id, $payload + ['status' => 'paused'])
            ->assertRedirect(route('dashboard'));
        $this->assertSame('paused', $svc->fresh()->status, 'Status harus berubah ke paused (TC-E09).');
        $this->get('/services')->assertOk()->assertDontSee($svc->title);

        $this->actingAs($owner)->patch('/services/'.$svc->id, $payload + ['status' => 'active'])
            ->assertRedirect(route('dashboard'));
        $this->assertSame('active', $svc->fresh()->status, 'Status harus kembali aktif (TC-E09).');
        $this->get('/services')->assertOk()->assertSee($svc->title);
    }

    public function test_tc_e10_pending_listing_cannot_self_activate(): void
    {
        $owner = $this->makeUser();
        $svc = $this->makeService($owner, $this->makeCategory(), ['status' => 'pending']);

        $this->actingAs($owner)->patch('/services/'.$svc->id, [
            'title' => $svc->title,
            'category_id' => $svc->category_id,
            'description' => $svc->description,
            'whatsapp' => $svc->whatsapp,
            'status' => 'active',
        ]);

        $this->assertSame(
            'pending',
            $svc->fresh()->status,
            'Listing pending tidak boleh diaktifkan sendiri (UAT#3, BR-01, TC-E10).'
        );
    }

    public function test_tc_e11_dashboard_edit_link_uses_id_route(): void
    {
        $owner = $this->makeUser();
        $svc = $this->makeService($owner, $this->makeCategory());

        $resp = $this->actingAs($owner)->get('/dashboard');

        $resp->assertOk();
        $resp->assertSee('/services/'.$svc->id.'/edit', false);
    }

    public function test_tc_e12_non_provider_can_open_dashboard_with_zero_metrics(): void
    {
        $user = $this->makeUser(['is_provider' => false]);

        $resp = $this->actingAs($user)->get('/dashboard');

        $resp->assertOk();
        $this->assertSame(0, $this->metricFrom($resp->getContent(), 'Total Jasa'), 'Dashboard user biasa = 0 (TC-E12).');
    }
}
