<?php
namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use Illuminate\Http\Request;

class ClearanceController extends Controller
{
    /**
     * Display list of clearances for staff's department
     */
    public function index(Request $request)
    {
        $staff = auth()->user();
        
        $query = Clearance::with('student')
            ->where('department_id', $staff->department_id);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search by student name
        if ($request->has('search') && $request->search != '') {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $clearances = $query->paginate(20)->withQueryString();

        return view('staff.clearances.index', compact('clearances'));
    }

    /**
     * Approve a clearance
     */
    public function approve(Clearance $clearance)
    {
        $staff = auth()->user();
        
        // Ensure clearance belongs to staff's department
        if ($clearance->department_id !== $staff->department_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $clearance->update([
            'status' => 'approved',
            'remarks' => null
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Clearance approved successfully']);
        }

        return back()->with('success', 'Clearance approved successfully.');
    }

    /**
     * Reject a clearance with remarks
     */
    public function reject(Request $request, Clearance $clearance)
    {
        $staff = auth()->user();
        
        // Ensure clearance belongs to staff's department
        if ($clearance->department_id !== $staff->department_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'remarks' => 'required|string|max:500'
        ]);

        $clearance->update([
            'status' => 'rejected',
            'remarks' => $validated['remarks']
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Clearance rejected successfully']);
        }

        return back()->with('success', 'Clearance rejected successfully.');
    }

    /**
     * Bulk approve clearances
     */
    public function bulkApprove(Request $request)
    {
        $staff = auth()->user();
        
        $validated = $request->validate([
            'clearance_ids' => 'required|array',
            'clearance_ids.*' => 'exists:clearances,id'
        ]);

        $count = Clearance::whereIn('id', $validated['clearance_ids'])
            ->where('department_id', $staff->department_id)
            ->update(['status' => 'approved', 'remarks' => null]);

        return response()->json([
            'success' => true,
            'message' => "{$count} clearances approved successfully"
        ]);
    }

    /**
     * Export clearances to CSV
     */
    public function export(Request $request)
    {
        $staff = auth()->user();
        
        $query = Clearance::with('student')
            ->where('department_id', $staff->department_id);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $clearances = $query->get();

        $filename = "clearances-" . now()->format('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add headers
        fputcsv($handle, ['Student Name', 'Student Email', 'Department', 'Status', 'Remarks', 'Request Date', 'Last Updated']);

        // Add data
        foreach ($clearances as $clearance) {
            fputcsv($handle, [
                $clearance->student->name,
                $clearance->student->email,
                $clearance->department->name,
                ucfirst($clearance->status),
                $clearance->remarks ?? '',
                $clearance->created_at->format('M d, Y H:i'),
                $clearance->updated_at->format('M d, Y H:i')
            ]);
        }

        fclose($handle);
        exit;
    }
}