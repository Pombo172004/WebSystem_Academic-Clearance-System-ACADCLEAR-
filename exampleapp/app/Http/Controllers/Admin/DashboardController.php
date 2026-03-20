<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use App\Models\User;
use App\Models\Clearance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalColleges' => College::count(),
            'totalDepartments' => Department::count(),
            'totalStudents' => User::where('role', 'student')->count(),
            'totalStaff' => User::where('role', 'staff')->count(),
            'pendingClearances' => Clearance::where('status', 'pending')->count(),
            'approvedClearances' => Clearance::where('status', 'approved')->count(),
            'rejectedClearances' => Clearance::where('status', 'rejected')->count(),
            'recentClearances' => Clearance::with(['student', 'department'])
                ->latest()
                ->take(10)
                ->get(),
            'recentStudents' => User::where('role', 'student')
                ->with('college')
                ->latest()
                ->take(5)
                ->get(),
            'collegeStats' => College::withCount('departments')
                ->withCount('students')
                ->get()
        ];

        return view('admin.dashboard', $data);
    }
}