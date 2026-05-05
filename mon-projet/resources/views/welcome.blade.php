@php
    $passwordCount = $storedPasswords->count();
    $sharedPasswordCount = $sharedPasswords->count();
    $totalPasswordCount = $passwordCount + $sharedPasswordCount;
@endphp

<x-layout title="Dragon's Hoard Password Manager" main-class="grid flex-1 gap-5 py-5 lg:grid-cols-[260px_minmax(0,1fr)]">
    <aside class="rounded-lg border border-stone-300 bg-white p-4 shadow-sm">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-stone-900">Categories</h2>
            <span class="rounded-md bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-900">{{ $totalPasswordCount }} items</span>
        </div>

        <div class="space-y-1">
            <a class="flex items-center justify-between rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-900" href="#">
                <span>Full Hoard</span>
                <span>{{ $totalPasswordCount }}</span>
            </a>
        </div>

        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm font-semibold text-amber-950">Security score</p>
            <div class="mt-3 h-2 rounded-full bg-amber-100">
                <div class="h-2 w-3/4 rounded-full bg-red-800"></div>
            </div>
            <p class="mt-3 text-sm text-amber-900">Password strength checks can be added next.</p>
        </div>
    </aside>

    <section class="space-y-5">
        <div class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase text-red-800">Main Hoard</p>
                    <h1 class="mt-1 text-3xl font-bold text-stone-950">Guard your saved passwords</h1>
                    <p class="mt-2 max-w-2xl text-stone-600">Store accounts, check weak passwords, and quickly copy login details from one clean dashboard.</p>
                </div>

                <a class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-red-800 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2" href="/passwords/create">
                    <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Add Treasure
                </a>
            </div>

            @if (session('status'))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-stone-200 p-4">
                    <p class="text-sm text-stone-500">Saved logins</p>
                    <p class="mt-2 text-2xl font-bold">{{ $totalPasswordCount }}</p>
                </div>
                <div class="rounded-lg border border-stone-200 p-4">
                    <p class="text-sm text-stone-500">Weak passwords</p>
                    <p class="mt-2 text-2xl font-bold text-amber-700">0</p>
                </div>
                <div class="rounded-lg border border-stone-200 p-4">
                    <p class="text-sm text-stone-500">Reused passwords</p>
                    <p class="mt-2 text-2xl font-bold text-red-700">0</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-stone-300 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-stone-200 p-4 lg:flex-row lg:items-center lg:justify-between">
                <label class="relative block w-full lg:max-w-sm">
                    <span class="sr-only">Search passwords</span>
                    <svg aria-hidden="true" class="absolute left-3 top-1/2 size-5 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none">
                        <path d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input class="h-11 w-full rounded-lg border border-stone-200 bg-stone-50 pl-10 pr-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="search" placeholder="Search by website or service">
                </label>

                <div class="flex gap-2">
                    <button class="h-11 rounded-lg border border-stone-200 px-4 text-sm font-semibold text-stone-700 hover:bg-stone-50">Sort</button>
                    <button class="h-11 rounded-lg border border-stone-200 px-4 text-sm font-semibold text-stone-700 hover:bg-stone-50">Filter</button>
                </div>
            </div>

            @if ($storedPasswords->isEmpty() && $sharedPasswords->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-lg font-semibold text-stone-950">No treasures saved yet</p>
                    <p class="mt-2 text-sm text-stone-600">Add your first website password to start building this hoard.</p>
                    <a class="mt-5 inline-flex h-11 items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white hover:bg-red-900" href="/passwords/create">Add Treasure</a>
                </div>
            @else
                <div class="divide-y divide-stone-200">
                    @foreach ($storedPasswords as $storedPassword)
                        <article class="grid gap-3 p-4 sm:grid-cols-[1fr_auto] sm:items-center">
                            <div class="flex min-w-0 items-center gap-3">
                                @if ($storedPassword->favicon_url)
                                    <img class="size-11 shrink-0 rounded-lg border border-stone-200 bg-stone-50 object-contain p-1" src="{{ $storedPassword->favicon_url }}" alt="">
                                @else
                                    <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-amber-100 font-bold text-amber-800">
                                        {{ mb_strtoupper(mb_substr($storedPassword->service_name, 0, 1)) }}
                                    </span>
                                @endif

                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold">{{ $storedPassword->service_name }}</h3>
                                    <a class="truncate text-sm text-stone-500 hover:text-red-800" href="{{ $storedPassword->url }}" target="_blank" rel="noreferrer">
                                        {{ $storedPassword->url }}
                                    </a>
                                    @if ($storedPassword->notes)
                                        <p class="truncate text-sm text-stone-500">{{ $storedPassword->notes }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Saved</span>
                                <a class="rounded-lg border border-stone-200 px-3 py-2 text-sm font-semibold hover:bg-stone-50" href="/passwords/{{ $storedPassword->id }}">Details</a>
                                <a class="rounded-lg border border-stone-200 px-3 py-2 text-sm font-semibold hover:bg-stone-50" href="/passwords/{{ $storedPassword->id }}/edit">Edit</a>
                            </div>
                        </article>
                    @endforeach

                    @foreach ($sharedPasswords as $storedPassword)
                        <article class="grid gap-3 bg-amber-50/40 p-4 sm:grid-cols-[1fr_auto] sm:items-center">
                            <div class="flex min-w-0 items-center gap-3">
                                @if ($storedPassword->favicon_url)
                                    <img class="size-11 shrink-0 rounded-lg border border-stone-200 bg-stone-50 object-contain p-1" src="{{ $storedPassword->favicon_url }}" alt="">
                                @else
                                    <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-amber-100 font-bold text-amber-800">
                                        {{ mb_strtoupper(mb_substr($storedPassword->service_name, 0, 1)) }}
                                    </span>
                                @endif

                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold">{{ $storedPassword->service_name }}</h3>
                                    <a class="truncate text-sm text-stone-500 hover:text-red-800" href="{{ $storedPassword->url }}" target="_blank" rel="noreferrer">
                                        {{ $storedPassword->url }}
                                    </a>
                                    <p class="truncate text-sm text-stone-500">Shared by {{ $storedPassword->user->name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-md bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Shared</span>
                                <a class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-stone-50" href="/passwords/{{ $storedPassword->id }}">Details</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layout>
