<x-layout title="Add Password - Dragon's Hoard Password Manager" main-class="flex flex-1 items-center justify-center py-8">
    <section class="grid w-full max-w-5xl gap-5 lg:grid-cols-[1fr_460px] lg:items-start">
        <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm lg:p-8">
            <p class="text-sm font-semibold uppercase text-red-800">New Treasure</p>
            <h1 class="mt-2 text-3xl font-bold text-stone-950">Save a website password</h1>
            <p class="mt-3 max-w-xl text-stone-600">
                Add a service, website URL, password, notes, and an automatically detected high-resolution favicon for the account.
            </p>

            <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-950">Storage details</p>
                <p class="mt-2 text-sm text-amber-900">
                    The saved website password is encrypted before it is stored in SQLite. The app tries to use the website's own favicon first, then falls back to a 256px favicon service.
                </p>
            </div>
        </div>

        <form class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm" action="/passwords" method="POST">
            @csrf

            <div class="mb-6">
                <p class="text-sm font-semibold uppercase text-red-800">Website Account</p>
                <h2 class="mt-1 text-2xl font-bold text-stone-950">Add to hoard</h2>
            </div>

            <div class="space-y-4">
                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Service name</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="text" name="service_name" value="{{ old('service_name') }}" placeholder="Google Classroom">
                    @error('service_name')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Website URL</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="url" name="url" value="{{ old('url') }}" placeholder="https://classroom.google.com">
                    @error('url')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Password</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="password" name="password" placeholder="Password for this website" autocomplete="new-password">
                    @error('password')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Notes</span>
                    <textarea class="mt-2 min-h-28 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" name="notes" placeholder="Security questions, account details, or reminders">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <button class="mt-6 inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-red-800 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2" type="submit">
                <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Save Password
            </button>
        </form>
    </section>
</x-layout>
