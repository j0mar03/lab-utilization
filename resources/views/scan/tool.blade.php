<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>{{ $tool->name }} — Checkout</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen">

    {{-- Header --}}
    <div class="bg-indigo-700 text-white px-4 py-4 flex items-center gap-3">
        <span class="text-2xl">🔧</span>
        <div>
            <div class="font-bold text-lg leading-tight">{{ $tool->name }}</div>
            <div class="text-indigo-200 text-sm">
                {{ $tool->category }}
                @if ($tool->room) — {{ $tool->room->name }} @endif
            </div>
        </div>
    </div>

    <div class="px-4 py-6 max-w-lg mx-auto">

        {{-- Availability Status --}}
        @if ($availableQty > 0)
            <div class="mb-5 bg-green-50 border border-green-300 rounded-xl p-4">
                <p class="font-semibold text-green-800 text-sm">
                    🟢 Available:
                    <span class="text-xl font-bold">{{ $availableQty }}</span>
                    of {{ $tool->total_quantity }} unit(s)
                </p>
            </div>
        @else
            <div class="mb-5 bg-red-50 border border-red-300 rounded-xl p-4">
                <p class="font-semibold text-red-800 text-sm">
                    🔴 All {{ $tool->total_quantity }} unit(s) are currently borrowed
                </p>
                @if ($openTransactions->isNotEmpty())
                    <div class="mt-2 text-xs text-red-600 space-y-1">
                        @foreach ($openTransactions as $tx)
                            <div>{{ $tx->borrower_name }} (since {{ $tx->checked_out_at->format('g:i A') }})</div>
                        @endforeach
                    </div>
                @endif
                <p class="mt-2 text-xs text-red-600">Please check back later or contact the Lab Head.</p>
            </div>
        @endif

        {{-- Currently borrowed list (if any) --}}
        @if ($openTransactions->isNotEmpty() && $availableQty > 0)
            <div class="mb-5 bg-yellow-50 border border-yellow-300 rounded-xl p-3">
                <p class="text-xs font-semibold text-yellow-700 mb-1">Currently borrowed by:</p>
                @foreach ($openTransactions as $tx)
                    <div class="text-xs text-yellow-600">
                        {{ $tx->borrower_name }} — {{ $tx->quantity }} unit(s) since {{ $tx->checked_out_at->format('g:i A') }}
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-300 rounded-xl p-4">
                @foreach ($errors->all() as $error)
                    <p class="text-red-700 text-sm">⚠️ {{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Checkout form (only if units available) --}}
        @if ($availableQty > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <h2 class="font-bold text-gray-800 text-lg mb-4">Borrow This Tool</h2>

                <form method="POST" action="{{ route('scan.tool.checkout', $tool->id) }}">
                    @csrf

                    {{-- Faculty / Borrower Selector --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Select Faculty / Borrower <span class="text-red-500">*</span>
                        </label>
                        <select id="faculty_select"
                                onchange="
                                    const opt = this.options[this.selectedIndex];
                                    if (opt.value === '__other__') {
                                        document.getElementById('borrower_name').value = '';
                                        document.getElementById('borrower_email').value = '';
                                        document.getElementById('borrower_name').focus();
                                    } else if (opt.value) {
                                        document.getElementById('borrower_name').value = opt.dataset.name;
                                        document.getElementById('borrower_email').value = opt.dataset.email || '';
                                    } else {
                                        document.getElementById('borrower_name').value = '';
                                        document.getElementById('borrower_email').value = '';
                                    }
                                "
                                class="w-full text-base border-gray-300 rounded-xl px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500 mb-2 bg-white">
                            <option value="">— Choose a Faculty Member —</option>
                            @foreach ($faculties->groupBy('department') as $dept => $members)
                                <optgroup label="{{ $dept }}">
                                    @foreach ($members as $faculty)
                                        <option value="{{ $faculty->name }}"
                                                data-name="{{ $faculty->name }}"
                                                data-email="{{ $faculty->email }}"
                                                {{ old('borrower_name') == $faculty->name ? 'selected' : '' }}>
                                            {{ $faculty->name }} ({{ $faculty->email }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                            <option value="__other__">✏️ Other / Student Walk-in (Type Name Below)</option>
                        </select>

                        <input type="text"
                               id="borrower_name"
                               name="borrower_name"
                               value="{{ old('borrower_name') }}"
                               placeholder="Full Name (or selected above)"
                               autocomplete="name"
                               class="w-full text-base border-gray-300 rounded-xl px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500 mb-2"
                               required>

                        <input type="email"
                               id="borrower_email"
                               name="borrower_email"
                               value="{{ old('borrower_email') }}"
                               placeholder="Institutional Email (optional)"
                               class="w-full text-sm border-gray-300 rounded-xl px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    @if ($tool->total_quantity > 1)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                How many units? <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="quantity"
                                   value="{{ old('quantity', 1) }}"
                                   min="1"
                                   max="{{ $availableQty }}"
                                   class="w-32 text-lg border-gray-300 rounded-xl px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Max: {{ $availableQty }}</p>
                        </div>
                    @else
                        <input type="hidden" name="quantity" value="1">
                    @endif

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Notes <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input type="text"
                               name="notes"
                               value="{{ old('notes') }}"
                               placeholder="e.g. for room 104 demo"
                               class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-lg py-4 rounded-xl transition-colors">
                        ✅ Borrow Tool
                    </button>
                </form>
            </div>
        @endif

        <p class="text-center text-xs text-gray-400 mt-6">PUP-ITECH Lab Utilization System</p>
    </div>

</body>
</html>
