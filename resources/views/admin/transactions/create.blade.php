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
             roomToolRows: {{ Js::from(old('room_tools', [])) }},
             selectedRoomId: '{{ old('room_id', request('room_id', '')) }}',
             occupiedRooms: {{ Js::from($rooms->filter(fn($r) => $r->isOccupied())->mapWithKeys(fn($r) => [
                 $r->id => [
                     'id' => $r->id,
                     'name' => $r->name,
                     'borrower_name' => $r->currentTransaction()?->borrower_name,
                     'subject' => $r->currentTransaction()?->subject,
                     'checked_out_at' => $r->currentTransaction()?->checked_out_at?->format('M d, g:i A'),
                     'transaction_id' => $r->currentTransaction()?->id,
                 ]
             ])) }},
             get currentOccupancy() {
                 return this.selectedRoomId && this.occupiedRooms[this.selectedRoomId] ? this.occupiedRooms[this.selectedRoomId] : null;
             },

             addToolRow() {
                 this.toolRows.push({ tool_id: '', quantity: 1 });
             },

             removeToolRow(index) {
                 if (this.toolRows.length > 1) {
                     this.toolRows.splice(index, 1);
                 }
             },

             addRoomToolRow(toolId = '') {
                 this.roomToolRows.push({ tool_id: toolId, quantity: 1 });
             },

             removeRoomToolRow(index) {
                 this.roomToolRows.splice(index, 1);
             },

             quickAddRoomTool(toolNameKeyword) {
                 const kw = toolNameKeyword.toLowerCase();
                 const found = this.availableTools.find(t => 
                     t.available > 0 && (
                         t.name.toLowerCase().includes(kw) || 
                         (t.category && t.category.toLowerCase().includes(kw))
                     )
                 ) || this.availableTools.find(t => 
                     t.name.toLowerCase().includes(kw) || 
                     (t.category && t.category.toLowerCase().includes(kw))
                 );

                 if (found) {
                     const existing = this.roomToolRows.find(r => r.tool_id == found.id);
                     if (existing) {
                         if (existing.quantity < found.available) {
                             existing.quantity++;
                         }
                     } else {
                         this.addRoomToolRow(found.id);
                     }
                 } else {
                     this.addRoomToolRow('');
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
                 this.selectedRoomId = event.target.value;
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
                                    x-model="selectedRoomId"
                                    @change="onRoomSelect($event)"
                                    :disabled="type !== 'room'"
                                    :required="type === 'room'"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm text-sm">
                                <option value="">— Choose a Room —</option>
                                @foreach ($rooms->groupBy(fn($r) => $r->department ?: 'General / Shared Lecture Rooms') as $dept => $roomList)
                                    <optgroup label="{{ $dept }} ({{ $roomList->first()->departmentShort() }})">
                                        @foreach ($roomList as $r)
                                            @php
                                                $isOcc = $r->isOccupied();
                                                $curr = $r->currentTransaction();
                                            @endphp
                                            <option value="{{ $r->id }}"
                                                    data-department="{{ $r->department }}"
                                                    {{ old('room_id', request('room_id')) == $r->id ? 'selected' : '' }}>
                                                {{ $isOcc ? '🔴 [IN USE: ' . Str::limit($curr?->borrower_name ?? 'Active', 18) . '] ' : ($r->isLab() ? '🔬 ' : '📖 ') }}{{ $r->name }} — {{ $r->isLab() ? 'Laboratory' : 'Lecture' }}{{ $isOcc ? ' (Occupied)' : ' (Available)' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('room_id')" />

                            {{-- Real-time Occupancy Conflict Alert --}}
                            <template x-if="currentOccupancy">
                                <div class="mt-2.5 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-700/60 space-y-2.5">
                                    <div class="flex items-start gap-2.5">
                                        <span class="text-xl">⚠️</span>
                                        <div class="text-xs text-amber-900 dark:text-amber-200">
                                            <p class="font-bold text-sm text-amber-800 dark:text-amber-300 flex items-center gap-2">
                                                <span>Room is Currently In Use</span>
                                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-200 dark:bg-amber-900 text-amber-900 dark:text-amber-200 font-bold">
                                                    Session #<span x-text="currentOccupancy.transaction_id"></span>
                                                </span>
                                            </p>
                                            <p class="mt-0.5">
                                                Currently checked out to <strong class="font-semibold" x-text="currentOccupancy.borrower_name"></strong>
                                                <template x-if="currentOccupancy.subject">
                                                    <span>for <span class="italic font-medium" x-text="currentOccupancy.subject"></span></span>
                                                </template>
                                                (since <span x-text="currentOccupancy.checked_out_at"></span>).
                                            </p>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-amber-200 dark:border-amber-800/60 space-y-1.5">
                                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-amber-950 dark:text-amber-200">
                                            <input type="radio" name="conflict_resolution" value="end_previous" checked
                                                   class="text-amber-600 focus:ring-amber-500">
                                            <span>🔄 Automatically End & Return previous session (Clean handover to new instructor)</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-xs text-amber-800 dark:text-amber-300">
                                            <input type="radio" name="conflict_resolution" value="allow_concurrent"
                                                   class="text-amber-600 focus:ring-amber-500">
                                            <span>👥 Allow Concurrent / Shared Room Occupancy (Both sessions will remain active)</span>
                                        </label>
                                    </div>
                                </div>
                            </template>
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

                        {{-- ── Software Utilized in Computer Laboratories ────── --}}
                        <div class="p-4 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">💻</span>
                                    <div>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-purple-900 dark:text-purple-300">
                                            Software & Applications Utilized (Optional)
                                        </h4>
                                        <p class="text-xs text-purple-700/80 dark:text-purple-400">
                                            For computer lab sessions, select the software tools utilized for research & OPCR reporting.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                                @foreach ($softwareCatalog as $swKey => $sw)
                                    <label class="flex items-start gap-2.5 p-2.5 rounded-lg border border-purple-200/80 dark:border-purple-800/60 bg-white dark:bg-gray-800 hover:bg-purple-50 dark:hover:bg-purple-900/30 cursor-pointer transition">
                                        <input type="checkbox"
                                               name="software_utilized[]"
                                               value="{{ $sw['name'] }}"
                                               :disabled="type !== 'room'"
                                               {{ is_array(old('software_utilized')) && in_array($sw['name'], old('software_utilized')) ? 'checked' : '' }}
                                               class="mt-1 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <div class="text-xs">
                                            <span class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                                <span>{{ $sw['icon'] }}</span>
                                                <span>{{ $sw['name'] }}</span>
                                            </span>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-tight">
                                                {{ $sw['description'] }}
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <div class="pt-1">
                                <label for="software_custom" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Other / Specialized Software (Optional):
                                </label>
                                <input type="text"
                                       id="software_custom"
                                       name="software_custom"
                                       :disabled="type !== 'room'"
                                       value="{{ old('software_custom') }}"
                                       placeholder="e.g., Blender, Proteus 8, Wireshark, Quartus Prime (separate with comma)"
                                       class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm text-xs">
                            </div>
                        </div>

                        {{-- ── Tools & Accessories Borrowed with this Room ──────────────── --}}
                        <div class="p-4 rounded-xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/60 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">📦</span>
                                    <div>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-300 flex items-center gap-2">
                                            <span>Borrow Tools & Accessories for this Room (Optional)</span>
                                            <template x-if="roomToolRows.length > 0">
                                                <span class="px-2 py-0.5 rounded-full bg-amber-200 dark:bg-amber-900 text-amber-900 dark:text-amber-200 text-[10px] font-bold"
                                                      x-text="roomToolRows.length + ' item' + (roomToolRows.length > 1 ? 's' : '')"></span>
                                            </template>
                                        </h4>
                                        <p class="text-xs text-amber-700/80 dark:text-amber-400">
                                            Need HDMI cable, projector remote, TV pen, clicker, or keys for this room session?
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="addRoomToolRow()"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-100 hover:bg-amber-200 dark:bg-amber-900/50 dark:hover:bg-amber-800/60 text-amber-800 dark:text-amber-300 rounded-lg text-xs font-semibold shadow-sm transition">
                                    <span>➕ Add Tool</span>
                                </button>
                            </div>

                            {{-- Quick-Add Shortcut Chips --}}
                            <div>
                                <span class="text-[11px] font-semibold text-amber-800 dark:text-amber-400 block mb-1.5">⚡ 1-Click Quick Add:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" @click="quickAddRoomTool('HDMI')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                                        <span>🔌 HDMI Cable</span>
                                    </button>
                                    <button type="button" @click="quickAddRoomTool('REMOTE')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                                        <span>📱 Projector / TV Remote</span>
                                    </button>
                                    <button type="button" @click="quickAddRoomTool('PROJECTOR')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                                        <span>📽️ Projector</span>
                                    </button>
                                    <button type="button" @click="quickAddRoomTool('EXTENSION')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                                        <span>🔌 Extension Cord</span>
                                    </button>
                                    <button type="button" @click="quickAddRoomTool('PRESENTER')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                                        <span>🖱️ Presenter / Clicker</span>
                                    </button>
                                    <button type="button" @click="quickAddRoomTool('KEY')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                                        <span>🔑 Room Keys</span>
                                    </button>
                                    <button type="button" @click="quickAddRoomTool('PEN')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                                        <span>🖊️ Smart TV Pen</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Selected Room Tools List --}}
                            <template x-if="roomToolRows.length > 0">
                                <div class="space-y-2 pt-2 border-t border-amber-200 dark:border-amber-800/50">
                                    <template x-for="(row, index) in roomToolRows" :key="index">
                                        <div class="p-2.5 rounded-lg bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-700 flex flex-wrap sm:flex-nowrap items-center gap-2">
                                            <div class="flex-1 min-w-[200px]">
                                                <select :name="'room_tools[' + index + '][tool_id]'"
                                                        x-model="row.tool_id"
                                                        :disabled="type !== 'room'"
                                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-xs py-1.5 px-2 focus:ring-amber-500 focus:border-amber-500">
                                                    <option value="">— Select Tool / Accessory —</option>
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

                                            <div class="w-28 shrink-0 flex items-center gap-1.5">
                                                <label class="text-[10px] text-gray-500">Qty:</label>
                                                <input type="number"
                                                       :name="'room_tools[' + index + '][quantity]'"
                                                       x-model.number="row.quantity"
                                                       min="1"
                                                       :max="getToolInfo(row.tool_id) ? getToolInfo(row.tool_id).available : 99"
                                                       :disabled="type !== 'room'"
                                                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-xs py-1 px-1.5 text-center font-bold">
                                            </div>

                                            <button type="button" @click="removeRoomToolRow(index)"
                                                    class="text-red-500 hover:text-red-700 text-xs px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition shrink-0"
                                                    title="Remove accessory">
                                                ✕ Remove
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="roomToolRows.length === 0">
                                <p class="text-[11px] text-amber-700/70 dark:text-amber-400 italic pt-1">
                                    No accessories attached yet. Click a quick add chip or "+ Add Tool" above if faculty is borrowing remotes, cables, or equipment along with this room.
                                </p>
                            </template>

                            <x-input-error class="mt-1" :messages="$errors->get('room_tools')" />
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
