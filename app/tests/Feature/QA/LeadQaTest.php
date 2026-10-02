<?php

namespace Tests\Feature\QA;

use App\Models\Lead;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modul B — Konversi / Lead (TC-B01 .. TC-B09).
 */
class LeadQaTest extends TestCase
{
    use RefreshDatabase, BuildsQAData;

    public function test_tc_b01_whatsapp_button_uses_template_link(): void
    {
        $svc = $this->makeService($this->makeUser(), $this->makeCategory());

        $expected = 'https://wa.me/6281234567890?text='.urlencode("Halo, saya lihat layanan {$svc->title} di DekatSini. Apakah tersedia?");

        $resp = $this->get('/services/'.$svc->slug);

        $resp->assertOk();
        $resp->assertSee('https://wa.me/6281234567890?text=', false);
        $resp->assertSee($expected, false);
        $resp->assertSee('DekatSini');
    }

    public function test_tc_b02_post_lead_whatsapp_is_logged(): void
    {
        $svc = $this->makeService($this->makeUser(), $this->makeCategory());

        $resp = $this->postJson('/leads', [
            'service_id' => $svc->id,
            'channel' => 'whatsapp',
        ]);

        $resp->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseHas('leads', [
            'service_id' => $svc->id,
            'channel' => 'whatsapp',
        ]);
    }

    public function test_tc_b03_lead_phone_and_quote_with_message_logged(): void
    {
        $svc = $this->makeService($this->makeUser(), $this->makeCategory());

        $this->postJson('/leads', ['service_id' => $svc->id, 'channel' => 'phone'])
            ->assertOk()->assertJson(['ok' => true]);
        $this->postJson('/leads', [
            'service_id' => $svc->id,
            'channel' => 'quote',
            'message' => 'Berapa harga cuci AC untuk 2 unit?',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('leads', ['service_id' => $svc->id, 'channel' => 'phone']);
        $this->assertDatabaseHas('leads', [
            'service_id' => $svc->id,
            'channel' => 'quote',
            'message' => 'Berapa harga cuci AC untuk 2 unit?',
        ]);
    }

    public function test_tc_b04_lead_appears_in_dashboard_new_contacts(): void
    {
        $owner = $this->makeUser();
        $svc = $this->makeService($owner, $this->makeCategory());

        $this->postJson('/leads', ['service_id' => $svc->id, 'channel' => 'whatsapp'])->assertOk();

        $resp = $this->actingAs($owner)->get('/dashboard');
        $resp->assertOk();
        $kontakBaru = $this->metricFrom($resp->getContent(), 'Kontak Baru');
        $this->assertNotNull($kontakBaru, 'Kartu "Kontak Baru" harus tampil di dashboard (TC-B04).');
        $this->assertGreaterThanOrEqual(1, $kontakBaru, 'Kontak Baru harus >= 1 setelah lead masuk (TC-B04).');
    }

    public function test_tc_b05_invalid_channel_rejected_422(): void
    {
        $svc = $this->makeService($this->makeUser(), $this->makeCategory());

        $this->postJson('/leads', ['service_id' => $svc->id, 'channel' => 'telegram'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['channel']);

        $this->assertSame(0, Lead::count(), 'Lead dengan channel tak valid tidak boleh tersimpan (TC-B05).');
    }

    public function test_tc_b06_invalid_service_id_rejected_422(): void
    {
        $this->postJson('/leads', ['service_id' => 999999, 'channel' => 'whatsapp'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['service_id']);

        $this->assertSame(0, Lead::count(), 'Lead dengan service_id tak valid tidak boleh tersimpan (TC-B06).');
    }

    public function test_tc_b07_lead_rate_limit_30_per_minute(): void
    {
        $svc = $this->makeService($this->makeUser(), $this->makeCategory());

        for ($i = 1; $i <= 30; $i++) {
            $this->postJson('/leads', ['service_id' => $svc->id, 'channel' => 'whatsapp'])
                ->assertOk();
        }

        $this->postJson('/leads', ['service_id' => $svc->id, 'channel' => 'whatsapp'])
            ->assertStatus(429);

        $this->assertSame(30, Lead::count(), 'Request ke-31 harus ditolak 429 (TC-B07).');
    }

    public function test_tc_b08_phone_button_uses_tel_link_and_logs_lead(): void
    {
        $svc = $this->makeService($this->makeUser(), $this->makeCategory(), ['phone' => '0211234567']);

        $resp = $this->get('/services/'.$svc->slug);

        $resp->assertOk();
        $resp->assertSee('href="tel:0211234567"', false);   // tombol telepon
        $resp->assertSee("logLead('phone')", false);
    }

    public function test_tc_b09_quote_form_sends_message_field(): void
    {
        $svc = $this->makeService($this->makeUser(), $this->makeCategory());

        $resp = $this->get('/services/'.$svc->slug);
        $html = $resp->getContent();

        $resp->assertOk();
        // Form quote harus punya field pesan...
        $this->assertMatchesRegularExpression('/name="(message|pesan)"/', $html, 'Form "Minta Penawaran" harus punya field pesan (TC-B09).');
        // ...dan JS harus mengirimkan field tersebut ke /leads.
        preg_match('/function logLead\(.*?\n\}/s', $html, $fn);
        $this->assertNotEmpty($fn, 'Fungsi logLead harus ada di halaman detail (TC-B09).');
        $this->assertStringContainsString('message', $fn[0], 'JS logLead harus mengirim field message (TC-B09).');
    }
}
