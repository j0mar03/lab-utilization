<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class FeedbackReportController extends Controller
{
    /**
     * Display client satisfaction metrics, construct averages, diagnostic issues, and research export.
     */
    public function index(Request $request): View
    {
        $roomId = $request->input('room_id');
        $capacity = $request->input('capacity');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Feedback::with(['room', 'transaction']);

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        if ($capacity) {
            $query->where('capacity', $capacity);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $allFeedbacks = (clone $query)->latest()->get();
        $feedbacksPaginated = $query->latest()->paginate(15)->withQueryString();

        $totalResponses = $allFeedbacks->count();

        // Compute Construct Means (EduQUAL Likert 1-5)
        $avgOverall      = $totalResponses > 0 ? round($allFeedbacks->avg('overall_score'), 2) : 0;
        $avgEnvironment  = $totalResponses > 0 ? round($allFeedbacks->avg('q1_environment'), 2) : 0;
        $avgResources    = $totalResponses > 0 ? round($allFeedbacks->avg('q2_resources'), 2) : 0;
        $avgSafety       = $totalResponses > 0 ? round($allFeedbacks->avg('q3_safety'), 2) : 0;
        $avgService      = $totalResponses > 0 ? round($allFeedbacks->avg('q4_service'), 2) : 0;
        $avgSystem       = $totalResponses > 0 ? round($allFeedbacks->avg('q5_system_experience'), 2) : 0;

        // Composite Quality Index (Mean across all 5 dimensions)
        $compositeIndex = $totalResponses > 0
            ? round(($avgEnvironment + $avgResources + $avgSafety + $avgService + $avgSystem) / 5, 2)
            : 0;

        // Overall Satisfaction Percentage (Rating >= 4 as positive satisfaction for OPCR/CHED)
        $positiveCount = $allFeedbacks->filter(fn($f) => $f->overall_score >= 4)->count();
        $satisfactionRate = $totalResponses > 0 ? round(($positiveCount / $totalResponses) * 100, 1) : 0;

        // Breakdown by Capacity (Student vs Faculty vs Guest)
        $capacityCounts = [
            'Student' => $allFeedbacks->where('capacity', 'Student')->count(),
            'Faculty' => $allFeedbacks->where('capacity', 'Faculty')->count(),
            'Guest'   => $allFeedbacks->where('capacity', 'Guest')->count(),
        ];

        // Diagnostic Issues Aggregation
        $issueLabels = Feedback::issueLabels();
        $issueCounts = [];
        foreach ($issueLabels as $k => $lbl) {
            $issueCounts[$k] = 0;
        }

        foreach ($allFeedbacks as $fb) {
            if (!empty($fb->reported_issues) && is_array($fb->reported_issues)) {
                foreach ($fb->reported_issues as $iss) {
                    if (isset($issueCounts[$iss])) {
                        $issueCounts[$iss]++;
                    }
                }
            }
        }
        arsort($issueCounts);

        // Room-by-Room Satisfaction Ranking
        $roomsSummary = [];
        $feedbacksByRoom = $allFeedbacks->groupBy('room_id');
        $allRooms = Room::orderByRaw("CASE WHEN name LIKE 'LAB%' THEN 0 ELSE 1 END, name")->get();

        foreach ($allRooms as $r) {
            $rFeedbacks = $feedbacksByRoom->get($r->id, collect());
            $cnt = $rFeedbacks->count();
            if ($cnt > 0) {
                $roomsSummary[] = [
                    'room'        => $r,
                    'count'       => $cnt,
                    'overall'     => round($rFeedbacks->avg('overall_score'), 2),
                    'environment' => round($rFeedbacks->avg('q1_environment'), 2),
                    'resources'   => round($rFeedbacks->avg('q2_resources'), 2),
                    'safety'      => round($rFeedbacks->avg('q3_safety'), 2),
                    'service'     => round($rFeedbacks->avg('q4_service'), 2),
                    'system'      => round($rFeedbacks->avg('q5_system_experience'), 2),
                ];
            }
        }

        usort($roomsSummary, fn($a, $b) => $b['overall'] <=> $a['overall']);

        return view('admin.feedbacks.index', [
            'feedbacks'          => $feedbacksPaginated,
            'totalResponses'     => $totalResponses,
            'avgOverall'         => $avgOverall,
            'avgEnvironment'     => $avgEnvironment,
            'avgResources'       => $avgResources,
            'avgSafety'          => $avgSafety,
            'avgService'         => $avgService,
            'avgSystem'          => $avgSystem,
            'compositeIndex'     => $compositeIndex,
            'satisfactionRate'   => $satisfactionRate,
            'capacityCounts'     => $capacityCounts,
            'issueCounts'        => $issueCounts,
            'issueLabels'        => $issueLabels,
            'roomsSummary'       => $roomsSummary,
            'roomsDropdown'      => $allRooms,
            'activeFilters'      => [
                'room_id'   => $roomId,
                'capacity'  => $capacity,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
        ]);
    }

    /**
     * Export raw survey dataset formatted as CSV for research analysis (SPSS / R / Excel).
     */
    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $roomId = $request->input('room_id');
        $capacity = $request->input('capacity');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Feedback::with(['room', 'transaction']);

        if ($roomId) {
            $query->where('room_id', $roomId);
        }
        if ($capacity) {
            $query->where('capacity', $capacity);
        }
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $feedbacks = $query->orderBy('created_at')->get();

        $filename = 'PUP_ITECH_Lab_Satisfaction_Dataset_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return Response::stream(function () use ($feedbacks) {
            $handle = fopen('php://output', 'w');

            // Research CSV Header Columns (SPSS Variable Codes)
            fputcsv($handle, [
                'Response_ID',
                'Timestamp',
                'Facility_Name',
                'Facility_Type',
                'Department',
                'Respondent_Capacity',
                'Respondent_Name',
                'Respondent_Email',
                'Transaction_ID',
                'Q1_Environment',
                'Q2_Resources',
                'Q3_Safety',
                'Q4_Service',
                'Q5_System_Usability',
                'Overall_Satisfaction',
                'Composite_Mean',
                'Reported_Issues',
                'Qualitative_Suggestions',
                'IP_Address',
            ]);

            foreach ($feedbacks as $fb) {
                fputcsv($handle, [
                    $fb->id,
                    $fb->created_at->format('Y-m-d H:i:s'),
                    $fb->room?->name ?? 'Unknown',
                    $fb->room?->room_type ?? 'general',
                    $fb->room?->departmentShort() ?? 'General',
                    $fb->capacity,
                    $fb->respondent_name ?? 'Anonymous',
                    $fb->respondent_email ?? '',
                    $fb->transaction_id ?? '',
                    $fb->q1_environment,
                    $fb->q2_resources,
                    $fb->q3_safety,
                    $fb->q4_service,
                    $fb->q5_system_experience,
                    $fb->overall_score,
                    $fb->composite_score,
                    !empty($fb->reported_issues) ? implode('; ', $fb->reported_issues) : 'none',
                    $fb->suggestions ?? '',
                    $fb->ip_address ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
