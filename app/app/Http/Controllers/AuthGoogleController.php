<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
class AuthGoogleController extends Controller
{
    public function redirect()
    {
        if (! $this->configured()) {
            return redirect()->route('login')
                ->with('status', 'Login Google belum dikonfigurasi. Silakan masuk menggunakan email dan kata sandi.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        if (! $this->configured()) {
            return redirect()->route('login')
                ->with('status', 'Login Google belum dikonfigurasi. Silakan masuk menggunakan email dan kata sandi.');
        }

        $g = Socialite::driver('google')->user();
        $user = User::updateOrCreate(
            ['email' => $g->getEmail()],
            ['name' => $g->getName() ?? $g->getNickname() ?? 'Pengguna', 'google_id' => $g->getId()]
        );
        Auth::login($user, true);
        return redirect()->intended('/');
    }

    /**
     * Google OAuth hanya dijalankan bila kredensial terkonfigurasi (TC-C11).
     */
    private function configured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }
}
