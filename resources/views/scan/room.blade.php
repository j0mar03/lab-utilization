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

        {{-- Current occupancy info & Quick Return --}}
        @if ($openTransactions->isNotEmpty())
            @php $primaryActiveTx = $openTransactions->first(); @endphp
            @if ($primaryActiveTx->isOverdue())
                <div class="mb-4 bg-red-50 border-2 border-red-300 rounded-xl p-3.5 flex items-center justify-between animate-pulse">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🚨</span>
                        <div>
                            <p class="font-bold text-red-900 text-xs">Room In Use — Overdue Session</p>
                            <p class="text-xs text-red-700">Occupied by {{ $primaryActiveTx->borrower_name }} (due {{ $primaryActiveTx->expected_return_at?->diffForHumans() }})</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-red-200 text-red-900">Overdue</span>
                </div>
            @else
                <div class="mb-4 bg-amber-50 border border-amber-300 rounded-xl p-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🟡</span>
                        <div>
                            <p class="font-bold text-amber-900 text-xs">Room Currently Occupied</p>
                            <p class="text-xs text-amber-700">In use by {{ $primaryActiveTx->borrower_name }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-200 text-amber-900">In Use</span>
                </div>
            @endif

            {{-- 1-Tap Return Card for returning room + key + accessories --}}
            <div class="mb-5 bg-gradient-to-br from-orange-50 to-amber-50 border-2 border-orange-300 rounded-2xl p-4 shadow-sm">
                <div class="flex items-start gap-2.5">
                    <span class="text-2xl">↩️</span>
                    <div class="flex-1">
                        <h3 class="font-bold text-orange-950 text-sm sm:text-base">
                            Returning this Room or Handing in Keys?
                        </h3>
                        <p class="text-xs text-orange-800 mt-0.5">
                            Borrower: <strong>{{ $primaryActiveTx->borrower_name }}</strong>
                            @if ($primaryActiveTx->subject) • <span class="italic">{{ $primaryActiveTx->subject }}</span> @endif
                        </p>

                        @if ($primaryActiveTx->items->isNotEmpty())
                            <div class="mt-2 p-2.5 bg-white/90 rounded-xl border border-orange-200/80 text-xs">
                                <span class="font-bold text-[11px] uppercase tracking-wide text-orange-700 block mb-1">
                                    📦 Equipment & Keys Returning Together:
                                </span>
                                <ul class="space-y-1">
                                    @foreach ($primaryActiveTx->items as $item)
                                        <li class="flex items-center justify-between text-gray-800">
                                            <span class="flex items-center gap-1.5">
                                                <span>{{ str_contains(strtolower($item->tool?->name ?? ''), 'key') ? '🔑' : '🔌' }}</span>
                                                <span class="font-medium">{{ $item->tool?->name ?? 'Tool' }}</span>
                                            </span>
                                            <span class="font-bold text-orange-700">×{{ $item->remaining_quantity }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('scan.room.return', $room->id) }}" class="mt-3"
                              onsubmit="return confirm('Return {{ $room->name }} and all included keys/tools now?');">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white font-bold py-3 px-4 rounded-xl text-sm shadow-sm transition flex items-center justify-center gap-2">
                                <span>✓ Vacate Room & Return All Keys / Tools</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="mb-5 bg-green-50 border border-green-300 rounded-xl p-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-base">🟢</span>
                    <div>
                        <p class="font-bold text-green-900 text-xs">Room Available</p>
                        <p class="text-xs text-green-700">Ready for class or lab utilization</p>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-green-200 text-green-900">Available</span>
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

            <form method="POST" action="{{ route('scan.room.checkout', $room->id) }}"
                  onsubmit="return validateScanForm(this)">
                @csrf

                {{-- Room Occupancy Conflict Alert & Handover Options --}}
                @if ($openTransactions->isNotEmpty())
                    @php $currTx = $openTransactions->first(); @endphp
                    <div class="mb-5 p-3.5 {{ $currTx->isOverdue() ? 'bg-red-50 border-2 border-red-300' : 'bg-amber-50 border border-amber-300' }} rounded-xl space-y-2">
                        <div class="text-xs {{ $currTx->isOverdue() ? 'text-red-900' : 'text-amber-900' }}">
                            <p class="font-bold text-sm {{ $currTx->isOverdue() ? 'text-red-800' : 'text-amber-800' }} flex items-center gap-1.5">
                                <span>{{ $currTx->isOverdue() ? '🚨 Room Session is Overdue' : '⚠️ Room Currently In Use' }}</span>
                            </p>
                            <p class="mt-1">
                                Currently registered to <strong>{{ $currTx->borrower_name }}</strong>
                                @if ($currTx->subject) for <span class="italic">{{ $currTx->subject }}</span> @endif
                                (since {{ $currTx->checked_out_at->format('g:i A') }}{{ $currTx->isOverdue() ? ', past due since ' . $currTx->expected_return_at->format('g:i A') : '' }}).
                            </p>
                        </div>
                        <div class="pt-2 border-t {{ $currTx->isOverdue() ? 'border-red-200' : 'border-amber-200' }} text-xs space-y-2">
                            <label class="flex items-center gap-2 font-semibold text-gray-900 cursor-pointer">
                                <input type="radio" name="conflict_resolution" value="end_previous" checked
                                       class="text-blue-600 focus:ring-blue-500">
                                <span>🔄 Take over room (End previous session)</span>
                            </label>
                            <label class="flex items-center gap-2 text-gray-700 cursor-pointer">
                                <input type="radio" name="conflict_resolution" value="allow_concurrent"
                                       class="text-blue-600 focus:ring-blue-500">
                                <span>👥 Share room with current user</span>
                            </label>
                        </div>
                    </div>
                @endif

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

                @if ($room->isComputerLab())
                    {{-- Software Utilized (Required for Computer Labs) --}}
                    <div class="mb-5 p-4 rounded-xl bg-purple-50 border-2 border-purple-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-bold text-purple-950 flex items-center gap-1.5">
                                <span>💻</span>
                                <span>Software & Applications Utilized</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                                Required for {{ $room->name }}
                            </span>
                        </div>
                        <p class="text-xs text-purple-700 mb-3 leading-tight">
                            Select the specialized software tools used during this computer laboratory session:
                        </p>

                        <div class="space-y-2">
                            @foreach ($softwareCatalog as $swKey => $sw)
                                <label class="flex items-start gap-3 p-2.5 rounded-xl border border-purple-200 bg-white hover:bg-purple-100/50 cursor-pointer transition">
                                    <input type="checkbox"
                                           name="software_utilized[]"
                                           value="{{ $sw['name'] }}"
                                           {{ is_array(old('software_utilized')) && in_array($sw['name'], old('software_utilized')) ? 'checked' : '' }}
                                           class="mt-1 h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-gray-900 flex items-center gap-1">
                                            <span>{{ $sw['icon'] }}</span>
                                            <span>{{ $sw['name'] }}</span>
                                        </span>
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            {{ $sw['description'] }}
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <label for="software_custom" class="block text-xs font-semibold text-purple-900 mb-1">
                                Other / Specific Software:
                            </label>
                            <input type="text"
                                   id="software_custom"
                                   name="software_custom"
                                   value="{{ old('software_custom') }}"
                                   placeholder="e.g., Blender, Proteus, Quartus Prime"
                                   class="w-full text-xs border-gray-300 rounded-xl px-3 py-2 focus:ring-purple-500 focus:border-purple-500 bg-white">
                        </div>

                        @error('software_utilized')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

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
        const scanSubjectsByDept = {{ \Illuminate\Support\Js::from($subjects->groupBy('department')->map(fn($list) => $list->map(fn($s) => ['code' => $s->code, 'name' => $s->name, 'full' => $s->code . ' - ' . $s->name, 'year_level' => $s->year_level]))) }};

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

        function validateScanForm(form) {
            @if ($room->isComputerLab())
            const checkedSw = form.querySelectorAll('input[name="software_utilized[]"]:checked');
            const customSw = form.querySelector('input[name="software_custom"]')?.value.trim();
            if (checkedSw.length === 0 && !customSw) {
                alert('Please select at least one software utilized for Computer Laboratory {{ $room->name }}.');
                return false;
            }
            @endif

            const btn = form.querySelector('button[type=submit]');
            if (btn) {
                btn.disabled = true;
                btn.innerText = '⏳ Checking Out...';
            }
            return true;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            renderScanSubjects('');
        });
    </script>
</body>
</html>
