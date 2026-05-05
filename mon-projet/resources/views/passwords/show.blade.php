<x-layout title="{{ $storedPassword->service_name }} - Dragon's Hoard Password Manager" main-class="flex flex-1 items-center justify-center py-8">
    <section class="w-full max-w-4xl space-y-5">
        <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 gap-4">
                    @if ($storedPassword->favicon_url)
                        <img class="size-14 shrink-0 rounded-lg border border-stone-200 bg-stone-50 object-contain p-1" src="{{ $storedPassword->favicon_url }}" alt="">
                    @else
                        <span class="grid size-14 shrink-0 place-items-center rounded-lg bg-amber-100 text-xl font-bold text-amber-800">
                            {{ mb_strtoupper(mb_substr($storedPassword->service_name, 0, 1)) }}
                        </span>
                    @endif

                    <div class="min-w-0">
                        <p class="text-sm font-semibold uppercase text-red-800">Saved Treasure</p>
                        <h1 class="mt-1 truncate text-3xl font-bold text-stone-950">{{ $storedPassword->service_name }}</h1>
                        <a class="mt-2 block truncate text-sm text-stone-600 hover:text-red-800" href="{{ $storedPassword->url }}" target="_blank" rel="noreferrer">{{ $storedPassword->url }}</a>
                    </div>
                </div>

                <div class="flex gap-2">
                    @if ($isOwner)
                        <a class="inline-flex h-10 items-center justify-center rounded-lg border border-stone-200 px-4 text-sm font-semibold hover:bg-stone-50" href="/passwords/{{ $storedPassword->id }}/edit">Edit</a>
                    @endif
                    <a class="inline-flex h-10 items-center justify-center rounded-lg border border-stone-200 px-4 text-sm font-semibold hover:bg-stone-50" href="/">Back</a>
                </div>
            </div>

            @if (session('status'))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif
        </div>

        <div class="grid gap-5 lg:grid-cols-[1fr_280px]">
            <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-stone-950">Details</h2>

                <dl class="mt-5 space-y-4">
                    <div>
                        <dt class="text-sm font-semibold text-stone-700">Service name</dt>
                        <dd class="mt-1 text-stone-950">{{ $storedPassword->service_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-stone-700">Website URL</dt>
                        <dd class="mt-1 break-all text-stone-950">{{ $storedPassword->url }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-stone-700">Password</dt>
                        <dd class="mt-1 flex flex-col gap-2 sm:flex-row">
                            <input id="password-field" class="h-11 min-w-0 flex-1 rounded-lg border border-stone-200 bg-stone-50 px-3 font-mono text-sm text-stone-950" type="password" value="Hidden until revealed" readonly>
                            <button id="reveal-password" class="inline-flex h-11 items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white hover:bg-red-900" type="button">
                                Reveal
                            </button>
                            <button id="copy-password" class="inline-flex h-11 items-center justify-center rounded-lg border border-stone-200 px-4 text-sm font-semibold hover:bg-stone-50" type="button" disabled>
                                Copy
                            </button>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-stone-700">Notes</dt>
                        <dd class="mt-1 whitespace-pre-line text-stone-950">{{ $storedPassword->notes ?: 'No notes saved.' }}</dd>
                    </div>
                </dl>
            </div>

            @if ($isOwner)
                <div class="space-y-5">
                    <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-stone-950">Share password</h2>
                        <p class="mt-2 text-sm text-stone-600">Send an invitation to another verified user.</p>

                        <form class="mt-5 space-y-3" action="/passwords/{{ $storedPassword->id }}/shares" method="POST">
                            @csrf
                            <label class="block">
                                <span class="text-sm font-semibold text-stone-700">Recipient email</span>
                                <input class="mt-2 h-11 w-full rounded-lg border border-stone-200 bg-stone-50 px-3 text-sm outline-none focus:border-red-800 focus:bg-white focus:ring-2 focus:ring-red-100" type="email" name="recipient_email" placeholder="friend@example.com">
                                @error('recipient_email')
                                    <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                                @enderror
                            </label>
                            <button class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white hover:bg-red-900" type="submit">
                                Send Invite
                            </button>
                        </form>

                        @if ($shares->isNotEmpty())
                            <div class="mt-5 space-y-2">
                                @foreach ($shares as $share)
                                    <div class="rounded-lg border border-stone-200 p-3">
                                        <p class="truncate text-sm font-semibold text-stone-950">{{ $share->recipient_email }}</p>
                                        <p class="mt-1 text-xs font-semibold {{ $share->status === 'accepted' ? 'text-green-700' : 'text-amber-700' }}">
                                            {{ ucfirst($share->status) }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="rounded-lg border border-red-200 bg-red-50 p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-red-950">Delete entry</h2>
                        <p class="mt-2 text-sm text-red-900">This removes the saved password from your hoard.</p>

                        <form id="delete-password-form" class="mt-5" action="/passwords/{{ $storedPassword->id }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button id="open-delete-modal" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white hover:bg-red-900" type="button">
                                Delete Password
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-amber-950">Shared password</h2>
                    <p class="mt-2 text-sm text-amber-900">This entry was shared with you by {{ $storedPassword->user->name }}.</p>
                </div>
            @endif
        </div>
    </section>

    @if ($isOwner)
        <div id="delete-confirmation-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-stone-950/60 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="delete-confirmation-title">
            <div class="w-full max-w-md rounded-lg border border-red-200 bg-white p-6 shadow-xl">
                <h2 id="delete-confirmation-title" class="text-xl font-bold text-stone-950">Delete password?</h2>
                <p class="mt-3 text-sm text-stone-600">
                    This will permanently delete {{ $storedPassword->service_name }} from your hoard.
                </p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button id="cancel-delete" class="inline-flex h-10 items-center justify-center rounded-lg border border-stone-200 px-4 text-sm font-semibold hover:bg-stone-50" type="button">
                        Cancel
                    </button>
                    <button id="confirm-delete" class="inline-flex h-10 items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white hover:bg-red-900" type="button">
                        Delete Password
                    </button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            const revealButton = document.getElementById('reveal-password');
            const copyButton = document.getElementById('copy-password');
            const passwordField = document.getElementById('password-field');
            const deleteForm = document.getElementById('delete-password-form');
            const deleteModal = document.getElementById('delete-confirmation-modal');
            const openDeleteModalButton = document.getElementById('open-delete-modal');
            const cancelDeleteButton = document.getElementById('cancel-delete');
            const confirmDeleteButton = document.getElementById('confirm-delete');
            let revealedPassword = null;

            function openDeleteModal() {
                deleteModal?.classList.remove('hidden');
                deleteModal?.classList.add('flex');
                confirmDeleteButton?.focus();
            }

            function closeDeleteModal() {
                deleteModal?.classList.add('hidden');
                deleteModal?.classList.remove('flex');
                openDeleteModalButton?.focus();
            }

            revealButton?.addEventListener('click', async () => {
                if (revealedPassword) {
                    const isHidden = passwordField.type === 'password';
                    passwordField.type = isHidden ? 'text' : 'password';
                    revealButton.textContent = isHidden ? 'Hide' : 'Reveal';
                    return;
                }

                const response = await fetch('/passwords/{{ $storedPassword->id }}/reveal', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                revealedPassword = data.password;
                passwordField.value = revealedPassword;
                passwordField.type = 'text';
                copyButton.disabled = false;
                revealButton.textContent = 'Hide';
            });

            copyButton?.addEventListener('click', async () => {
                if (!revealedPassword) {
                    return;
                }

                await navigator.clipboard.writeText(revealedPassword);
                copyButton.textContent = 'Copied';
            });

            openDeleteModalButton?.addEventListener('click', openDeleteModal);
            cancelDeleteButton?.addEventListener('click', closeDeleteModal);
            confirmDeleteButton?.addEventListener('click', () => deleteForm?.submit());
            deleteModal?.addEventListener('click', (event) => {
                if (event.target === deleteModal) {
                    closeDeleteModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !deleteModal?.classList.contains('hidden')) {
                    closeDeleteModal();
                }
            });
        </script>
    @endpush
</x-layout>
