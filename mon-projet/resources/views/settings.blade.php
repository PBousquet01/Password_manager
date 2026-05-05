<x-layout title="Settings - Dragon's Hoard Password Manager" main-class="py-6">
    <section class="mx-auto max-w-5xl space-y-5">
        <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase text-red-800">Settings</p>
            <h1 class="mt-2 text-3xl font-bold text-stone-950">Account security</h1>
            <p class="mt-3 max-w-2xl text-stone-600">
                Manage sign-in protections, credentials, passkeys, and active sessions for {{ Auth::user()->email }}.
            </p>

            @if (session('status'))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif
        </div>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-5">
                <section class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-stone-950">Multi-factor authentication</h2>
                            <p class="mt-1 text-sm text-stone-600">Require an email code or an authenticator app code after your password.</p>
                        </div>
                        <span class="rounded-lg bg-green-100 px-3 py-2 text-sm font-semibold text-green-900">
                            {{ Auth::user()->mfa_method === 'token' ? 'Authenticator app' : 'Email' }} enabled
                        </span>
                    </div>

                    @if (session('totp_secret'))
                        <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                            <p class="text-sm font-semibold text-amber-950">Add this to Google Authenticator</p>
                            <p class="mt-2 text-sm text-amber-900">Use manual setup and enter this setup key.</p>
                            <p class="mt-3 break-all font-mono text-lg font-bold text-amber-900">{{ session('totp_secret') }}</p>
                            <p class="mt-3 break-all text-xs text-amber-900">{{ session('totp_uri') }}</p>
                        </div>
                    @endif

                    <form class="mt-5 space-y-4" action="/settings/mfa" method="POST">
                        @csrf
                        @method('PUT')

                        <fieldset class="grid gap-3 sm:grid-cols-2">
                            <label class="rounded-lg border border-stone-200 p-4">
                                <input class="size-4 text-red-800 focus:ring-red-800" type="radio" name="mfa_method" value="email" @checked((Auth::user()->mfa_method ?? 'email') === 'email')>
                                <span class="ml-2 text-sm font-semibold text-stone-950">Email code</span>
                            </label>
                            <label class="rounded-lg border border-stone-200 p-4">
                                <input class="size-4 text-red-800 focus:ring-red-800" type="radio" name="mfa_method" value="token" @checked(Auth::user()->mfa_method === 'token')>
                                <span class="ml-2 text-sm font-semibold text-stone-950">Authenticator app</span>
                            </label>
                        </fieldset>

                        @error('mfa_method')
                            <p class="text-sm font-semibold text-red-700">{{ $message }}</p>
                        @enderror

                        <label class="block">
                            <span class="text-sm font-semibold text-stone-700">Current master password</span>
                            <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="password" name="current_password" autocomplete="current-password">
                            @error('current_password')
                                <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                            @enderror
                        </label>

                        <button class="inline-flex h-11 items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white hover:bg-red-900" type="submit">
                            Save MFA Settings
                        </button>
                    </form>
                </section>

                <section class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-stone-950">Modify password</h2>
                    <p class="mt-1 text-sm text-stone-600">Change your master password. New passwords must stay strong.</p>

                    <form class="mt-5 space-y-4" action="/settings/password" method="POST">
                        @csrf
                        @method('PUT')

                        <label class="block">
                            <span class="text-sm font-semibold text-stone-700">Current master password</span>
                            <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="password" name="current_password" autocomplete="current-password">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-stone-700">New master password</span>
                            <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="password" name="password" autocomplete="new-password">
                            @error('password')
                                <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-stone-700">Confirm new master password</span>
                            <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="password" name="password_confirmation" autocomplete="new-password">
                        </label>

                        <button class="inline-flex h-11 items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white hover:bg-red-900" type="submit">
                            Update Password
                        </button>
                    </form>
                </section>
            </div>

            <div class="space-y-5">
                <section class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-stone-950">Passkeys</h2>
                    <p class="mt-1 text-sm text-stone-600">Registered passkeys will appear here.</p>

                    <div class="mt-5 space-y-3">
                        @forelse ($passkeys as $passkey)
                            <div class="rounded-lg border border-stone-200 p-4">
                                <p class="font-semibold text-stone-950">{{ $passkey->name }}</p>
                                <p class="mt-1 text-sm text-stone-500">Last used {{ $passkey->last_used_at?->diffForHumans() ?? 'never' }}</p>
                                <form class="mt-3" action="/settings/passkeys/{{ $passkey->id }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-semibold text-red-800 hover:text-red-900" type="submit">Remove</button>
                                </form>
                            </div>
                        @empty
                            <div class="rounded-lg border border-stone-200 bg-stone-50 p-4 text-sm text-stone-600">
                                No passkeys registered yet.
                            </div>
                        @endforelse
                    </div>

                    <button class="mt-5 inline-flex h-10 w-full items-center justify-center rounded-lg border border-stone-200 px-4 text-sm font-semibold text-stone-500" type="button" disabled>
                        Add Passkey
                    </button>
                </section>

                <section class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-stone-950">Sessions</h2>
                    <p class="mt-1 text-sm text-stone-600">Review browsers currently signed in to your account.</p>

                    <div class="mt-5 space-y-3">
                        @forelse ($sessions as $session)
                            <div class="rounded-lg border border-stone-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-stone-950">{{ $session['user_agent'] ?: 'Unknown browser' }}</p>
                                        <p class="mt-1 text-sm text-stone-500">{{ $session['ip_address'] ?: 'Unknown IP' }}</p>
                                        <p class="mt-1 text-xs text-stone-500">Last active {{ $session['last_activity']->diffForHumans() }}</p>
                                    </div>
                                    @if ($session['is_current'])
                                        <span class="shrink-0 rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-green-900">Current</span>
                                    @endif
                                </div>

                                @unless ($session['is_current'])
                                    <form class="mt-3" action="/settings/sessions/{{ $session['id'] }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm font-semibold text-red-800 hover:text-red-900" type="submit">Revoke</button>
                                    </form>
                                @endunless
                            </div>
                        @empty
                            <div class="rounded-lg border border-stone-200 bg-stone-50 p-4 text-sm text-stone-600">
                                No database sessions found.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </section>
</x-layout>
