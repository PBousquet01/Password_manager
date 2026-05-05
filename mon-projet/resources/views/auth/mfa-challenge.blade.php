<x-layout title="Multi-factor Authentication - Dragon's Hoard Password Manager" main-class="flex flex-1 items-center justify-center py-8">
    <section class="w-full max-w-md rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase text-red-800">Extra Verification</p>
        <h1 class="mt-2 text-3xl font-bold text-stone-950">Enter your code</h1>
        <p class="mt-3 text-sm text-stone-600">
            @if ($method === 'email')
                We sent a six-digit code to your verified email address.
            @else
                Enter the six-digit code from your authenticator app.
            @endif
        </p>

        <form class="mt-6 space-y-4" action="/mfa/challenge" method="POST">
            @csrf

            <label class="block">
                <span class="text-sm font-semibold text-stone-700">Verification code</span>
                <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 font-mono text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="text" name="code" autocomplete="one-time-code" autofocus>
                @error('code')
                    <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                @enderror
            </label>

            <button class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white hover:bg-red-900" type="submit">
                Verify
            </button>
        </form>

        <form class="mt-3" action="/logout" method="POST">
            @csrf
            <button class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-stone-200 px-4 text-sm font-semibold hover:bg-stone-50" type="submit">
                Cancel Login
            </button>
        </form>
    </section>
</x-layout>
