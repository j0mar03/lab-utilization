<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Checkout & Guidelines</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
            color: #1f2937;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #7c2d12;
            background: linear-gradient(135deg, #881337 0%, #4c0519 100%);
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .content {
            padding: 24px;
        }
        .greeting {
            font-size: 15px;
            margin-bottom: 16px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-purple { background-color: #f3e8ff; color: #6b21a8; }
        .badge-amber  { background-color: #fef3c7; color: #92400e; }
        .badge-blue   { background-color: #e0f2fe; color: #0369a1; }

        .summary-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            margin: 18px 0;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 13px;
        }
        .summary-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .summary-label {
            color: #6b7280;
            font-weight: 500;
        }
        .summary-value {
            color: #111827;
            font-weight: 600;
            text-align: right;
        }

        .guidelines-box {
            border-radius: 10px;
            padding: 18px;
            margin: 20px 0;
        }
        .guidelines-purple {
            background-color: #faf5ff;
            border: 1px solid #e9d5ff;
        }
        .guidelines-amber {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
        }
        .guidelines-blue {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
        }
        .guidelines-title {
            font-size: 14px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .guideline-item {
            margin-bottom: 10px;
            font-size: 13px;
            line-height: 1.5;
            color: #374151;
        }
        .guideline-item strong {
            color: #111827;
        }
        .guideline-item:last-child {
            margin-bottom: 0;
        }

        .accessories-box {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 12px;
            margin: 16px 0;
            color: #92400e;
        }

        .action-button {
            display: block;
            width: fit-content;
            margin: 20px auto;
            background-color: #7c2d12;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            text-align: center;
        }

        .return-notice {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 12px 14px;
            font-size: 12px;
            color: #1e40af;
            border-radius: 0 8px 8px 0;
            margin-top: 20px;
            line-height: 1.5;
        }

        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>

<div class="container">
    {{-- Header --}}
    <div class="header">
        <h1>PUP Institute of Technology</h1>
        <p>Laboratory & Room Utilization System</p>
    </div>

    {{-- Main Content --}}
    <div class="content">
        <p class="greeting">
            Dear <strong>{{ $transaction->borrower_name }}</strong>,
        </p>

        <p style="font-size: 13px; line-height: 1.5; color: #4b5563;">
            This email confirms your checkout session for <strong>{{ $room->name ?? 'Room' }}</strong>. Below are your session details along with important facility usage guidelines to ensure safety, cleanliness, and security.
        </p>

        {{-- Booking Summary --}}
        <div class="summary-card">
            <div class="summary-row">
                <span class="summary-label">Room / Facility:</span>
                <span class="summary-value">
                    {{ $room->name ?? 'Room' }}
                    @if ($room && $room->location)
                        ({{ $room->location }})
                    @endif
                </span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Classification:</span>
                <span class="summary-value">
                    @if ($roomType === 'computer_lab')
                        <span class="badge badge-purple">💻 Computer Laboratory</span>
                    @elseif ($roomType === 'engineering_lab')
                        <span class="badge badge-amber">⚙️ Engineering / Mechanical Lab</span>
                    @elseif ($roomType === 'lecture')
                        <span class="badge badge-blue">📖 Lecture Classroom</span>
                    @else
                        <span class="badge badge-blue">🏫 General Facility</span>
                    @endif
                </span>
            </div>
            @if ($transaction->subject)
                <div class="summary-row">
                    <span class="summary-label">Subject / Purpose:</span>
                    <span class="summary-value">{{ $transaction->subject }}</span>
                </div>
            @endif
            <div class="summary-row">
                <span class="summary-label">Time In (Checked Out):</span>
                <span class="summary-value">
                    {{ $transaction->checked_out_at ? $transaction->checked_out_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A') }}
                </span>
            </div>
            @if ($transaction->expected_return_at)
                <div class="summary-row">
                    <span class="summary-label">Expected Return:</span>
                    <span class="summary-value" style="color: #b91c1c;">
                        {{ $transaction->expected_return_at->format('h:i A') }}
                    </span>
                </div>
            @endif
            @if ($transaction->hasSoftwareUtilized())
                <div class="summary-row">
                    <span class="summary-label">Software Recorded:</span>
                    <span class="summary-value">{{ $transaction->softwareSummary() }}</span>
                </div>
            @endif
        </div>

        {{-- Borrowed Accessories / Tools attached to room --}}
        @if ($transaction->items && $transaction->items->isNotEmpty())
            <div class="accessories-box">
                <strong>📦 Borrowed Accessories & Equipment:</strong>
                <ul style="margin: 6px 0 0 0; padding-left: 20px;">
                    @foreach ($transaction->items as $item)
                        <li>{{ $item->tool?->name ?? 'Item' }} (×{{ $item->quantity_borrowed }})</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Dynamic Guidelines based on Room Type --}}
        @if ($roomType === 'computer_lab')
            {{-- Computer Lab Guidelines --}}
            <div class="guidelines-box guidelines-purple">
                <h3 class="guidelines-title" style="color: #6b21a8;">
                    <span>💻</span>
                    <span>Computer Laboratory Guidelines & Regulations</span>
                </h3>
                <div class="guideline-item">
                    • <strong>Strictly No Food & Drinks:</strong> Liquids and food items are prohibited near all computer workstations, keyboards, and server racks.
                </div>
                <div class="guideline-item">
                    • <strong>Save Files to Cloud / USB:</strong> Workstations are deep-frozen and reset regularly. Remind students to save coursework to Google Drive, MS OneDrive, or personal USB drives.
                </div>
                <div class="guideline-item">
                    • <strong>Authorized Software Only:</strong> Run only department-approved software. Altering system settings or downloading unauthorized executables is prohibited.
                </div>
                <div class="guideline-item">
                    • <strong>Post-Class Shutdown:</strong> Ensure all computer terminals and monitors are properly shut down before dismissing the class.
                </div>
                <div class="guideline-item">
                    • <strong>Power & Air Conditioning:</strong> Turn off all air-conditioning units, lights, and projectors upon exiting.
                </div>
            </div>
        @elseif ($roomType === 'engineering_lab')
            {{-- Engineering / Mechanical Lab Guidelines --}}
            <div class="guidelines-box guidelines-amber">
                <h3 class="guidelines-title" style="color: #92400e;">
                    <span>⚙️</span>
                    <span>Engineering & Mechanical Lab Safety Guidelines</span>
                </h3>
                <div class="guideline-item">
                    • <strong>Personal Protective Equipment (PPE):</strong> Safety glasses, closed-toe shoes, and appropriate workshop attire must be worn at all times around machinery.
                </div>
                <div class="guideline-item">
                    • <strong>Apparatus Inspection:</strong> Inspect all multimeters, power supplies, test leads, and machinery prior to operating. Report any damaged equipment immediately.
                </div>
                <div class="guideline-item">
                    • <strong>Workstation & Bench Care:</strong> Keep workbench areas orderly. Do not leave powered circuits or rotating tools unattended.
                </div>
                <div class="guideline-item">
                    • <strong>Main Power Off:</strong> Ensure main bench circuit breakers, gas valves, and machine master switches are shut off before vacating.
                </div>
                <div class="guideline-item">
                    • <strong>Tool Return:</strong> Return all borrowed trainer kits, apparatus, and keys to the Student Assistant counter in complete condition.
                </div>
            </div>
        @elseif ($roomType === 'lecture')
            {{-- Lecture Classroom Guidelines --}}
            <div class="guidelines-box guidelines-blue">
                <h3 class="guidelines-title" style="color: #0369a1;">
                    <span>📖</span>
                    <span>Lecture Classroom Guidelines</span>
                </h3>
                <div class="guideline-item">
                    • <strong>Clean Whiteboard:</strong> Please erase all whiteboard notes and markers before leaving for the next class.
                </div>
                <div class="guideline-item">
                    • <strong>Arrangement of Chairs:</strong> Instruct students to arrange armchairs and desks in neat, orderly rows.
                </div>
                <div class="guideline-item">
                    • <strong>Cleanliness:</strong> Ensure the room is free of trash, candy wrappers, and plastic bottles.
                </div>
                <div class="guideline-item">
                    • <strong>Energy Conservation:</strong> Turn off ceiling fans, lights, and projector equipment before locking the room.
                </div>
            </div>
        @else
            {{-- General Room Guidelines --}}
            <div class="guidelines-box guidelines-blue">
                <h3 class="guidelines-title" style="color: #0369a1;">
                    <span>🏫</span>
                    <span>Facility Proper Use Guidelines</span>
                </h3>
                <div class="guideline-item">
                    • <strong>Cleanliness:</strong> Keep the facility clean, orderly, and tidy.
                </div>
                <div class="guideline-item">
                    • <strong>Security:</strong> Lock doors and windows when concluding your session.
                </div>
                <div class="guideline-item">
                    • <strong>Turn Off Utilities:</strong> Turn off lights, fans, and electronics before departing.
                </div>
            </div>
        @endif

        {{-- Lab Manual Link if available --}}
        @if ($manualUrl)
            <div style="text-align: center; margin: 15px 0;">
                <a href="{{ $manualUrl }}" target="_blank" class="action-button">
                    📄 View Official Room Guidelines & Manual ↗
                </a>
            </div>
        @endif

        {{-- Return Reminder --}}
        <div class="return-notice">
            <strong>⏰ Returning Keys & Closing Your Session:</strong><br>
            When your class session concludes, please return the room key and any borrowed accessories to the <strong>Student Assistant (SA) Counter</strong> immediately. This officially closes your session in the system and avoids overdue notifications.
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p style="margin: 0;">Polytechnic University of the Philippines — Institute of Technology (PUP-ITECH)</p>
        <p style="margin: 4px 0 0 0;">This is an automated operational notification generated upon room checkout.</p>
    </div>
</div>

</body>
</html>
