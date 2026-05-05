<x-layout title="Verify Email - Dragon's Hoard Password Manager" main-class="flex flex-1 items-center justify-center py-8">
    <section class="w-full max-w-xl rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase text-red-800">Email Verification</p>
        <h1 class="mt-2 text-3xl font-bold text-stone-950">Check your inbox</h1>
        <p class="mt-3 text-stone-600">
            Verify {{ Auth::user()->email }} before opening your vault. Use the link in the email we sent when the account was created.
        </p>

        @if (session('status') === 'verification-link-sent' || session('status') === 'Verification link sent.')
            <div class="mt-5 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-900">
                A fresh verification link has been sent to your email address.
            </div>
        @endif

        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <form method="POST" action="/email/verification-notification" class="flex-1">
                @csrf
                <button class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2" type="submit">
                    Resend Verification Email
                </button>
            </form>

            <form method="POST" action="/logout">
                @csrf
                <button class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-stone-200 px-4 text-sm font-semibold hover:bg-stone-50 sm:w-auto" type="submit">
                    Logout
                </button>
            </form>
        </div>
    </section>
</x-layout>
