<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Feedback Received — PUP-ITECH</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen antialiased flex flex-col justify-between">

    <header class="shadow-md py-4" style="background: linear-gradient(135deg, #7b1113 0%, #4a0809 100%) !important; color: #ffffff !important;">
        <div class="max-w-md mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🔬</span>
                <span class="text-sm font-black uppercase tracking-wide" style="color: #ffffff !important;">PUP-ITECH Lab System</span>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background-color: rgba(255, 255, 255, 0.22); color: #ffffff !important; border: 1px solid rgba(255,255,255,0.4);">
                Feedback Logged
            </span>
        </div>
    </header>

    <main class="max-w-md mx-auto w-full px-4 py-8 flex-1 flex flex-col justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center space-y-4">
            <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300 rounded-full flex items-center justify-center text-3xl mx-auto">
                ✓
            </div>

            <h2 class="text-xl font-extrabold text-gray-900 dark:text-gray-100">
                Thank You for Your Feedback!
            </h2>

            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                Your evaluation for <strong>{{ $feedback->room?->name ?? 'the facility' }}</strong> has been recorded successfully.
                Your ratings directly contribute to laboratory facility improvements and academic research quality benchmarks.
            </p>

            <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-xl border border-gray-100 dark:border-gray-700 text-xs text-left space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Facility Evaluated:</span>
                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ $feedback->room?->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Capacity:</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $feedback->capacity }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Overall Rating:</span>
                    <span class="font-bold text-amber-500">{{ str_repeat('⭐', $feedback->overall_score) }} ({{ $feedback->overall_score }}/5)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Construct Composite Mean:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $feedback->composite_score }} / 5.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Timestamp:</span>
                    <span class="text-gray-600 dark:text-gray-400">{{ $feedback->created_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>

            <div class="pt-2 space-y-2">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-red-900 hover:bg-red-800 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                        ← Back to Dashboard
                    </a>
                @endauth
                @if ($feedback->transaction)
                    <a href="{{ route('scan.return-success', $feedback->transaction->id) }}"
                       class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold text-xs rounded-xl transition">
                        View Return Receipt
                    </a>
                @else
                    <a href="{{ route('feedback.create') }}"
                       class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-red-800 hover:bg-red-900 text-white font-semibold text-xs rounded-xl transition">
                        Submit Another Feedback
                    </a>
                @endif
            </div>
        </div>
    </main>

    <footer class="text-center py-4 text-xs text-gray-400 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
        Polytechnic University of the Philippines — Institute of Technology
    </footer>

</body>
</html>
