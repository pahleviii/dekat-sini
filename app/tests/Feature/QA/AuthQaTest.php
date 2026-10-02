<?php

namespace Tests\Feature\QA;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modul C — Auth (TC-C01 .. TC-C11).
 */
class AuthQaTest extends TestCase
{
    use RefreshDatabase, BuildsQAData;

    public function test_tc_c01_register_success_creates_user_and_redirects_dashboard(): void
    {
        $resp = $this->post('/register', [
            'name' => 'Budi Tester',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $resp->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
        $this->assertSame(1, User::count());
    }

    public function test_tc_c02_register_duplicate_email_rejected(): void
    {
        $this->makeUser(['email' => 'dupe@example.com']);

        $resp = $this->postJson('/register', [
            'name' => 'Duplikat',
            'email' => 'dupe@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $resp->assertUnprocessable()->assertJsonValidationErrors(['email']);
        $this->assertSame(1, User::count(), 'Email duplikat tidak boleh membuat user baru (TC-C02).');
    }

    public function test_tc_c03_login_success_redirects_dashboard(): void
    {
        $this->makeUser(['email' => 'login@example.com', 'password' => 'rahasia123']);

        $resp = $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'rahasia123',
        ]);

        $resp->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_tc_c04_wrong_password_gives_indonesian_error(): void
    {
        $this->makeUser(['email' => 'login@example.com', 'password' => 'rahasia123']);

        $resp = $this->postJson('/login', [
            'email' => 'login@example.com',
            'password' => 'salah-satu',
        ]);

        $resp->assertUnprocessable()->assertJsonValidationErrors(['email']);
        $this->assertGuest();

        $msg = $resp->json('errors.email.0');
        $this->assertNotEmpty($msg);
        $this->assertStringNotContainsString(
            'These credentials do not match our records.',
            $msg,
            'Pesan error login harus berbahasa Indonesia (TC-C04).'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/^auth\./',
            $msg,
            'Pesan error tidak boleh berupa kunci translasi mentah (TC-C04).'
        );
        $this->assertMatchesRegularExpression(
            '/\b(Yang Anda masukkan|salah|gagal|cocok|periksa|kata sandi)\b/iu',
            $msg,
            'Pesan error login harus berupa kalimat Indonesia yang jelas (TC-C04).'
        );
    }

    public function test_tc_c05_login_page_uses_indonesian_copy(): void
    {
        $resp = $this->get('/login');

        $resp->assertOk();
        $resp->assertSee('Masuk');
        $resp->assertSee('Lupa kata sandi?');
        $resp->assertSee('Ingat saya');
        $resp->assertDontSee('Forgot your password?');
        $resp->assertDontSee('Remember me');
        $resp->assertDontSee('Log in');
    }

    public function test_tc_c06_register_page_uses_indonesian_copy(): void
    {
        $resp = $this->get('/register');

        $resp->assertOk();
        $resp->assertSee('Nama');
        $resp->assertSee('Kata Sandi');
        $resp->assertSee('Konfirmasi Kata Sandi');
        $resp->assertSee('Sudah punya akun?');
        $resp->assertDontSee('Confirm Password');
        $resp->assertDontSee('Already registered?');
    }

    public function test_tc_c07_google_oauth_button_on_login_and_register(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('/auth/google', false);

        $this->get('/register')
            ->assertOk()
            ->assertSee('/auth/google', false);
    }

    public function test_tc_c08_guest_redirected_to_login(): void
    {
        $this->get('/services-new')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_tc_c09_logout_ends_session_and_redirects_home(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $resp = $this->post('/logout');

        $resp->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_tc_c10_logout_button_available_in_navbar(): void
    {
        $user = $this->makeUser();

        $resp = $this->actingAs($user)->get('/');

        $resp->assertOk();
        $resp->assertSee('/logout', false);      // form/tombol keluar ada di layout utama
        $resp->assertSee('Keluar');
    }

    public function test_tc_c11_google_auth_fails_safely_without_config(): void
    {
        config(['services.google' => null]);

        $resp = $this->get('/auth/google');

        $this->assertLessThan(
            500,
            $resp->getStatusCode(),
            'Google OAuth tanpa konfigurasi harus gagal aman, bukan 500 (TC-C11).'
        );
        // Harus redirect + pesan ke user, bukan stacktrace.
        $this->assertTrue(in_array($resp->getStatusCode(), [200, 302], true), 'Harus redirect aman, dapat status '.$resp->getStatusCode());
        $status = session('status') ?? session('error');
        $this->assertNotEmpty($status, 'Harus ada pesan kondisi ke user (TC-C11).');
    }
}
