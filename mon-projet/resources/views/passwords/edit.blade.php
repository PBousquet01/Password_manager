<x-layout title="Edit {{ $storedPassword->service_name }} - Dragon's Hoard Password Manager" main-class="flex flex-1 items-center justify-center py-8">
    <section class="grid w-full max-w-5xl gap-5 lg:grid-cols-[1fr_460px] lg:items-start">
        <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm lg:p-8">
            <p class="text-sm font-semibold uppercase text-red-800">Modify Treasure</p>
            <h1 class="mt-2 text-3xl font-bold text-stone-950">Edit saved password</h1>
            <p class="mt-3 max-w-xl text-stone-600">
                Update the service name, website URL, password, or notes. The favicon will refresh from the website URL.
            </p>

            <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-950">Storage details</p>
                <p class="mt-2 text-sm text-amber-900">
                    The password remains encrypted in SQLite after saving changes.
                </p>
            </div>
        </div>

        <form class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm" action="/passwords/{{ $storedPassword->id }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <p class="text-sm font-semibold uppercase text-red-800">Website Account</p>
                <h2 class="mt-1 text-2xl font-bold text-stone-950">{{ $storedPassword->service_name }}</h2>
            </div>

            <div class="space-y-4">
                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Service name</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="text" name="service_name" value="{{ old('service_name', $storedPassword->service_name) }}">
                    @error('service_name')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Website URL</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="url" name="url" value="{{ old('url', $storedPassword->url) }}">
                    @error('url')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Password</span>
                    <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="text" name="password" value="{{ old('password', $storedPassword->password) }}" autocomplete="off">
                    @error('password')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-stone-700">Notes</span>
                    <textarea class="mt-2 min-h-28 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 py-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" name="notes">{{ old('notes', $storedPassword->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="mt-6 flex gap-2">
                <button class="inline-flex h-11 flex-1 items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2" type="submit">
                    Save Changes
                </button>
                <a class="inline-flex h-11 items-center justify-center rounded-lg border border-stone-200 px-4 text-sm font-semibold hover:bg-stone-50" href="/passwords/{{ $storedPassword->id }}">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</x-layout>
