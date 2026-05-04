@props([
    'title' => "Dragon's Hoard Password Manager",
    'mainClass' => '',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        @stack('stylesheets')
    </head>
    <body class="min-h-screen bg-stone-100 font-sans text-stone-950 antialiased">
        <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-4 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-4 border-b border-stone-300 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="/" class="flex items-center gap-3">
                    <span class="grid size-11 place-items-center rounded-lg bg-red-800 text-amber-100 shadow-sm">
                        <svg aria-hidden="true" class="size-6" viewBox="0 0 24 24" fill="none">
                            <path d="M12 3 5 6v5c0 4.4 2.9 8.5 7 10 4.1-1.5 7-5.6 7-10V6l-7-3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M9.5 12.5 12 9l2.5 3.5A3 3 0 1 1 9.5 12.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span>
                        <span class="block text-xl font-bold">Dragon's Hoard</span>
                        <span class="block text-sm text-stone-600">Password Manager</span>
                    </span>
                </a>

                <nav class="flex flex-wrap items-center gap-2 text-sm font-medium text-stone-600">
                    <a class="rounded-lg px-3 py-2 text-stone-950 hover:bg-white" href="/">Hoard</a>
                    <a class="rounded-lg px-3 py-2 hover:bg-white hover:text-stone-950" href="#">Generator</a>
                    <a class="rounded-lg px-3 py-2 hover:bg-white hover:text-stone-950" href="#">Security</a>
                    <a class="rounded-lg px-3 py-2 hover:bg-white hover:text-stone-950" href="#">Settings</a>
                </nav>
            </header>

            <main class="{{ $mainClass }}">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
