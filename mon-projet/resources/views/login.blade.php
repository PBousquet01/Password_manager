<x-layout title="Login - Dragon's Hoard Password Manager" main-class="flex flex-1 items-center justify-center py-8">
    <section class="grid w-full max-w-5xl gap-5 lg:grid-cols-[1fr_420px] lg:items-stretch">
        <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm lg:p-8">
            <p class="text-sm font-semibold uppercase text-red-800">Vault Access</p>
            <h1 class="mt-2 text-3xl font-bold text-stone-950">Enter the hoard</h1>
            <p class="mt-3 max-w-xl text-stone-600">
                Sign in with your master password to unlock saved accounts, review weak passwords, and manage your private login collection.
            </p>

            <div class="mt-8 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-stone-200 p-4">
                    <p class="text-sm text-stone-500">Encrypted vault</p>
                    <p class="mt-2 text-xl font-bold text-red-800">Locked</p>
                </div>
                <div class="rounded-lg border border-stone-200 p-4">
                    <p class="text-sm text-stone-500">Saved logins</p>
                    <p class="mt-2 text-xl font-bold">24</p>
                </div>
                <div class="rounded-lg border border-stone-200 p-4">
                    <p class="text-sm text-stone-500">Security score</p>
                    <p class="mt-2 text-xl font-bold text-amber-700">75%</p>
                </div>
            </div>

            <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-950">Master password reminder</p>
                <p class="mt-2 text-sm text-amber-900">
                    For a real password manager, the master password should never be stored directly. It should be hashed and checked securely.
                </p>
            </div>
        </div>

        <form class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm" action="/login" method="POST">
            @csrf

            <div class="mb-6">
                <p class="text-sm font-semibold uppercase text-red-800">Login</p>
                <h2 class="mt-1 text-2xl font-bold text-stone-950">Unlock account</h2>
            </div>

            <div class="space-y-4">
                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Email address</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="email" name="email" value="{{ old('email') }}" placeholder="student@example.com" autocomplete="email">
                    @error('email')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Master password</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="password" name="password" placeholder="Enter your master password" autocomplete="current-password">
                    @error('password')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <div class="flex items-center justify-between gap-3">
                    <label class="flex items-center gap-2 text-sm text-stone-600">
                        <input class="size-4 rounded border-stone-300 text-red-800 focus:ring-red-800" type="checkbox" name="remember">
                        Remember me
                    </label>

                    <a class="text-sm font-semibold text-red-800 hover:text-red-900" href="#">Forgot password?</a>
                </div>
            </div>

            <button class="mt-6 inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-red-800 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2" type="submit">
                <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none">
                    <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M6.5 10h11A1.5 1.5 0 0 1 19 11.5v7A1.5 1.5 0 0 1 17.5 20h-11A1.5 1.5 0 0 1 5 18.5v-7A1.5 1.5 0 0 1 6.5 10Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 14v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Unlock Hoard
            </button>

            <div class="mt-5 flex items-center gap-3">
                <span class="h-px flex-1 bg-stone-200"></span>
                <span class="text-xs font-semibold uppercase text-stone-500">or</span>
                <span class="h-px flex-1 bg-stone-200"></span>
            </div>

            <button id="login-passkey" class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-lg border border-stone-200 px-4 text-sm font-semibold hover:bg-stone-50 disabled:cursor-not-allowed disabled:bg-stone-100 disabled:text-stone-400" type="button">
                Login with Passkey
            </button>
            <p id="passkey-login-status" class="mt-3 text-sm font-semibold text-stone-600"></p>

            <p class="mt-5 text-center text-sm text-stone-600">
                New to Dragon's Hoard?
                <a class="font-semibold text-red-800 hover:text-red-900" href="/register">Create an account</a>
            </p>
        </form>
    </section>

    @push('scripts')
        <script>
            const passkeyLoginButton = document.getElementById('login-passkey');
            const passkeyLoginStatus = document.getElementById('passkey-login-status');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            function base64urlToBuffer(value) {
                const base64 = value.replace(/-/g, '+').replace(/_/g, '/').padEnd(Math.ceil(value.length / 4) * 4, '=');
                const binary = atob(base64);
                const bytes = new Uint8Array(binary.length);

                for (let index = 0; index < binary.length; index++) {
                    bytes[index] = binary.charCodeAt(index);
                }

                return bytes.buffer;
            }

            function bufferToBase64url(buffer) {
                const bytes = new Uint8Array(buffer);
                let binary = '';

                bytes.forEach((byte) => binary += String.fromCharCode(byte));

                return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
            }

            function setPasskeyLoginStatus(message, isError = false) {
                passkeyLoginStatus.textContent = message;
                passkeyLoginStatus.className = `mt-3 text-sm font-semibold ${isError ? 'text-red-700' : 'text-stone-600'}`;
            }

            passkeyLoginButton?.addEventListener('click', async () => {
                if (!window.PublicKeyCredential) {
                    setPasskeyLoginStatus('This browser does not support passkeys.', true);
                    return;
                }

                passkeyLoginButton.disabled = true;
                setPasskeyLoginStatus('Preparing passkey login...');

                try {
                    const optionsResponse = await fetch('/passkeys/options', {
                        headers: {'Accept': 'application/json'},
                    });
                    const options = await optionsResponse.json();

                    options.challenge = base64urlToBuffer(options.challenge);
                    options.allowCredentials = options.allowCredentials.map((credential) => ({
                        ...credential,
                        id: base64urlToBuffer(credential.id),
                    }));

                    setPasskeyLoginStatus('Follow your browser prompt to sign in.');

                    const credential = await navigator.credentials.get({publicKey: options});
                    const payload = {
                        credential: {
                            id: credential.id,
                            type: credential.type,
                            rawId: bufferToBase64url(credential.rawId),
                            response: {
                                authenticatorData: bufferToBase64url(credential.response.authenticatorData),
                                clientDataJSON: bufferToBase64url(credential.response.clientDataJSON),
                                signature: bufferToBase64url(credential.response.signature),
                                userHandle: credential.response.userHandle ? bufferToBase64url(credential.response.userHandle) : null,
                            },
                        },
                    };

                    const loginResponse = await fetch('/passkeys/login', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });
                    const result = await loginResponse.json();

                    if (!loginResponse.ok) {
                        throw new Error(result.message || 'Passkey login failed.');
                    }

                    window.location.href = result.redirect || '/';
                } catch (error) {
                    setPasskeyLoginStatus(error.message || 'Passkey login was cancelled.', true);
                    passkeyLoginButton.disabled = false;
                }
            });
        </script>
    @endpush
</x-layout>
