<x-layout title="Dragon's Hoard Password Manager" main-class="grid flex-1 gap-5 py-5 lg:grid-cols-[260px_minmax(0,1fr)]">
    <aside class="rounded-lg border border-stone-300 bg-white p-4 shadow-sm">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-stone-900">Categories</h2>
            <span class="rounded-md bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-900">24 items</span>
        </div>

        <div class="space-y-1">
            <a class="flex items-center justify-between rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-900" href="#">
                <span>Full Hoard</span>
                <span>24</span>
            </a>
            <a class="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-stone-600 hover:bg-stone-100 hover:text-stone-950" href="#">
                <span>School</span>
                <span>8</span>
            </a>
            <a class="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-stone-600 hover:bg-stone-100 hover:text-stone-950" href="#">
                <span>Social</span>
                <span>6</span>
            </a>
            <a class="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-stone-600 hover:bg-stone-100 hover:text-stone-950" href="#">
                <span>Shopping</span>
                <span>5</span>
            </a>
            <a class="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-stone-600 hover:bg-stone-100 hover:text-stone-950" href="#">
                <span>Work</span>
                <span>5</span>
            </a>
        </div>

        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm font-semibold text-amber-950">Security score</p>
            <div class="mt-3 h-2 rounded-full bg-amber-100">
                <div class="h-2 w-3/4 rounded-full bg-red-800"></div>
            </div>
            <p class="mt-3 text-sm text-amber-900">18 of 24 passwords are strong.</p>
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

                <button class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-red-800 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2">
                    <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Add Treasure
                </button>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-stone-200 p-4">
                    <p class="text-sm text-stone-500">Saved logins</p>
                    <p class="mt-2 text-2xl font-bold">24</p>
                </div>
                <div class="rounded-lg border border-stone-200 p-4">
                    <p class="text-sm text-stone-500">Weak passwords</p>
                    <p class="mt-2 text-2xl font-bold text-amber-700">3</p>
                </div>
                <div class="rounded-lg border border-stone-200 p-4">
                    <p class="text-sm text-stone-500">Reused passwords</p>
                    <p class="mt-2 text-2xl font-bold text-red-700">2</p>
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
                    <input class="h-11 w-full rounded-lg border border-stone-200 bg-stone-50 pl-10 pr-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="search" placeholder="Search by website or username">
                </label>

                <div class="flex gap-2">
                    <button class="h-11 rounded-lg border border-stone-200 px-4 text-sm font-semibold text-stone-700 hover:bg-stone-50">Sort</button>
                    <button class="h-11 rounded-lg border border-stone-200 px-4 text-sm font-semibold text-stone-700 hover:bg-stone-50">Filter</button>
                </div>
            </div>

            <div class="divide-y divide-stone-200">
                <article class="grid gap-3 p-4 sm:grid-cols-[1fr_auto] sm:items-center">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-sky-100 font-bold text-sky-800">G</span>
                        <div class="min-w-0">
                            <h3 class="truncate font-semibold">Google Classroom</h3>
                            <p class="truncate text-sm text-stone-500">student.email@example.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Strong</span>
                        <button class="rounded-lg border border-stone-200 px-3 py-2 text-sm font-semibold hover:bg-stone-50">Copy</button>
                    </div>
                </article>

                <article class="grid gap-3 p-4 sm:grid-cols-[1fr_auto] sm:items-center">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-violet-100 font-bold text-violet-800">M</span>
                        <div class="min-w-0">
                            <h3 class="truncate font-semibold">Microsoft 365</h3>
                            <p class="truncate text-sm text-stone-500">school.account@example.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Strong</span>
                        <button class="rounded-lg border border-stone-200 px-3 py-2 text-sm font-semibold hover:bg-stone-50">Copy</button>
                    </div>
                </article>

                <article class="grid gap-3 p-4 sm:grid-cols-[1fr_auto] sm:items-center">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-amber-100 font-bold text-amber-800">L</span>
                        <div class="min-w-0">
                            <h3 class="truncate font-semibold">Library Portal</h3>
                            <p class="truncate text-sm text-stone-500">pbousquet</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Review</span>
                        <button class="rounded-lg border border-stone-200 px-3 py-2 text-sm font-semibold hover:bg-stone-50">Copy</button>
                    </div>
                </article>

                <article class="grid gap-3 p-4 sm:grid-cols-[1fr_auto] sm:items-center">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-rose-100 font-bold text-rose-800">N</span>
                        <div class="min-w-0">
                            <h3 class="truncate font-semibold">Netflix</h3>
                            <p class="truncate text-sm text-stone-500">family.login@example.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">Reused</span>
                        <button class="rounded-lg border border-stone-200 px-3 py-2 text-sm font-semibold hover:bg-stone-50">Copy</button>
                    </div>
                </article>
            </div>
        </div>
    </section>
</x-layout>
