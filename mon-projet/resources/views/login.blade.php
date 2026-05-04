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

            <p class="mt-5 text-center text-sm text-stone-600">
                New to Dragon's Hoard?
                <a class="font-semibold text-red-800 hover:text-red-900" href="/register">Create an account</a>
            </p>
        </form>
    </section>
</x-layout>
