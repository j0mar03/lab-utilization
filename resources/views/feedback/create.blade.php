<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Facility & Laboratory Experience Feedback — PUP-ITECH</title>
    @vite(['resources/css/app.css'])
    <style>
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            transition: transform 0.1s ease, color 0.15s ease;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            transform: scale(1.15);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen antialiased flex flex-col justify-between">

    <!-- Top PUP Header -->
    <header class="bg-maroon-850 bg-red-900 text-white shadow-md">
        <div class="max-w-xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🔬</span>
                <div>
                    <h1 class="text-base font-extrabold tracking-tight uppercase leading-tight">PUP Institute of Technology</h1>
                    <p class="text-xs text-red-200">Facility Quality & Utilization Feedback Survey</p>
                </div>
            </div>
            @if ($selectedRoom)
                <span class="text-xs font-bold bg-white/20 px-2.5 py-1 rounded-full border border-white/30 text-white">
                    {{ $selectedRoom->name }}
                </span>
            @endif
        </div>
    </header>

    <!-- Main Survey Card -->
    <main class="max-w-xl mx-auto w-full px-4 py-6 flex-1">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 sm:p-7">

            <div class="border-b border-gray-100 dark:border-gray-700 pb-4 mb-5">
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span>📋</span>
                    <span>Client Satisfaction & Diagnostic Survey</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                    Your evaluation helps optimize academic facility scheduling, ensure equipment availability, and directly supports PUP-ITECH accreditation and institutional quality reviews.
                </p>
            </div>

            @if (isset($errors) && $errors->any())
                <div class="mb-5 p-3.5 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-xs text-red-700 dark:text-red-300">
                    <p class="font-bold mb-1">Please answer all required rating items:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($transaction)
                <div class="mb-5 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 rounded-2xl shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="text-3xl">🎉</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <h3 class="font-extrabold text-sm text-emerald-950 dark:text-emerald-100 flex items-center gap-2">
                                    <span>Key Returned Successfully!</span>
                                </h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 dark:bg-emerald-800 text-emerald-900 dark:text-emerald-100 uppercase tracking-wide">
                                    Session Closed
                                </span>
                            </div>
                            <p class="text-xs text-emerald-800 dark:text-emerald-200 mt-1">
                                <strong>{{ $transaction->room?->name ?? 'Room' }}</strong>
                                @if ($transaction->returned_at)
                                    · Returned at {{ $transaction->returned_at->format('g:i A') }}
                                @endif
                                @if ($transaction->checked_out_at && $transaction->returned_at)
                                    (Duration: {{ $transaction->checked_out_at->diff($transaction->returned_at)->format('%hh %im') }})
                                @endif
                            </p>
                            <p class="text-[11px] text-emerald-700 dark:text-emerald-300 mt-1">
                                Please take 20 seconds to answer the 5 quick questions below so we can keep our laboratories well-maintained!
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('feedback.store') }}" method="POST" class="space-y-6">
                @csrf

                @if ($transaction)
                    <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
                @endif

                <!-- SECTION 1: Respondent & Room Context -->
                <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-xl border border-gray-200 dark:border-gray-700 space-y-3.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                        <span>1️⃣</span>
                        <span>Session Context & Demographics</span>
                    </h3>

                    <!-- Room Selector -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Laboratory / Lecture Room <span class="text-red-500">*</span>
                        </label>
                        @if ($transaction && $transaction->room)
                            <input type="hidden" name="room_id" value="{{ $transaction->room->id }}">
                            <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-xl">🚪</span>
                                    <div>
                                        <span class="font-extrabold text-sm text-gray-900 dark:text-gray-100 block">{{ $transaction->room->name }}</span>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $transaction->room->roomType() }} · {{ $transaction->room->department ?? 'General' }}</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 rounded-full border border-red-200 dark:border-red-800">
                                    Active Session
                                </span>
                            </div>
                        @else
                            <select name="room_id" id="room_id" required
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-red-600 focus:border-red-600">
                                <option value="">-- Select Laboratory or Classroom --</option>
                                @foreach ($rooms as $r)
                                    <option value="{{ $r->id }}" {{ (old('room_id', $selectedRoom?->id) == $r->id) ? 'selected' : '' }}>
                                        {{ $r->name }} ({{ $r->roomType() }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Capacity / Role -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            In what capacity did you utilize this facility? <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (['Student', 'Faculty', 'Guest'] as $cap)
                                <label class="flex items-center justify-center gap-1.5 px-3 py-2 border rounded-lg cursor-pointer text-xs font-semibold transition
                                    {{ old('capacity', $defaultCapacity) === $cap ? 'bg-red-50 border-red-500 text-red-900 dark:bg-red-900/40 dark:border-red-600 dark:text-red-200' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                    <input type="radio" name="capacity" value="{{ $cap }}" class="text-red-600 focus:ring-red-500"
                                           {{ old('capacity', $defaultCapacity) === $cap ? 'checked' : '' }} required>
                                    <span>{{ $cap }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Optional Name / Email -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label for="respondent_name" class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                Name (Optional)
                            </label>
                            <input type="text" name="respondent_name" id="respondent_name" value="{{ old('respondent_name', $defaultName) }}"
                                   placeholder="e.g. Prof. Juan Dela Cruz"
                                   class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white mt-0.5">
                        </div>
                        <div>
                            <label for="respondent_email" class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                Email Address (Optional)
                            </label>
                            <input type="email" name="respondent_email" id="respondent_email" value="{{ old('respondent_email', $defaultEmail) }}"
                                   placeholder="e.g. jdelacruz@pup.edu.ph"
                                   class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white mt-0.5">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Core Likert Constructs (1 to 5) -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-1 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                            <span>2️⃣</span>
                            <span>Evaluation Dimensions (1 = Poor, 5 = Excellent)</span>
                        </h3>
                        <span class="text-[10px] text-gray-400 italic">5-Point Likert</span>
                    </div>

                    @php
                        $questions = [
                            [
                                'field' => 'q1_environment',
                                'label' => '1. Environmental Quality & Cleanliness',
                                'desc'  => 'Cleanliness of floors/whiteboards, lighting, comfortable air-conditioning/ventilation, and neat seating.',
                                'icon'  => '🧹'
                            ],
                            [
                                'field' => 'q2_resources',
                                'label' => '2. Resource & Technology Availability',
                                'desc'  => 'Functionality and readiness of TV display, Projector, PC workstations, power sockets, and tools.',
                                'icon'  => '🖥️'
                            ],
                            [
                                'field' => 'q3_safety',
                                'label' => '3. Laboratory Safety & Workstation Rules',
                                'desc'  => 'Safety rules implementation, hazard precautions, Deep-Freeze warnings, and emergency orderly exits.',
                                'icon'  => '🦺'
                            ],
                            [
                                'field' => 'q4_service',
                                'label' => '4. Laboratory Staff & SA Assistance',
                                'desc'  => 'Student Assistant counter responsiveness, key dispatch speed, courtesy, and technical support assistance.',
                                'icon'  => '🤝'
                            ],
                            [
                                'field' => 'q5_system_experience',
                                'label' => '5. Digital Checkout & QR Usability',
                                'desc'  => 'Convenience of mobile QR scan checkout, clear automated email guidelines, and overall borrowing speed.',
                                'icon'  => '📱'
                            ],
                            [
                                'field' => 'overall_score',
                                'label' => '6. Overall Laboratory Experience Today',
                                'desc'  => 'Your comprehensive cumulative satisfaction with this laboratory or classroom session.',
                                'icon'  => '⭐',
                                'highlight' => true
                            ],
                        ];
                    @endphp

                    @foreach ($questions as $q)
                        <div class="p-3.5 rounded-xl border {{ !empty($q['highlight']) ? 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' }} transition">
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div>
                                    <span class="text-xs font-bold text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                        <span>{{ $q['icon'] }}</span>
                                        <span>{{ $q['label'] }}</span>
                                    </span>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-snug">
                                        {{ $q['desc'] }}
                                    </p>
                                </div>
                            </div>

                            <!-- 1-5 Rating Pills -->
                            <div class="grid grid-cols-5 gap-1.5 pt-2">
                                @for ($val = 1; $val <= 5; $val++)
                                    @php
                                        $valLabels = [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'];
                                    @endphp
                                    <label class="flex flex-col items-center justify-center p-2 rounded-lg border text-center cursor-pointer transition text-xs
                                        hover:border-red-400 hover:bg-red-50/50 dark:hover:bg-red-900/20
                                        has-[:checked]:bg-red-600 has-[:checked]:border-red-600 has-[:checked]:text-white
                                        border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-gray-700 dark:text-gray-300">
                                        <input type="radio" name="{{ $q['field'] }}" value="{{ $val }}" required
                                               {{ old($q['field']) == $val ? 'checked' : '' }} class="sr-only">
                                        <span class="font-extrabold text-sm">{{ $val }}</span>
                                        <span class="text-[9px] uppercase tracking-wider opacity-85 mt-0.5">{{ $valLabels[$val] }}</span>
                                    </label>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- SECTION 3: Diagnostic Issue Checklist -->
                <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                        <span>3️⃣</span>
                        <span>Diagnostic Issue Reporting (Optional)</span>
                    </h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                        Did you encounter any specific hardware, environment, or software issues today?
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1 text-xs">
                        @foreach ($issueLabels as $key => $label)
                            <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <input type="checkbox" name="reported_issues[]" value="{{ $key }}"
                                       {{ in_array($key, (array) old('reported_issues', [])) ? 'checked' : '' }}
                                       class="rounded text-red-600 focus:ring-red-500">
                                <span class="text-gray-800 dark:text-gray-200 text-xs">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- SECTION 4: Qualitative Feedback & Suggestions -->
                <div>
                    <label for="suggestions" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1.5">
                        <span>💬</span>
                        <span>Suggestions on how we can further improve our facilities and services:</span>
                    </label>
                    <textarea name="suggestions" id="suggestions" rows="3"
                              placeholder="Please share any constructive comments or improvements regarding equipment, staff assistance, software, or lab guidelines..."
                              class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-red-600 focus:border-red-600 p-3 leading-relaxed">{{ old('suggestions') }}</textarea>
                    <p class="text-[10px] text-gray-400 mt-1 italic">
                        Constructive feedback is directly analyzed for continuous laboratory enhancement and OPCR quality benchmarks.
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3.5 px-4 bg-red-800 hover:bg-red-900 active:bg-red-950 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2">
                        <span>📤 Submit Evaluation Feedback</span>
                        <span>→</span>
                    </button>
                    <p class="text-center text-[11px] text-gray-400 mt-2">
                        Takes less than 1 minute · All individual submissions remain confidential
                    </p>

                    @if ($transaction)
                        <div class="text-center mt-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <a href="{{ route('scan.return-success', $transaction->id) }}"
                               class="text-xs text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 font-medium">
                                Skip rating for now & view return receipt →
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </main>

    <footer class="text-center py-4 text-xs text-gray-400 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
        Polytechnic University of the Philippines — Institute of Technology (PUP-ITECH)
    </footer>

</body>
</html>
