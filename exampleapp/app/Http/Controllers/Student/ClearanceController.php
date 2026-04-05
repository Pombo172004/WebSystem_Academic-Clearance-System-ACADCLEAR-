<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClearanceController extends Controller
{
    /**
     * Display student's clearance status
     */
    public function index()
    {
        $student = auth()->user();
        
        $clearances = $student->clearances()
            ->with(['department', 'checklistItems'])
            ->orderByRaw("FIELD(status, 'pending', 'rejected', 'approved')")
            ->get();

        $stats = [
            'total' => $clearances->count(),
            'approved' => $clearances->where('status', 'approved')->count(),
            'pending' => $clearances->where('status', 'pending')->count(),
            'rejected' => $clearances->where('status', 'rejected')->count(),
            'progress' => $clearances->count() > 0 
                ? round(($clearances->where('status', 'approved')->count() / $clearances->count()) * 100, 2)
                : 0
        ];

        return view('student.clearances.index', compact('clearances', 'stats'));
    }

    /**
     * Show specific clearance details
     */
    public function show($id)
    {
        $clearance = auth()->user()
            ->clearances()
            ->with(['department', 'checklistItems'])
            ->findOrFail($id);

        return view('student.clearances.show', compact('clearance'));
    }

    /**
     * Get clearance summary for API
     */
    public function summary(Request $request)
    {
        $student = auth()->user();
        
        $clearances = $student->clearances()->with(['department', 'checklistItems'])->get();

        $allItems = $clearances->flatMap(function ($clearance) {
            if ($clearance->checklistItems->isNotEmpty()) {
                return $clearance->checklistItems;
            }

            // Fallback for legacy rows without checklist items.
            return collect([(object) [
                'status' => $clearance->status,
                'approved_at' => $clearance->updated_at,
            ]]);
        });

        $approvedItems = $allItems->where('status', 'approved')->count();
        $pendingItems = $allItems->where('status', 'pending')->count();
        $rejectedDepartments = $clearances->where('status', 'rejected')->count();

        $departmentRows = $clearances->map(function ($clearance) {
            $items = $clearance->checklistItems;

            $totalItems = $items->count() > 0 ? $items->count() : 1;
            $approvedItemCount = $items->where('status', 'approved')->count();
            $pendingItemCount = $items->where('status', 'pending')->count();

            if ($clearance->status === 'rejected') {
                $departmentStatus = 'rejected';
            } elseif ($approvedItemCount > 0 && $approvedItemCount === $totalItems) {
                $departmentStatus = 'approved';
            } else {
                $departmentStatus = 'pending';
            }

            return [
                'id' => $clearance->department_id,
                'name' => $clearance->department->name,
                'status' => $departmentStatus,
                'remarks' => $clearance->remarks,
                'updated_at' => $clearance->updated_at->format('M d, Y'),
                'approved_items' => $approvedItemCount,
                'total_items' => $totalItems,
                'pending_items' => $pendingItemCount,
            ];
        });
        
        $summary = [
            'total_departments' => $clearances->count(),
            'total_items' => $allItems->count(),
            'approved' => $approvedItems,
            'pending' => $pendingItems,
            'rejected' => $rejectedDepartments,
            'progress' => $allItems->count() > 0 
                ? round(($approvedItems / $allItems->count()) * 100, 2)
                : 0,
            'departments' => $departmentRows,
        ];

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($summary);
        }

        return view('student.clearances.summary', [
            'summary' => $summary,
            'clearances' => $clearances,
            'stats' => [
                'total' => $summary['total_items'],
                'total_departments' => $summary['total_departments'],
                'approved' => $summary['approved'],
                'pending' => $summary['pending'],
                'rejected' => $summary['rejected'],
                'progress' => $summary['progress'],
            ],
        ]);
    }
}