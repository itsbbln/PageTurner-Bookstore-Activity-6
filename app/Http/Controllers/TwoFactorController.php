<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorSecret;
use App\Notifications\TwoFactorCodeNotification;
use App\Notifications\TwoFactorDisabledNotification;
use App\Notifications\TwoFactorEnabledNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function settings(Request $request): View
    {
        return view('profile.two-factor-settings', [
            'user' => $request->user(),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            return back()->with('error', 'You must verify your email before enabling two-factor authentication.');
        }

        $recoveryCodes = $this->generateRecoveryCodes(8);
        $hashedCodes = array_map(fn ($code) => hash('sha256', strtoupper($code)), $recoveryCodes);

        $user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_type' => 'email',
            'two_factor_recovery_codes' => json_encode($hashedCodes),
        ])->save();

        $user->notify(new TwoFactorEnabledNotification());

        return redirect()->route('two-factor.settings')
            ->with('success', 'Two-factor authentication has been enabled.')
            ->with('recovery_codes', $recoveryCodes);
    }

    protected function generateRecoveryCodes(int $count): array
    {
        $codes = [];
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        for ($i = 0; $i < $count; $i++) {
            $codes[] = substr(str_shuffle(str_repeat($chars, 4)), 0, 8);
        }
        return $codes;
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_type' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        $user->notify(new TwoFactorDisabledNotification());

        $request->session()->forget(['two_factor_pending', 'two_factor_passed']);

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }

    public function showChallenge(Request $request): View|RedirectResponse
    {
        if (! $request->user()?->two_factor_enabled) {
            return redirect()->route('dashboard');
        }

        $this->sendCode($request);

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6'],
        ]);

        $user = $request->user();
        $code = $request->input('code');

        // Try recovery code first (8 chars, alphanumeric)
        if (strlen($code) === 8 && ctype_alnum($code)) {
            $hashedCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);
            $hashedInput = hash('sha256', strtoupper($code));
            $index = array_search($hashedInput, $hashedCodes);
            if ($index !== false) {
                array_splice($hashedCodes, $index, 1);
                $user->forceFill(['two_factor_recovery_codes' => json_encode($hashedCodes)])->save();
                $request->session()->put('two_factor_passed', true);
                $home = $user->isAdmin() ? route('admin.dashboard') : route('dashboard');
                return redirect()->intended($home);
            }
        }

        // Try email OTP (6 digits)
        $record = TwoFactorSecret::where('user_id', $user->id)
            ->where('otp_code', $code)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (! $record) {
            return back()->withErrors([
                'code' => 'The provided code is invalid or has expired.',
            ]);
        }

        $record->delete();

        $request->session()->put('two_factor_passed', true);

        $home = $user->isAdmin() ? route('admin.dashboard') : route('dashboard');
        return redirect()->intended($home);
    }

    public function resend(Request $request): RedirectResponse
    {
        $this->sendCode($request);

        return back()->with('status', 'A new verification code has been sent to your email address.');
    }

    protected function sendCode(Request $request): void
    {
        $user = $request->user();

        if (! $user->two_factor_enabled) {
            return;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        TwoFactorSecret::where('user_id', $user->id)->delete();

        TwoFactorSecret::create([
            'user_id' => $user->id,
            'otp_code' => $code,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $user->notify(new TwoFactorCodeNotification($code));
    }
}

