<?php

use App\Models\User;
use App\Models\StoredPassword;
use App\Models\StoredPasswordShare;
use App\Notifications\PasswordShareInvitation;
use App\Services\FaviconResolver;
use App\Services\TotpService;
use App\Notifications\MfaEmailCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

Route::get('/', function (Request $request) {
    $storedPasswords = $request->user()
        ->storedPasswords()
        ->latest()
        ->get();
    $sharedPasswords = $request->user()
        ->acceptedPasswordShares()
        ->with(['storedPassword.user'])
        ->latest('accepted_at')
        ->get()
        ->pluck('storedPassword')
        ->filter();

    return view('welcome', [
        'storedPasswords' => $storedPasswords,
        'sharedPasswords' => $sharedPasswords,
    ]);
})->middleware(['auth', 'verified']);

Route::get('/passwords/create', function () {
    return view('passwords.create');
})->middleware(['auth', 'verified']);

Route::get('/generator', function () {
    return view('generator');
})->middleware(['auth', 'verified']);

Route::get('/settings', function (Request $request) {
    $sessions = DB::table('sessions')
        ->where('user_id', $request->user()->id)
        ->orderByDesc('last_activity')
        ->get()
        ->map(fn (object $session) => [
            'id' => $session->id,
            'ip_address' => $session->ip_address,
            'user_agent' => $session->user_agent,
            'last_activity' => now()->setTimestamp($session->last_activity),
            'is_current' => $session->id === $request->session()->getId(),
        ]);

    return view('settings', [
        'sessions' => $sessions,
        'passkeys' => $request->user()->passkeys()->latest()->get(),
    ]);
})->middleware(['auth', 'verified']);

Route::put('/settings/password', function (Request $request) {
    $validated = $request->validate([
        'current_password' => ['required', 'current_password'],
        'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
    ]);

    $request->user()->update([
        'password' => $validated['password'],
    ]);

    $request->session()->regenerate();

    return back()->with('status', 'Master password updated.');
})->middleware(['auth', 'verified']);

Route::put('/settings/mfa', function (Request $request, TotpService $totpService) {
    $validated = $request->validate([
        'current_password' => ['required', 'current_password'],
        'mfa_method' => ['required', 'in:email,token'],
    ]);

    $user = $request->user();

    if ($validated['mfa_method'] === 'email') {
        $user->update([
            'mfa_method' => 'email',
            'mfa_token_hash' => null,
            'mfa_totp_secret' => null,
        ]);

        return back()->with('status', 'Email multi-factor authentication enabled.');
    }

    $secret = $totpService->generateSecret();

    $user->update([
        'mfa_method' => 'token',
        'mfa_email_code_hash' => null,
        'mfa_email_code_expires_at' => null,
        'mfa_token_hash' => null,
        'mfa_totp_secret' => $secret,
    ]);

    return back()
        ->with('status', 'Authenticator app multi-factor authentication enabled.')
        ->with('totp_secret', $secret)
        ->with('totp_uri', $totpService->provisioningUri($user, $secret));
})->middleware(['auth', 'verified']);

Route::delete('/settings/sessions/{session}', function (Request $request, string $session) {
    abort_if($session === $request->session()->getId(), 422);

    DB::table('sessions')
        ->where('user_id', $request->user()->id)
        ->where('id', $session)
        ->delete();

    return back()->with('status', 'Session revoked.');
})->middleware(['auth', 'verified']);

Route::delete('/settings/passkeys/{passkey}', function (Request $request, string $passkey) {
    $request->user()
        ->passkeys()
        ->whereKey($passkey)
        ->delete();

    return back()->with('status', 'Passkey removed.');
})->middleware(['auth', 'verified']);

