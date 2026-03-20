<?php
namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $staff = auth()->user();
        
        // Get statistics for staff's department
        $stats = [
            'total' => Clearance::where('department_id', $staff->department_id)->count(),
            'pending' => Clearance::where('department_id', $staff->department_id)
                ->where('status', 'pending')
                ->count(),
            'approved' => Clearance::where('department_id', $staff->department_id)
                ->where('status', 'approved')
                ->count(),
            'rejected' => Clearance::where('department_id', $staff->department_id)
                ->where('status', 'rejected')
                ->count(),
        ];

        // Get recent clearances
        $recentClearances = Clearance::with('student')
            ->where('department_id', $staff->department_id)
            ->latest()
            ->take(10)
            ->get();

        return view('staff.dashboard', compact('stats', 'recentClearances'));
    }
}