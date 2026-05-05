<x-layout title="Password Generator - Dragon's Hoard Password Manager" main-class="flex flex-1 items-center justify-center py-8">
    <section class="grid w-full max-w-5xl gap-5 lg:grid-cols-[1fr_460px] lg:items-start">
        <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm lg:p-8">
            <p class="text-sm font-semibold uppercase text-red-800">Generator</p>
            <h1 class="mt-2 text-3xl font-bold text-stone-950">Forge a strong password</h1>
            <p class="mt-3 max-w-xl text-stone-600">
                Choose the password length and character groups. The generator uses browser crypto and estimates entropy from the selected character set.
            </p>

            <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-950">Default settings</p>
                <p class="mt-2 text-sm text-amber-900">
                    Symbols, numbers, uppercase, and lowercase are enabled by default. Length starts at 12 characters.
                </p>
            </div>
        </div>

        <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <p class="text-sm font-semibold uppercase text-red-800">Generated Password</p>
                <h2 class="mt-1 text-2xl font-bold text-stone-950">Ready to copy</h2>
            </div>

            <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
                <output id="generated-password" class="block min-h-8 break-all font-mono text-lg font-semibold text-stone-950"></output>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <button id="generate-password" class="inline-flex h-11 items-center justify-center rounded-lg bg-red-800 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2" type="button">
                    Generate
                </button>
                <button id="copy-password" class="inline-flex h-11 items-center justify-center rounded-lg border border-stone-200 px-4 text-sm font-semibold hover:bg-stone-50" type="button">
                    Copy
                </button>
            </div>

            <div class="mt-6 rounded-lg border border-stone-200 p-4">
                <div class="flex items-center justify-between gap-3">
                    <label class="text-sm font-semibold text-stone-700" for="password-size">Size</label>
                    <output id="size-output" class="rounded-md bg-amber-100 px-2 py-1 text-sm font-semibold text-amber-950">12</output>
                </div>
                <input id="password-size" class="mt-3 w-full accent-red-800" type="range" min="8" max="64" value="12">
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <label class="flex items-center justify-between gap-3 rounded-lg border border-stone-200 p-3 text-sm font-semibold text-stone-700">
                    <span>Symbols</span>
                    <input id="include-symbols" class="size-4 rounded border-stone-300 text-red-800 focus:ring-red-800" type="checkbox" checked>
                </label>

                <label class="flex items-center justify-between gap-3 rounded-lg border border-stone-200 p-3 text-sm font-semibold text-stone-700">
                    <span>Numbers</span>
                    <input id="include-numbers" class="size-4 rounded border-stone-300 text-red-800 focus:ring-red-800" type="checkbox" checked>
                </label>

                <label class="flex items-center justify-between gap-3 rounded-lg border border-stone-200 p-3 text-sm font-semibold text-stone-700">
                    <span>Uppercase</span>
                    <input id="include-uppercase" class="size-4 rounded border-stone-300 text-red-800 focus:ring-red-800" type="checkbox" checked>
                </label>

                <label class="flex items-center justify-between gap-3 rounded-lg border border-stone-200 p-3 text-sm font-semibold text-stone-700">
                    <span>Lowercase</span>
                    <input id="include-lowercase" class="size-4 rounded border-stone-300 text-red-800 focus:ring-red-800" type="checkbox" checked>
                </label>
            </div>

            <div id="entropy-panel" class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 transition-colors">
                <p id="entropy-title" class="text-sm font-semibold text-red-950 transition-colors">Entropy</p>
                <p id="entropy-score" class="mt-1 text-2xl font-bold text-red-800 transition-colors"><span id="entropy-value">0</span> bits</p>
                <p id="entropy-label" class="mt-1 text-sm text-red-900 transition-colors"></p>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            const groups = {
                lowercase: 'abcdefghijklmnopqrstuvwxyz',
                uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                numbers: '0123456789',
                symbols: '!@#$%^&*()-_=+[]{};:,.<>?/',
            };

            const passwordOutput = document.getElementById('generated-password');
            const sizeInput = document.getElementById('password-size');
            const sizeOutput = document.getElementById('size-output');
            const entropyValue = document.getElementById('entropy-value');
            const entropyLabel = document.getElementById('entropy-label');
            const entropyPanel = document.getElementById('entropy-panel');
            const entropyTitle = document.getElementById('entropy-title');
            const entropyScore = document.getElementById('entropy-score');
            const copyButton = document.getElementById('copy-password');
            const controls = [
                document.getElementById('include-symbols'),
                document.getElementById('include-numbers'),
                document.getElementById('include-uppercase'),
                document.getElementById('include-lowercase'),
            ];

            function selectedGroups() {
                return [
                    document.getElementById('include-lowercase').checked ? groups.lowercase : '',
                    document.getElementById('include-uppercase').checked ? groups.uppercase : '',
                    document.getElementById('include-numbers').checked ? groups.numbers : '',
                    document.getElementById('include-symbols').checked ? groups.symbols : '',
                ].filter(Boolean);
            }

            function randomIndex(max) {
                const values = new Uint32Array(1);
                crypto.getRandomValues(values);
                return values[0] % max;
            }

            function shuffle(chars) {
                for (let index = chars.length - 1; index > 0; index--) {
                    const swapIndex = randomIndex(index + 1);
                    [chars[index], chars[swapIndex]] = [chars[swapIndex], chars[index]];
                }

                return chars;
            }

            function entropyBits(length, charsetSize) {
                if (charsetSize === 0) {
                    return 0;
                }

                return length * Math.log2(charsetSize);
            }

            function labelForEntropy(bits) {
                if (bits >= 100) {
                    return 'Very strong';
                }

                if (bits >= 70) {
                    return 'Strong';
                }

                if (bits >= 50) {
                    return 'Moderate';
                }

                return 'Weak';
            }

            function colorForEntropy(bits) {
                if (bits >= 100) {
                    return {
                        panel: 'mt-6 rounded-lg border border-[#d4af37] bg-[#fff8dc] p-4 transition-colors',
                        title: 'text-sm font-semibold text-[#5f4600] transition-colors',
                        score: 'mt-1 text-2xl font-bold text-[#b8860b] transition-colors',
                        label: 'mt-1 text-sm text-[#745600] transition-colors',
                    };
                }

                if (bits >= 70) {
                    return {
                        panel: 'mt-6 rounded-lg border border-green-300 bg-green-50 p-4 transition-colors',
                        title: 'text-sm font-semibold text-green-950 transition-colors',
                        score: 'mt-1 text-2xl font-bold text-green-700 transition-colors',
                        label: 'mt-1 text-sm text-green-900 transition-colors',
                    };
                }

                if (bits >= 50) {
                    return {
                        panel: 'mt-6 rounded-lg border border-yellow-300 bg-yellow-50 p-4 transition-colors',
                        title: 'text-sm font-semibold text-yellow-950 transition-colors',
                        score: 'mt-1 text-2xl font-bold text-yellow-700 transition-colors',
                        label: 'mt-1 text-sm text-yellow-900 transition-colors',
                    };
                }

                return {
                    panel: 'mt-6 rounded-lg border border-red-200 bg-red-50 p-4 transition-colors',
                    title: 'text-sm font-semibold text-red-950 transition-colors',
                    score: 'mt-1 text-2xl font-bold text-red-800 transition-colors',
                    label: 'mt-1 text-sm text-red-900 transition-colors',
                };
            }

            function updateEntropyColor(bits) {
                const color = colorForEntropy(bits);
                entropyPanel.className = color.panel;
                entropyTitle.className = color.title;
                entropyScore.className = color.score;
                entropyLabel.className = color.label;
            }

            function generatePassword() {
                const activeGroups = selectedGroups();
                const length = Number(sizeInput.value);
                sizeOutput.textContent = length;

                if (activeGroups.length === 0) {
                    passwordOutput.textContent = 'Choose at least one option';
                    entropyValue.textContent = '0';
                    entropyLabel.textContent = 'No character set selected';
                    updateEntropyColor(0);
                    copyButton.disabled = true;
                    return;
                }

                const charset = activeGroups.join('');
                const chars = activeGroups.map((group) => group[randomIndex(group.length)]);

                while (chars.length < length) {
                    chars.push(charset[randomIndex(charset.length)]);
                }

                const password = shuffle(chars).join('');
                const bits = entropyBits(length, charset.length);

                passwordOutput.textContent = password;
                entropyValue.textContent = bits.toFixed(1);
                entropyLabel.textContent = `${labelForEntropy(bits)} with ${charset.length} possible characters per position.`;
                updateEntropyColor(bits);
                copyButton.disabled = false;
                copyButton.textContent = 'Copy';
            }

            document.getElementById('generate-password')?.addEventListener('click', generatePassword);
            sizeInput?.addEventListener('input', generatePassword);
            controls.forEach((control) => control?.addEventListener('change', generatePassword));

            copyButton?.addEventListener('click', async () => {
                if (!passwordOutput.textContent || copyButton.disabled) {
                    return;
                }

                await navigator.clipboard.writeText(passwordOutput.textContent);
                copyButton.textContent = 'Copied';
            });

            generatePassword();
        </script>
    @endpush
</x-layout>
