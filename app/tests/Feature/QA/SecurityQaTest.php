<?php

namespace Tests\Feature\QA;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Modul F — Keamanan & Kualitas Teknis (TC-F01 .. TC-F06).
 */
class SecurityQaTest extends TestCase
{
    use RefreshDatabase, BuildsQAData;

    public function test_tc_f01_xss_in_search_is_escaped(): void
    {
        $this->makeService($this->makeUser(), $this->makeCategory());

        $resp = $this->get('/services?search='.urlencode('<script>alert(1)</script>'));

        $resp->assertOk();
        $resp->assertDontSee('<script>alert(1)</script>', false);   // tag aktif tidak boleh ada
        $resp->assertSee('&lt;script&gt;', false);                 // harus ter-escape
    }

    public function test_tc_f02_xss_in_listing_title_is_escaped(): void
    {
        $svc = $this->makeService($this->makeUser(), $this->makeCategory(), [
            'title' => '<img src=x onerror=alert(1)>',
        ]);

        $list = $this->get('/services');
        $list->assertOk();
        $list->assertDontSee('<img src=x', false);
        $list->assertSee('&lt;img src=x', false);

        $detail = $this->get('/services/'.$svc->slug);
        $detail->assertOk();
        $detail->assertDontSee('<img src=x', false);
        $detail->assertSee('&lt;img src=x', false);
    }

    public function test_tc_f03_csrf_protected_forms_reject_post_without_token(): void
    {
        $user = $this->makeUser();
        $payload = $this->validServicePayload();

        // Nonaktifkan bypass CSRF khusus mode testing (PreventRequestForgery::runningUnitTests).
        $this->app->instance('env', 'production');

        $this->actingAs($user)->post('/services', $payload)->assertStatus(419);

        // Form juga wajib menyertakan token.
        $this->app->instance('env', 'testing');
        $html = $this->actingAs($user)->get('/services-new')->getContent();
        $this->assertStringContainsString('_token', $html, 'Form listing harus menyertakan token CSRF (TC-F03).');
    }

    public function test_tc_f04_guest_cannot_post_protected_routes(): void
    {
        $this->post('/services', [])->assertRedirect('/login');
        $this->patch('/services/1', [])->assertRedirect('/login');
        $this->delete('/services/1')->assertRedirect('/login');
    }

    public function test_tc_f05_password_stored_hashed(): void
    {
        $raw = 'kata-sandi-rahasia-99';

        $this->post('/register', [
            'name' => 'Uji Keamanan',
            'email' => 'aman@example.com',
            'password' => $raw,
            'password_confirmation' => $raw,
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $user = \App\Models\User::where('email', 'aman@example.com')->firstOrFail();
        $this->assertNotSame($raw, $user->password, 'Password tidak boleh tersimpan plaintext (TC-F05).');
        $this->assertTrue(Hash::check($raw, $user->password), 'Password harus valid ter-hash (TC-F05).');
        $this->assertTrue(str_starts_with($user->password, '$2y$') || str_starts_with($user->password, '$argon'), 'Password harus di-hash bcrypt/argon (TC-F05).');
    }

    public function test_tc_f06_lead_rejected_for_pending_or_paused_listing(): void
    {
        $owner = $this->makeUser();
        $cat = $this->makeCategory();

        foreach (['pending', 'paused'] as $status) {
            $svc = $this->makeService($owner, $cat, ['status' => $status]);

            $resp = $this->postJson('/leads', ['service_id' => $svc->id, 'channel' => 'whatsapp']);

            $this->assertTrue(
                in_array($resp->status(), [403, 422], true),
                "Lead untuk listing {$status} harus ditolak (dapat {$resp->status()}) (TC-F06)."
            );
        }

        $this->assertSame(0, Lead::count(), 'Tidak boleh ada lead tersimpan untuk listing non-aktif (TC-F06).');
    }
}
