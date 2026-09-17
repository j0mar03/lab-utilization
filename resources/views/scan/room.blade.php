<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>{{ $room->name }} — Lab Checkout</title>
    @vite(['resources/css/app.css'])
    <style>
        body { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    {{-- Header bar --}}
    <div class="bg-blue-700 text-white px-4 py-4 flex items-center gap-3">
        <span class="text-2xl">🏫</span>
        <div>
            <div class="font-bold text-lg leading-tight">{{ $room->name }}</div>
            <div class="text-blue-200 text-sm">{{ $room->location ?? 'PUP-ITECH' }}</div>
        </div>
    </div>

    <div class="px-4 py-6 max-w-lg mx-auto">

        {{-- Current occupancy info --}}
        @if ($openTransactions->isNotEmpty())
            <div class="mb-5 bg-yellow-50 border border-yellow-300 rounded-xl p-4">
                <p class="font-semibold text-yellow-800 text-sm mb-2">
                    🟡 Room currently in use
                </p>
                @foreach ($openTransactions as $tx)
                    <div class="text-sm text-yellow-700 mb-1">
                        <span class="font-medium">{{ $tx->borrower_name }}</span>
                        @if ($tx->subject) — {{ $tx->subject }} @endif
                        <span class="text-yellow-500 ml-1 text-xs">
                            (since {{ $tx->checked_out_at->format('g:i A') }})
                        </span>
                    </div>
                @endforeach
                <p class="text-xs text-yellow-600 mt-2">
                    Multiple users can share the room. Fill in your details below.
                </p>
            </div>
        @else
            <div class="mb-5 bg-green-50 border border-green-300 rounded-xl p-4">
                <p class="font-semibold text-green-800 text-sm">🟢 Room is available</p>
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

        {{-- Checkout Form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <h2 class="font-bold text-gray-800 text-lg mb-4">Check Out Room</h2>

            <form method="POST" action="{{ route('scan.room.checkout', $room->id) }}">
                @csrf

                {{-- Faculty / Borrower Selector --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Select Faculty / Borrower <span class="text-red-500">*</span>
                    </label>
                    <select id="faculty_select"
                            onchange="onFacultyScanChange(this)"
                            class="w-full text-base border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500 focus:border-blue-500 mb-2 bg-white">
                        <option value="">— Choose a Faculty Member —</option>
                        @foreach ($faculties->groupBy('department') as $dept => $members)
                            <optgroup label="{{ $dept }}">
                                @foreach ($members as $faculty)
                                    <option value="{{ $faculty->name }}"
                                            data-name="{{ $faculty->name }}"
                                            data-email="{{ $faculty->email }}"
                                            data-department="{{ $faculty->department }}"
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
                           class="w-full text-base border-gray-300 rounded-xl px-4 py-2.5 focus:ring-blue-500 focus:border-blue-500 mb-2"
                           required>

                    <input type="email"
                           id="borrower_email"
                           name="borrower_email"
                           value="{{ old('borrower_email') }}"
                           placeholder="Institutional Email (optional)"
                           class="w-full text-sm border-gray-300 rounded-xl px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- Subject with Dynamic Department Dropdown --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-gray-700">
                            Subject / Purpose <span class="text-red-500">*</span>
                        </label>
                        <span id="scan_dept_badge" class="hidden text-xs px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-medium"></span>
                    </div>

                    <select id="subject_select"
                            onchange="onSubjectScanChange(this)"
                            class="w-full text-sm border-gray-300 rounded-xl px-4 py-2.5 focus:ring-blue-500 focus:border-blue-500 mb-2 bg-white">
                    </select>

                    <input type="text"
                           id="subject"
                           name="subject"
                           value="{{ old('subject') }}"
                           placeholder="e.g. COMP 001 - Introduction to Computing"
                           class="w-full text-base border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>

                {{-- Notes (optional) --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Notes <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text"
                           name="notes"
                           value="{{ old('notes') }}"
                           placeholder="e.g. need projector"
                           class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-lg py-4 rounded-xl transition-colors">
                    ✅ Check Out Room
                </button>
            </form>
        </div>

        {{-- Wi-Fi info (if available) --}}
        @if ($room->has_wifi && $room->wifi_notes)
            <div class="mt-4 bg-blue-50 rounded-xl p-4 border border-blue-200">
                <p class="text-sm font-semibold text-blue-800 mb-1">📶 Wi-Fi</p>
                <p class="text-sm text-blue-700">{{ $room->wifi_notes }}</p>
            </div>
        @endif

        <p class="text-center text-xs text-gray-400 mt-6">
            PUP-ITECH Lab Utilization System
        </p>
    </div>

    <script>
        const scanSubjectsByDept = @json($subjects->groupBy('department')->map(fn($list) => $list->map(fn($s) => ['code' => $s->code, 'name' => $s->name, 'full' => $s->code . ' - ' . $s->name, 'year_level' => $s->year_level])));

        function renderScanSubjects(deptFilter) {
            const sel = document.getElementById('subject_select');
            const badge = document.getElementById('scan_dept_badge');
            if (!sel) return;

            sel.innerHTML = '';
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = deptFilter
                ? '— Select Subject (' + deptFilter + ') —'
                : '— Choose Subject from Pre-list (or type below) —';
            sel.appendChild(defaultOpt);

            if (deptFilter && badge) {
                badge.textContent = 'Dept: ' + deptFilter;
                badge.classList.remove('hidden');
            } else if (badge) {
                badge.classList.add('hidden');
            }

            const depts = (deptFilter && scanSubjectsByDept[deptFilter])
                ? [deptFilter]
                : Object.keys(scanSubjectsByDept);

            depts.forEach(dept => {
                const list = scanSubjectsByDept[dept];
                if (!list || !list.length) return;
                const grp = document.createElement('optgroup');
                grp.label = dept;
                list.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.full;
                    opt.textContent = item.full + (item.year_level ? ' (' + item.year_level + ')' : '');
                    grp.appendChild(opt);
                });
                sel.appendChild(grp);
            });

            const customOpt = document.createElement('option');
            customOpt.value = '__custom__';
            customOpt.textContent = '✏️ Custom / Other Subject (Type manually below)';
            sel.appendChild(customOpt);
        }

        function onFacultyScanChange(elem) {
            const opt = elem.options[elem.selectedIndex];
            const nameInput = document.getElementById('borrower_name');
            const emailInput = document.getElementById('borrower_email');

            if (!opt || !opt.value) {
                nameInput.value = '';
                emailInput.value = '';
                renderScanSubjects('');
                return;
            }

            if (opt.value === '__other__') {
                nameInput.value = '';
                emailInput.value = '';
                nameInput.focus();
                renderScanSubjects('');
                return;
            }

            nameInput.value = opt.dataset.name || opt.value;
            emailInput.value = opt.dataset.email || '';
            const dept = opt.dataset.department || '';
            renderScanSubjects(dept);
        }

        function onSubjectScanChange(elem) {
            const subjInput = document.getElementById('subject');
            if (!subjInput) return;
            if (elem.value === '__custom__') {
                subjInput.focus();
                return;
            }
            if (elem.value) {
                subjInput.value = elem.value;
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            renderScanSubjects('');
        });
    </script>
</body>
</html>
