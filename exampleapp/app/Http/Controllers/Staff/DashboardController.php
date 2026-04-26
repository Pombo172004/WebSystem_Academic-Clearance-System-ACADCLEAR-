<?php
namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Models\ClearanceChecklistItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $staff = auth()->user();

        if (!$staff->office_role) {
            $stats = [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
            ];

            $recentClearances = collect();

            return view('staff.dashboard', compact('stats', 'recentClearances'))
                ->with('warning', 'No office role assigned to your account yet. Please contact your school admin.');
        }
        
        // Office-role aware checklist statistics for staff's department.
        $roleItemQuery = ClearanceChecklistItem::query()
            ->where('office_role', $staff->office_role)
            ->whereHas('clearance', function ($query) use ($staff) {
                if (filled($staff->department_id)) {
                    $query->where('department_id', $staff->department_id);
                }
            });

        $stats = [
            'total' => (clone $roleItemQuery)->count(),
            'pending' => (clone $roleItemQuery)->where('status', 'pending')->count(),
            'approved' => (clone $roleItemQuery)->where('status', 'approved')->count(),
            'rejected' => Clearance::query()
                ->when(filled($staff->department_id), function ($query) use ($staff) {
                    $query->where('department_id', $staff->department_id);
                })
                ->where('status', 'rejected')
                ->whereHas('checklistItems', function ($query) use ($staff) {
                    $query->where('office_role', $staff->office_role);
                })
                ->count(),
        ];

        // Get recent clearances that include checklist items for this office role.
        $recentClearances = Clearance::with('student')
            ->when(filled($staff->department_id), function ($query) use ($staff) {
                $query->where('department_id', $staff->department_id);
            })
            ->whereHas('checklistItems', function ($query) use ($staff) {
                $query->where('office_role', $staff->office_role);
            })
            ->with(['checklistItems' => function ($query) use ($staff) {
                $query->where('office_role', $staff->office_role)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            }])
            ->latest()
            ->take(10)
            ->get();

        return view('staff.dashboard', compact('stats', 'recentClearances'));
    }
}