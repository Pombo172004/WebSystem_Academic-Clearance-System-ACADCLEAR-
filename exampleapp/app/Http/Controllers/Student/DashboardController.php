<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $student = auth()->user();
        
        // Get all clearances with department/checklist details.
        $clearances = $student->clearances()
            ->with(['department', 'checklistItems'])
            ->latest('updated_at')
            ->get();

        $allItems = $clearances->flatMap(function ($clearance) {
            if ($clearance->checklistItems->isNotEmpty()) {
                return $clearance->checklistItems;
            }

            // Fallback for legacy clearances without checklist rows.
            return collect([(object) [
                'status' => $clearance->status,
            ]]);
        });

        $approvedItems = $allItems->where('status', 'approved')->count();
        $pendingItems = $allItems->where('status', 'pending')->count();
        $rejectedClearances = $clearances->where('status', 'rejected')->count();
        
        // Calculate checklist-aware statistics.
        $stats = [
            'total' => $clearances->count(),
            'total_items' => $allItems->count(),
            'approved' => $approvedItems,
            'rejected' => $rejectedClearances,
            'pending' => $pendingItems,
            'progress' => $allItems->count() > 0 
                ? round(($approvedItems / $allItems->count()) * 100, 2)
                : 0
        ];

        // Group by status for charts
        $byStatus = [
            'labels' => ['Approved', 'Pending', 'Rejected'],
            'data' => [
                $stats['approved'],
                $stats['pending'],
                $stats['rejected']
            ],
            'colors' => ['#28a745', '#ffc107', '#dc3545']
        ];

        return view('student.dashboard', compact('clearances', 'stats', 'byStatus'));
    }
}