Route::post('/passwords', function (Request $request, FaviconResolver $faviconResolver) {
    $validated = $request->validate([
        'service_name' => ['required', 'string', 'max:255'],
        'url' => ['required', 'url', 'max:2048'],
        'password' => ['required', 'string'],
        'notes' => ['nullable', 'string', 'max:5000'],
        'favicon_url' => ['nullable', 'url', 'max:2048'],
    ]);

    StoredPassword::create([
        'user_id' => $request->user()->id,
        'service_name' => $validated['service_name'],
        'url' => $validated['url'],
        'password' => $validated['password'],
        'notes' => $validated['notes'] ?? null,
        'favicon_url' => $faviconResolver->resolve($validated['url'], $validated['favicon_url'] ?? null),
    ]);

    return redirect('/')->with('status', 'Password saved to the hoard.');
})->middleware(['auth', 'verified']);

Route::get('/passwords/{storedPassword}', function (Request $request, StoredPassword $storedPassword) {
    $isOwner = $storedPassword->user_id === $request->user()->id;
    $acceptedShare = $storedPassword->shares()
        ->where('recipient_user_id', $request->user()->id)
        ->where('status', 'accepted')
        ->first();

    abort_unless($isOwner || $acceptedShare, 404);

    return view('passwords.show', [
        'storedPassword' => $storedPassword,
        'isOwner' => $isOwner,
        'shares' => $isOwner ? $storedPassword->shares()->latest()->get() : collect(),
    ]);
})->middleware(['auth', 'verified']);

Route::post('/passwords/{storedPassword}/reveal', function (Request $request, StoredPassword $storedPassword) {
    $canAccess = $storedPassword->user_id === $request->user()->id
        || $storedPassword->shares()
            ->where('recipient_user_id', $request->user()->id)
            ->where('status', 'accepted')
            ->exists();

    abort_unless($canAccess, 404);

    return response()->json([
        'password' => $storedPassword->password,
    ]);
})->middleware(['auth', 'verified']);

Route::post('/passwords/{storedPassword}/shares', function (Request $request, StoredPassword $storedPassword) {
    abort_unless($storedPassword->user_id === $request->user()->id, 404);

    $validated = $request->validate([
        'recipient_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'exists:users,email'],
    ]);

    $recipientEmail = strtolower($validated['recipient_email']);

    if ($recipientEmail === strtolower($request->user()->email)) {
        throw ValidationException::withMessages([
            'recipient_email' => 'You already own this password.',
        ]);
    }

    if ($storedPassword->shares()->where('recipient_email', $recipientEmail)->exists()) {
        throw ValidationException::withMessages([
            'recipient_email' => 'This password has already been shared with that email address.',
        ]);
    }

    $recipient = User::where('email', $recipientEmail)->firstOrFail();

    if (! $recipient->hasVerifiedEmail()) {
        throw ValidationException::withMessages([
            'recipient_email' => 'The recipient must verify their email before receiving shared passwords.',
        ]);
    }

    $share = StoredPasswordShare::create([
        'stored_password_id' => $storedPassword->id,
        'owner_id' => $request->user()->id,
        'recipient_user_id' => $recipient->id,
        'recipient_email' => $recipientEmail,
        'status' => 'pending',
    ]);

    $recipient->notify(new PasswordShareInvitation($share));

    return back()->with('status', 'Share invitation sent.');
})->middleware(['auth', 'verified']);

Route::get('/password-shares/{share}/accept', function (Request $request, StoredPasswordShare $share) {
    abort_unless(strtolower($share->recipient_email) === strtolower($request->user()->email), 403);

    $share->update([
        'recipient_user_id' => $request->user()->id,
        'status' => 'accepted',
        'accepted_at' => now(),
    ]);

    return redirect("/passwords/{$share->stored_password_id}")->with('status', 'Shared password accepted.');
})->middleware(['auth', 'verified', 'signed'])->name('password-shares.accept');

Route::get('/passwords/{storedPassword}/edit', function (Request $request, StoredPassword $storedPassword) {
    abort_unless($storedPassword->user_id === $request->user()->id, 404);

    return view('passwords.edit', [
        'storedPassword' => $storedPassword,
    ]);
})->middleware(['auth', 'verified']);

