<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $student = auth()->user();
        
        // Get all clearances with department details
        $clearances = $student->clearances()
            ->with('department')
            ->orderBy('status')
            ->get();
        
        // Calculate statistics
        $stats = [
            'total' => $clearances->count(),
            'approved' => $clearances->where('status', 'approved')->count(),
            'rejected' => $clearances->where('status', 'rejected')->count(),
            'pending' => $clearances->where('status', 'pending')->count(),
            'progress' => $clearances->count() > 0 
                ? round(($clearances->where('status', 'approved')->count() / $clearances->count()) * 100, 2)
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