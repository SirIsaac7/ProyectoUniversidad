<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        if (! $googleUser->email) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google no devolvio un correo electronico valido.',
                ]);
        }

        $mustSetupLocalPassword = false;

        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user) {
            if (! $user->estado) {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Tu cuenta se encuentra inactiva.',
                    ]);
            }

            $user->update([
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->name ?? 'Usuario Google',
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => Hash::make(Str::random(24)),
                'estado' => true,
                'email_verified_at' => now(),
            ]);

            $mustSetupLocalPassword = true;
        }

        session(['authenticated_via_google' => true]);

        if ($user->hasEnabledTwoFactorAuthentication()) {
            session([
                'login.id' => $user->getKey(),
                'login.remember' => true,
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            return redirect()->route('two-factor.login');
        }

        Auth::login($user, true);

        if ($mustSetupLocalPassword) {
            return redirect()
                ->route('perfil.password-local.edit')
                ->with('status', 'local-password-required');
        }

        return redirect()->route('inicio');
    }
}