Route::put('/passwords/{storedPassword}', function (Request $request, StoredPassword $storedPassword, FaviconResolver $faviconResolver) {
    abort_unless($storedPassword->user_id === $request->user()->id, 404);

    $validated = $request->validate([
        'service_name' => ['required', 'string', 'max:255'],
        'url' => ['required', 'url', 'max:2048'],
        'password' => ['required', 'string'],
        'notes' => ['nullable', 'string', 'max:5000'],
    ]);

    $storedPassword->update([
        'service_name' => $validated['service_name'],
        'url' => $validated['url'],
        'password' => $validated['password'],
        'notes' => $validated['notes'] ?? null,
        'favicon_url' => $faviconResolver->resolve($validated['url']),
    ]);

    return redirect("/passwords/{$storedPassword->id}")->with('status', 'Password details updated.');
})->middleware(['auth', 'verified']);

Route::delete('/passwords/{storedPassword}', function (Request $request, StoredPassword $storedPassword) {
    abort_unless($storedPassword->user_id === $request->user()->id, 404);

    $storedPassword->delete();

    return redirect('/')->with('status', 'Password deleted from the hoard.');
})->middleware(['auth', 'verified']);

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    $user = $request->user();
    $remember = $request->boolean('remember');

    if ($user->mfa_method) {
        if ($user->mfa_method === 'email') {
            $code = (string) random_int(100000, 999999);

            $user->update([
                'mfa_email_code_hash' => Hash::make($code),
                'mfa_email_code_expires_at' => now()->addMinutes(10),
            ]);

            $user->notify(new MfaEmailCode($code));
        }

        Auth::logout();

        $request->session()->put([
            'mfa.user_id' => $user->id,
            'mfa.remember' => $remember,
        ]);

        return redirect('/mfa/challenge');
    }

    $request->session()->regenerate();

    return redirect('/');
});

Route::get('/mfa/challenge', function (Request $request) {
    $userId = $request->session()->get('mfa.user_id');

    if (! $userId) {
        return redirect('/login');
    }

    $user = User::findOrFail($userId);

    return view('auth.mfa-challenge', [
        'method' => $user->mfa_method,
    ]);
})->middleware('guest');

Route::post('/mfa/challenge', function (Request $request, TotpService $totpService) {
    $userId = $request->session()->get('mfa.user_id');

    if (! $userId) {
        return redirect('/login');
    }

    $validated = $request->validate([
        'code' => ['required', 'string'],
    ]);

    $user = User::findOrFail($userId);
    $code = trim($validated['code']);
    $valid = false;

    if ($user->mfa_method === 'email') {
        $valid = $user->mfa_email_code_hash
            && $user->mfa_email_code_expires_at
            && $user->mfa_email_code_expires_at->isFuture()
            && Hash::check($code, $user->mfa_email_code_hash);
    }

    if ($user->mfa_method === 'token') {
        $valid = $user->mfa_totp_secret && $totpService->verify($user->mfa_totp_secret, $code);
    }

    if (! $valid) {
        throw ValidationException::withMessages([
            'code' => 'The verification code is invalid.',
        ]);
    }

    $remember = (bool) $request->session()->get('mfa.remember', false);

    $user->update([
        'mfa_email_code_hash' => null,
        'mfa_email_code_expires_at' => null,
    ]);

    Auth::login($user, $remember);
    $request->session()->forget('mfa');
    $request->session()->regenerate();

    return redirect('/');
})->middleware('guest');

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/')->with('status', 'Email address verified.');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect('/');
    }

    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/register', function () {
    return view('register');
});

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        'terms' => ['accepted'],
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => $validated['password'],
        'mfa_method' => 'email',
    ]);

    Auth::login($user);
    $user->sendEmailVerificationNotification();

    return redirect('/email/verify');
});
