<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                ➕ New Transaction / Checkout
            </h2>
            <a href="{{ route('admin.transactions.index') }}"
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                ← Back to History
            </a>
        </div>
    </x-slot>

    <div class="py-8"
         x-data="{
             type: '{{ old('type', request('type', 'room')) }}',
             borrowerMode: '{{ old('borrower_mode', 'faculty') }}',
             borrowerName: '{{ addslashes(old('borrower_name', '')) }}',
             borrowerEmail: '{{ addslashes(old('borrower_email', '')) }}',
             selectedDepartment: '',
             deptFilter: '',
             subjectRoom: '{{ addslashes(old('subject', '')) }}',
             subjectTool: '{{ addslashes(old('tool_subject', '')) }}',
             subjectsByDept: {{ Js::from($subjects->groupBy('department')->map(fn($list) => $list->map(fn($s) => ['code' => $s->code, 'name' => $s->name, 'full' => $s->code . ' - ' . $s->name, 'year_level' => $s->year_level]))) }},
             availableTools: {{ Js::from($tools->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'category' => $t->category,
                'department' => $t->department,
                'department_short' => $t->departmentShort(),
                'available' => (int) $t->available_quantity,
                'total' => (int) $t->total_quantity,
            ])) }},
             toolRows: {{ Js::from(old('tools', [
                 ['tool_id' => old('tool_id', ''), 'quantity' => (int) old('quantity', 1)]
             ])) }},

             addToolRow() {
                 this.toolRows.push({ tool_id: '', quantity: 1 });
             },

             removeToolRow(index) {
                 if (this.toolRows.length > 1) {
                     this.toolRows.splice(index, 1);
                 }
             },

             getToolInfo(toolId) {
                 if (!toolId) return null;
                 return this.availableTools.find(t => t.id == toolId) || null;
             },

             init() {
                 this.updateSubjectDropdown('subject_room_select', this.subjectRoom);
                 this.updateSubjectDropdown('subject_tool_select', this.subjectTool);
                 this.$watch('deptFilter', () => {
                     this.updateSubjectDropdown('subject_room_select', this.subjectRoom);
                     this.updateSubjectDropdown('subject_tool_select', this.subjectTool);
                 });
             },

             updateSubjectDropdown(selectId, currentVal) {
                 const sel = document.getElementById(selectId);
                 if (!sel) return;
                 sel.innerHTML = '';

                 const defaultOpt = document.createElement('option');
                 defaultOpt.value = '';
                 defaultOpt.textContent = this.deptFilter
                     ? '— Select Subject (' + this.deptFilter + ') —'
                     : '— Choose Subject from Pre-list (or type below) —';
                 sel.appendChild(defaultOpt);

                 const depts = (this.deptFilter && this.subjectsByDept[this.deptFilter])
                     ? [this.deptFilter]
                     : Object.keys(this.subjectsByDept);

                 depts.forEach(dept => {
                     const list = this.subjectsByDept[dept];
                     if (!list || !list.length) return;
                     const grp = document.createElement('optgroup');
                     grp.label = dept;
                     list.forEach(item => {
                         const opt = document.createElement('option');
                         opt.value = item.full;
                         opt.textContent = item.full + (item.year_level ? ' (' + item.year_level + ')' : '');
                         if (item.full === currentVal) {
                             opt.selected = true;
                         }
                         grp.appendChild(opt);
                     });
                     sel.appendChild(grp);
                 });

                 const customOpt = document.createElement('option');
                 customOpt.value = '__custom__';
                 customOpt.textContent = '✏️ Custom / Other Subject (Type manually below)';
                 sel.appendChild(customOpt);
             },

             onFacultySelect(event) {
                 const opt = event.target.selectedOptions[0];
                 if (!opt || !opt.value) {
                     this.borrowerName = '';
                     this.borrowerEmail = '';
                     this.selectedDepartment = '';
                     this.deptFilter = '';
                     return;
                 }
                 if (opt.value === '__custom__') {
                     this.borrowerMode = 'custom';
                     this.borrowerName = '';
                     this.borrowerEmail = '';
                     this.selectedDepartment = '';
                     this.deptFilter = '';
                     this.$nextTick(() => document.getElementById('borrower_name').focus());
                     return;
                 }
                 this.borrowerName = opt.dataset.name || opt.value;
                 this.borrowerEmail = opt.dataset.email || '';
                 this.selectedDepartment = opt.dataset.department || '';

                 if (this.selectedDepartment && this.subjectsByDept[this.selectedDepartment]) {
                     this.deptFilter = this.selectedDepartment;
                 } else {
                     this.deptFilter = '';
                 }
             },

             onSubjectSelect(event, targetProp) {
                 const val = event.target.value;
                 if (val === '__custom__') {
                     const inputId = targetProp === 'subjectRoom' ? 'subject_room' : 'subject_tool';
                     this.$nextTick(() => document.getElementById(inputId)?.focus());
                     return;
                 }
                 if (val) {
                     this[targetProp] = val;
                 }
             },

             onRoomSelect(event) {
                 const opt = event.target.selectedOptions[0];
                 if (opt && opt.dataset.department) {
                     if (!this.selectedDepartment) {
                         this.selectedDepartment = opt.dataset.department;
                     }
                     if (!this.deptFilter && this.subjectsByDept[opt.dataset.department]) {
                         this.deptFilter = opt.dataset.department;
                     }
                 }
             }
         }">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Type Selector Tabs --}}
            <div class="flex bg-gray-100 dark:bg-gray-700 p-1.5 rounded-xl gap-2">
                <button type="button" @click="type = 'room'"
                        :class="type === 'room' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm font-bold' : 'text-gray-600 dark:text-gray-300 font-medium hover:text-gray-900'"
                        class="flex-1 py-2.5 px-4 rounded-lg text-sm flex items-center justify-center gap-2 transition">
                    <span class="text-base">🏫</span>
                    <span>Room Utilization</span>
                </button>
                <button type="button" @click="type = 'tool'"
                        :class="type === 'tool' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm font-bold' : 'text-gray-600 dark:text-gray-300 font-medium hover:text-gray-900'"
                        class="flex-1 py-2.5 px-4 rounded-lg text-sm flex items-center justify-center gap-2 transition">
                    <span class="text-base">🔧</span>
                    <span>Tool / Equipment Borrowing</span>
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <form method="POST" action="{{ route('admin.transactions.store') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="type" :value="type">
                    <input type="hidden" name="borrower_mode" :value="borrowerMode">

                    {{-- Borrower Information Section --}}
                    <div class="space-y-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-750/50 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                👤 Borrower / Faculty Information <span class="text-red-500">*</span>
                            </label>
                            <button type="button"
                                    @click="borrowerMode = (borrowerMode === 'faculty' ? 'custom' : 'faculty')"
                                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                <span x-show="borrowerMode === 'faculty'">✏️ Student / Manual Entry</span>
                                <span x-show="borrowerMode === 'custom'">📋 Choose from Faculty List</span>
                            </button>
                        </div>

                        {{-- Faculty Dropdown (Organized just like Room) --}}
                        <div x-show="borrowerMode === 'faculty'">
                            <x-input-label for="faculty_selector" value="Select Faculty Member *" />
                            <select id="faculty_selector"
                                    @change="onFacultySelect($event)"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm">
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
                                <option value="__custom__">✏️ Other / Student Walk-in (Enter Manually)</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Choose faculty to auto-fill full name, institutional email, and filter their department's subjects.
                            </p>
                        </div>

                        {{-- Borrower Full Name --}}
                        <div>
                            <x-input-label for="borrower_name" value="Borrower Name *" />
                            <x-text-input id="borrower_name" name="borrower_name" type="text" class="mt-1 block w-full"
                                          x-model="borrowerName"
                                          :value="old('borrower_name')" required
                                          placeholder="e.g., Prof. Juan Dela Cruz or Student Name" />
                            <x-input-error class="mt-2" :messages="$errors->get('borrower_name')" />
                        </div>

                        {{-- Optional Borrower Email --}}
                        <div>
                            <x-input-label for="borrower_email" value="Institutional Email / Contact (Optional)" />
                            <x-text-input id="borrower_email" name="borrower_email" type="email" class="mt-1 block w-full"
                                          x-model="borrowerEmail"
                                          :value="old('borrower_email')" placeholder="e.g., borrower@pup.edu.ph" />
                            <x-input-error class="mt-2" :messages="$errors->get('borrower_email')" />
                        </div>

                        {{-- Department --}}
                        <div>
                            <x-input-label for="department" value="Department" />
                            <select id="department" name="department" x-model="selectedDepartment"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm">
                                <option value="">— Auto-detect or Select Department —</option>
                                @foreach (\App\Models\Tool::DEPARTMENTS as $dept)
                                    <option value="{{ $dept }}" {{ old('department') === $dept ? 'selected' : '' }}>
                                        {{ $dept }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Auto-filled when selecting faculty, or auto-detected if left blank.
                            </p>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════════════ --}}
                    {{-- ROOM MODE FIELDS                                        --}}
                    {{-- ═══════════════════════════════════════════════════════ --}}
                    <div x-show="type === 'room'" class="space-y-5">
                        {{-- Room Selection --}}
                        <div>
                            <x-input-label for="room_id" value="Select Room to Use *" />
                            <select id="room_id" name="room_id"
                                    @change="onRoomSelect($event)"
                                    :disabled="type !== 'room'"
                                    :required="type === 'room'"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm">
                                <option value="">— Choose a Room —</option>
                                @foreach ($rooms->groupBy(fn($r) => $r->department ?: 'General / Shared Lecture Rooms') as $dept => $roomList)
                                    <optgroup label="{{ $dept }} ({{ $roomList->first()->departmentShort() }})">
                                        @foreach ($roomList as $r)
                                            <option value="{{ $r->id }}"
                                                    data-department="{{ $r->department }}"
                                                    {{ old('room_id') == $r->id ? 'selected' : '' }}>
                                                {{ $r->isLab() ? '🔬 ' : '📖 ' }}{{ $r->name }} — {{ $r->isLab() ? 'Laboratory Room' : 'Lecture Room' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('room_id')" />
                        </div>

                        {{-- Subject Selection & Custom Input --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <x-input-label for="subject_room_select" value="Course / Subject / Purpose *" />
                                <div class="flex items-center gap-1 text-xs">
                                    <template x-if="deptFilter">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs">
                                            <span>Showing:</span>
                                            <strong class="truncate max-w-[200px]" x-text="deptFilter"></strong>
                                            <button type="button" @click="deptFilter = ''" class="hover:text-blue-900 dark:hover:text-white font-bold ml-1" title="Show all departments">
                                                (Show All)
                                            </button>
                                        </span>
                                    </template>
                                    <template x-if="!deptFilter && selectedDepartment && subjectsByDept[selectedDepartment]">
                                        <button type="button" @click="deptFilter = selectedDepartment" class="text-blue-600 dark:text-blue-400 hover:underline">
                                            Filter by Faculty Dept
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Dynamic Subject Pre-list Dropdown --}}
                            <select id="subject_room_select"
                                    @change="onSubjectSelect($event, 'subjectRoom')"
                                    :disabled="type !== 'room'"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm">
                            </select>

                            {{-- Direct Subject Text Input --}}
                            <input type="text"
                                   id="subject_room"
                                   name="subject"
                                   x-model="subjectRoom"
                                   :disabled="type !== 'room'"
                                   :required="type === 'room'"
                                   value="{{ old('subject') }}"
                                   placeholder="e.g., COMP 001 - Introduction to Computing or Custom Purpose"
                                   class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Select from the curriculum pre-list above or type custom details (e.g. section, exam, activity).
                            </p>
                            <x-input-error class="mt-1" :messages="$errors->get('subject')" />
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════════════ --}}
                    {{-- TOOL MODE FIELDS (MULTI-TOOL BORROWING)                  --}}
                    {{-- ═══════════════════════════════════════════════════════ --}}
                    <div x-show="type === 'tool'" class="space-y-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                    <span>🔧 Borrowed Tools & Equipment</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 font-medium">
                                        Multi-item supported
                                    </span>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Add one or more tools for this transaction. Quantities will be deducted from active stock.
                                </p>
                            </div>
                            <button type="button" @click="addToolRow()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg text-xs font-semibold transition">
                                <span>➕ Add Another Tool</span>
                            </button>
                        </div>

                        {{-- Fallback single-tool values for legacy readers --}}
                        <input type="hidden" name="tool_id" :value="toolRows[0]?.tool_id || ''" :disabled="type !== 'tool'">
                        <input type="hidden" name="quantity" :value="toolRows[0]?.quantity || 1" :disabled="type !== 'tool'">

                        {{-- List of Tool Rows --}}
                        <div class="space-y-3">
                            <template x-for="(row, index) in toolRows" :key="index">
                                <div class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/60 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400" x-text="'Item #' + (index + 1)"></span>
                                        <template x-if="toolRows.length > 1">
                                            <button type="button" @click="removeToolRow(index)"
                                                    class="text-red-500 hover:text-red-700 text-xs flex items-center gap-1 px-2 py-0.5 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                                                <span>✕ Remove</span>
                                            </button>
                                        </template>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        {{-- Tool Dropdown (spans 2 cols) --}}
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Tool / Equipment *
                                            </label>
                                            <select :name="'tools[' + index + '][tool_id]'"
                                                    x-model="row.tool_id"
                                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm"
                                                    :disabled="type !== 'tool'"
                                                    :required="type === 'tool'">
                                                <option value="">— Select Tool / Equipment —</option>
                                                @foreach ($tools->groupBy(fn($t) => $t->department ?: 'General / Shared Inventory') as $deptName => $deptTools)
                                                    <optgroup label="{{ $deptName }}">
                                                        @foreach ($deptTools as $t)
                                                            <option value="{{ $t->id }}" {{ $t->available_quantity <= 0 ? 'disabled' : '' }}>
                                                                {{ $t->name }} ({{ $t->category }}) — {{ $t->available_quantity }}/{{ $t->total_quantity }} avail{{ $t->available_quantity <= 0 ? ' (Out of stock)' : '' }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Quantity Input (spans 1 col) --}}
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Quantity *
                                                <span class="text-[11px] font-normal text-gray-500" x-show="row.tool_id">
                                                    (Max: <strong x-text="getToolInfo(row.tool_id)?.available ?? 1"></strong>)
                                                </span>
                                            </label>
                                            <input type="number"
                                                   :name="'tools[' + index + '][quantity]'"
                                                   x-model.number="row.quantity"
                                                   min="1"
                                                   :max="getToolInfo(row.tool_id)?.available || 999"
                                                   class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm"
                                                   :disabled="type !== 'tool'"
                                                   :required="type === 'tool'">
                                        </div>
                                    </div>

                                    {{-- Stock indicator / warning --}}
                                    <template x-if="row.tool_id">
                                        <div class="text-xs flex items-center justify-between pt-1">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <template x-if="getToolInfo(row.tool_id)?.department_short">
                                                    <span class="px-1.5 py-0.5 rounded text-[11px] font-semibold bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                                                          x-text="'Dept: ' + getToolInfo(row.tool_id).department_short"></span>
                                                </template>
                                                <template x-if="getToolInfo(row.tool_id) && row.quantity > getToolInfo(row.tool_id).available">
                                                    <span class="text-red-600 font-medium">
                                                        ⚠️ Warning: Requested quantity exceeds currently available stock (<span x-text="getToolInfo(row.tool_id).available"></span>)!
                                                    </span>
                                                </template>
                                                <template x-if="getToolInfo(row.tool_id) && row.quantity <= getToolInfo(row.tool_id).available">
                                                    <span class="text-emerald-600 dark:text-emerald-400">
                                                        ✓ Stock verified (<span x-text="getToolInfo(row.tool_id).available"></span> available in inventory)
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Add button at bottom & summary --}}
                        <div class="flex items-center justify-between pt-1">
                            <button type="button" @click="addToolRow()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-dashed border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg text-xs font-semibold transition">
                                <span>➕ Add Another Tool</span>
                            </button>
                            <span class="text-xs text-gray-500">
                                Total: <strong x-text="toolRows.length"></strong> tool line(s)
                            </span>
                        </div>

                        <x-input-error class="mt-2" :messages="$errors->get('tools')" />
                        <x-input-error class="mt-2" :messages="$errors->get('tool_id')" />
                        <x-input-error class="mt-2" :messages="$errors->get('quantity')" />

                        {{-- Subject (Optional for tools) --}}
                        <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                            <x-input-label for="subject_tool_select" value="Subject / Activity (Optional)" />
                            <select id="subject_tool_select"
                                    @change="onSubjectSelect($event, 'subjectTool')"
                                    :disabled="type !== 'tool'"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm">
                            </select>
                            <input type="text"
                                   id="subject_tool"
                                   name="tool_subject"
                                   x-model="subjectTool"
                                   :disabled="type !== 'tool'"
                                   value="{{ old('tool_subject') }}"
                                   placeholder="e.g., Electronics Laboratory Experiment #3"
                                   class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm">
                            <x-input-error class="mt-1" :messages="$errors->get('tool_subject')" />
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════════════ --}}
                    {{-- MANUAL TIME ENTRY (LOGBOOK & FORGOTTEN SESSIONS)       --}}
                    {{-- ═══════════════════════════════════════════════════════ --}}
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-750/50 border border-gray-200 dark:border-gray-700 space-y-3"
                         x-data="{
                             timeIn: '{{ old('time_in', now()->format('Y-m-d\TH:i')) }}',
                             timeOut: '{{ old('time_out', '') }}',
                             setNow() {
                                 const now = new Date();
                                 const offset = now.getTimezoneOffset() * 60000;
                                 this.timeIn = (new Date(now - offset)).toISOString().slice(0, 16);
                             },
                             setHourAgo(h) {
                                 const d = new Date();
                                 d.setHours(d.getHours() - h);
                                 const offset = d.getTimezoneOffset() * 60000;
                                 this.timeIn = (new Date(d - offset)).toISOString().slice(0, 16);
                             },
                             setYesterday() {
                                 const d = new Date();
                                 d.setDate(d.getDate() - 1);
                                 const offset = d.getTimezoneOffset() * 60000;
                                 this.timeIn = (new Date(d - offset)).toISOString().slice(0, 16);
                             },
                             setTimeOutRelative(h) {
                                 const base = this.timeIn ? new Date(this.timeIn) : new Date();
                                 base.setHours(base.getHours() + h);
                                 const offset = base.getTimezoneOffset() * 60000;
                                 this.timeOut = (new Date(base - offset)).toISOString().slice(0, 16);
                             },
                             clearTimeOut() {
                                 this.timeOut = '';
                             }
                         }">

                        <div class="flex items-center justify-between">
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                ⏱️ Time In & Time Out (Logbook / Forgotten Session)
                            </label>
                            <div>
                                <span x-show="timeOut" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                    ✓ Completed (Mark as Returned)
                                </span>
                                <span x-show="!timeOut" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                    ⚡ Active (Currently In-Use)
                                </span>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Log past/forgotten sessions directly into the logbook. Set <strong>Time Out</strong> to record as already returned, or leave it blank to keep active.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            {{-- Time In --}}
                            <div>
                                <x-input-label for="time_in" value="Time In / Borrowed At *" />
                                <input type="datetime-local" id="time_in" name="time_in"
                                       x-model="timeIn"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm"
                                       required>
                                <div class="flex items-center gap-1 mt-1.5 flex-wrap">
                                    <span class="text-[11px] text-gray-400">Presets:</span>
                                    <button type="button" @click="setNow()" class="px-1.5 py-0.5 text-[11px] bg-white dark:bg-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 rounded border border-gray-300 dark:border-gray-600">
                                        Now
                                    </button>
                                    <button type="button" @click="setHourAgo(1)" class="px-1.5 py-0.5 text-[11px] bg-white dark:bg-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 rounded border border-gray-300 dark:border-gray-600">
                                        -1h
                                    </button>
                                    <button type="button" @click="setHourAgo(2)" class="px-1.5 py-0.5 text-[11px] bg-white dark:bg-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 rounded border border-gray-300 dark:border-gray-600">
                                        -2h
                                    </button>
                                    <button type="button" @click="setYesterday()" class="px-1.5 py-0.5 text-[11px] bg-white dark:bg-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 rounded border border-gray-300 dark:border-gray-600">
                                        Yesterday
                                    </button>
                                </div>
                                <x-input-error class="mt-1" :messages="$errors->get('time_in')" />
                            </div>

                            {{-- Time Out --}}
                            <div>
                                <div class="flex items-center justify-between">
                                    <x-input-label for="time_out" value="Time Out / Returned At (Optional)" />
                                    <button type="button" x-show="timeOut" @click="clearTimeOut()" class="text-[11px] text-red-500 hover:underline">
                                        Clear (Leave Open)
                                    </button>
                                </div>
                                <input type="datetime-local" id="time_out" name="time_out"
                                       x-model="timeOut"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm">
                                <div class="flex items-center gap-1 mt-1.5 flex-wrap">
                                    <span class="text-[11px] text-gray-400">Quick Return:</span>
                                    <button type="button" @click="setTimeOutRelative(1)" class="px-1.5 py-0.5 text-[11px] bg-white dark:bg-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 rounded border border-gray-300 dark:border-gray-600">
                                        +1h
                                    </button>
                                    <button type="button" @click="setTimeOutRelative(2)" class="px-1.5 py-0.5 text-[11px] bg-white dark:bg-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 rounded border border-gray-300 dark:border-gray-600">
                                        +2h
                                    </button>
                                    <button type="button" @click="setTimeOutRelative(3)" class="px-1.5 py-0.5 text-[11px] bg-white dark:bg-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 rounded border border-gray-300 dark:border-gray-600">
                                        +3h
                                    </button>
                                    <button type="button" @click="timeOut = (new Date(Date.now() - (new Date()).getTimezoneOffset()*60000)).toISOString().slice(0, 16)" class="px-1.5 py-0.5 text-[11px] bg-white dark:bg-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 rounded border border-gray-300 dark:border-gray-600">
                                        Now
                                    </button>
                                </div>
                                <x-input-error class="mt-1" :messages="$errors->get('time_out')" />
                            </div>
                        </div>
                    </div>

                    {{-- Duration Preset --}}
                    <div>
                        <x-input-label for="duration_hours" value="Expected Duration (Hours)" />
                        <div class="flex items-center gap-3 mt-1">
                            <x-text-input id="duration_hours" name="duration_hours" type="number" min="1" max="24"
                                          class="block w-28" :value="old('duration_hours', 3)" />
                            <span class="text-sm text-gray-500 dark:text-gray-400">hours</span>
                            <div class="flex gap-1.5">
                                <button type="button" @click="document.getElementById('duration_hours').value = 1"
                                        class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-750 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded border border-gray-200 dark:border-gray-600">
                                    1h
                                </button>
                                <button type="button" @click="document.getElementById('duration_hours').value = 2"
                                        class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-750 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded border border-gray-200 dark:border-gray-600">
                                    2h
                                </button>
                                <button type="button" @click="document.getElementById('duration_hours').value = 3"
                                        class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-750 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded border border-gray-200 dark:border-gray-600">
                                    3h (Standard)
                                </button>
                                <button type="button" @click="document.getElementById('duration_hours').value = 5"
                                        class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-750 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded border border-gray-200 dark:border-gray-600">
                                    5h (Half Day)
                                </button>
                            </div>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('duration_hours')" />
                    </div>

                    {{-- Notes --}}
                    <div>
                        <x-input-label for="notes" value="Notes / Remarks (Optional)" />
                        <textarea id="notes" name="notes" rows="2"
                                  class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm"
                                  placeholder="e.g., Borrower presented ID, special equipment requested...">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('admin.transactions.index') }}"
                           class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                            Cancel
                        </a>
                        <x-primary-button>
                            Record Checkout
                        </x-primary-button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
