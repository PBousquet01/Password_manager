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

        <link rel="icon" type="image/png" href="/icon.PNG">
        <link rel="apple-touch-icon" href="/icon.PNG">

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
                    <img class="size-11 rounded-lg object-cover shadow-sm" src="/icon.PNG" alt="" aria-hidden="true">
                    <span>
                        <span class="block text-xl font-bold">Dragon's Hoard</span>
                        <span class="block text-sm text-stone-600">Password Manager</span>
                    </span>
                </a>

                <nav class="flex flex-wrap items-center gap-2 text-sm font-medium text-stone-600">
                    <a class="rounded-lg px-3 py-2 text-stone-950 hover:bg-white" href="/">Hoard</a>
                    <a class="rounded-lg px-3 py-2 hover:bg-white hover:text-stone-950" href="/generator">Generator</a>
                    <a class="rounded-lg px-3 py-2 hover:bg-white hover:text-stone-950" href="#">Security</a>
                    <a class="rounded-lg px-3 py-2 hover:bg-white hover:text-stone-950" href="/settings">Settings</a>

                    @guest
                        <a class="rounded-lg bg-red-800 px-3 py-2 text-white hover:bg-red-900" href="/login">Login</a>
                        <a class="rounded-lg border border-stone-300 px-3 py-2 text-stone-700 hover:bg-white hover:text-stone-950" href="/register">Register</a>
                    @endguest

                    @auth
                        <span class="rounded-lg bg-amber-100 px-3 py-2 font-semibold text-amber-950">
                            {{ Auth::user()->name }}
                        </span>
                        <form action="/logout" method="POST">
                            @csrf
                            <button class="rounded-lg bg-red-800 px-3 py-2 text-white hover:bg-red-900" type="submit">
                                Logout
                            </button>
                        </form>
                    @endauth
                </nav>
            </header>

            <main class="{{ $mainClass }}">
                {{ $slot }}
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
