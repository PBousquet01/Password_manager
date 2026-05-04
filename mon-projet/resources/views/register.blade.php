<x-layout title="Register - Dragon's Hoard Password Manager" main-class="flex flex-1 items-center justify-center py-8">
    <section class="grid w-full max-w-5xl gap-5 lg:grid-cols-[1fr_460px] lg:items-stretch">
        <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm lg:p-8">
            <p class="text-sm font-semibold uppercase text-red-800">New Hoard</p>
            <h1 class="mt-2 text-3xl font-bold text-stone-950">Create your vault</h1>
            <p class="mt-3 max-w-xl text-stone-600">
                Start a protected collection for school, work, shopping, and personal accounts with one master password.
            </p>

            <div class="mt-8 space-y-3">
                <div class="flex gap-3 rounded-lg border border-stone-200 p-4">
                    <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-red-50 text-red-800">
                        <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 3 5 6v5c0 4.4 2.9 8.5 7 10 4.1-1.5 7-5.6 7-10V6l-7-3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="font-semibold text-stone-950">Private by design</h2>
                        <p class="mt-1 text-sm text-stone-600">Your master password protects access to the whole hoard.</p>
                    </div>
                </div>

                <div class="flex gap-3 rounded-lg border border-stone-200 p-4">
                    <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-800">
                        <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="font-semibold text-stone-950">Ready to organize</h2>
                        <p class="mt-1 text-sm text-stone-600">Add categories later for school accounts, apps, and websites.</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-950">Project note</p>
                <p class="mt-2 text-sm text-amber-900">
                    In the backend, save only a secure password hash. Never store a plain-text master password.
                </p>
            </div>
        </div>

        <form class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm" action="/register" method="POST">
            @csrf

            <div class="mb-6">
                <p class="text-sm font-semibold uppercase text-red-800">Register</p>
                <h2 class="mt-1 text-2xl font-bold text-stone-950">Claim your hoard</h2>
            </div>

            <div class="space-y-4">
                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Full name</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="text" name="name" value="{{ old('name') }}" placeholder="Philippe Bousquet" autocomplete="name">
                    @error('name')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Email address</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="email" name="email" value="{{ old('email') }}" placeholder="student@example.com" autocomplete="email">
                    @error('email')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Master password</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="password" name="password" placeholder="Create a strong master password" autocomplete="new-password">
                    @error('password')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Confirm master password</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="password" name="password_confirmation" placeholder="Repeat your master password" autocomplete="new-password">
                    @error('password_confirmation')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="mt-5 rounded-lg border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-semibold text-stone-900">Master password should include:</p>
                <div class="mt-3 grid gap-2 text-sm text-stone-600 sm:grid-cols-2">
                    <span>At least 12 characters</span>
                    <span>Uppercase and lowercase</span>
                    <span>A number</span>
                    <span>A symbol</span>
                </div>
            </div>

            <label class="mt-5 flex items-start gap-2 text-sm text-stone-600">
                <input class="mt-0.5 size-4 rounded border-stone-300 text-red-800 focus:ring-red-800" type="checkbox" name="terms">
                <span>I understand that losing the master password can lock me out of the vault.</span>
            </label>
            @error('terms')
                <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
            @enderror

            <button class="mt-6 inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-red-800 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2" type="submit">
                <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Create Vault
            </button>

            <p class="mt-5 text-center text-sm text-stone-600">
                Already have an account?
                <a class="font-semibold text-red-800 hover:text-red-900" href="/login">Login</a>
            </p>
        </form>
    </section>
</x-layout>
