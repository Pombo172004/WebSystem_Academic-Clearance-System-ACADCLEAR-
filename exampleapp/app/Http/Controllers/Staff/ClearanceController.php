<?php
namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Models\ClearanceChecklistItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClearanceController extends Controller
{
    /**
     * Show form for staff to add clearance for a student.
     */
    public function create()
    {
        $staff = auth()->user();
        $officeRoles = User::officeRoles();

        $students = User::query()
            ->where('role', 'student')
            ->where('college_id', $staff->college_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('staff.clearances.create', compact('students', 'staff', 'officeRoles'));
    }

    /**
     * Store clearance created by staff for their assigned department.
     */
    public function store(Request $request)
    {
        $staff = auth()->user();

        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) use ($staff) {
                    $query->where('role', 'student')
                        ->where('college_id', $staff->college_id);
                }),
            ],
            'clearance_title' => ['required', 'string', 'max:255'],
            'checklist_items' => ['nullable', 'array'],
            'checklist_items.*' => ['string', 'max:255'],
            'custom_item_name' => ['nullable', 'string', 'max:255'],
            'custom_item_contact' => ['nullable', 'string', 'max:255'],
            'custom_item_location' => ['nullable', 'string', 'max:255'],
            'custom_item_office_role' => ['nullable', Rule::in(array_keys(User::officeRoles()))],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $items = $this->buildChecklistItems(
            $validated['checklist_items'] ?? [],
            $validated['custom_item_name'] ?? null,
            $validated['custom_item_contact'] ?? null,
            $validated['custom_item_location'] ?? null,
            $validated['custom_item_office_role'] ?? null
        );

        if (empty($items)) {
            return back()->withErrors([
                'checklist_items' => 'Please select at least one checklist item (office or instructor).',
            ])->withInput();
        }

        $firstItem = $items[0];

        $clearance = Clearance::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'department_id' => $staff->department_id,
            ],
            [
                'status' => 'pending',
                'remarks' => $validated['remarks'] ?? null,
                'clearance_title' => $validated['clearance_title'],
                'office_or_instructor' => $firstItem['item_name'],
                'approval_location' => $firstItem['location'],
            ]
        );

        $clearance->checklistItems()->delete();

        foreach ($items as $index => $item) {
            $clearance->checklistItems()->create([
                'item_name' => $item['item_name'],
                'office_role' => $item['office_role'],
                'contact_person' => $item['contact_person'] ?? null,
                'location' => $item['location'],
                'status' => 'pending',
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('staff.clearances.index')
            ->with('success', 'Clearance has been added for the selected student.');
    }

    /**
     * Mark a checklist item as pending/approved.
     */
    public function updateChecklistItem(Request $request, Clearance $clearance, ClearanceChecklistItem $item)
    {
        $staff = auth()->user();

        if (
            $clearance->department_id !== $staff->department_id ||
            $item->clearance_id !== $clearance->id ||
            !$staff->office_role ||
            $item->office_role !== $staff->office_role
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved'])],
        ]);

        $item->update([
            'status' => $validated['status'],
            'approved_at' => $validated['status'] === 'approved' ? now() : null,
        ]);

        $this->recalculateClearanceStatus($clearance);

        return back()->with('success', 'Checklist item status updated.');
    }

    /**
     * Display list of clearances for staff's department
     */
    public function index(Request $request)
    {
        $staff = auth()->user();

        if (!$staff->office_role) {
            $clearances = Clearance::query()->whereRaw('1 = 0')->paginate(20);
            session()->flash('warning', 'Your account has no office role assigned yet. Please contact your school admin.');

            return view('staff.clearances.index', compact('clearances'));
        }
        
        $query = Clearance::with('student')
            ->where('department_id', $staff->department_id)
            ->whereHas('checklistItems', function ($itemQuery) use ($staff) {
                $itemQuery->where('office_role', $staff->office_role);
            });

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

        $clearances = $query
            ->with(['checklistItems' => function ($itemQuery) use ($staff) {
                $itemQuery->where('office_role', $staff->office_role);
            }])
            ->paginate(20)
            ->withQueryString();

        return view('staff.clearances.index', compact('clearances'));
    }

    /**
     * Approve a clearance
     */
    public function approve(Clearance $clearance)
    {
        $staff = auth()->user();
        
        // Ensure clearance belongs to staff's department
        if ($clearance->department_id !== $staff->department_id || !$staff->office_role) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $affected = $clearance->checklistItems()
            ->where('office_role', $staff->office_role)
            ->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        if ($affected === 0) {
            return response()->json(['error' => 'No clearance item assigned to your office role.'], 403);
        }

        $this->recalculateClearanceStatus($clearance);

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
        if ($clearance->department_id !== $staff->department_id || !$staff->office_role) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'remarks' => 'required|string|max:500'
        ]);

        $hasRoleItem = $clearance->checklistItems()
            ->where('office_role', $staff->office_role)
            ->exists();

        if (!$hasRoleItem) {
            return response()->json(['error' => 'No clearance item assigned to your office role.'], 403);
        }

        $clearance->checklistItems()
            ->where('office_role', $staff->office_role)
            ->update([
                'status' => 'pending',
                'approved_at' => null,
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

        if (!$staff->office_role) {
            return response()->json([
                'success' => false,
                'message' => 'No office role assigned to your account.'
            ], 403);
        }
        
        $validated = $request->validate([
            'clearance_ids' => 'required|array',
            'clearance_ids.*' => 'exists:clearances,id'
        ]);

        $clearances = Clearance::whereIn('id', $validated['clearance_ids'])
            ->where('department_id', $staff->department_id)
            ->whereHas('checklistItems', function ($itemQuery) use ($staff) {
                $itemQuery->where('office_role', $staff->office_role);
            })
            ->get();

        $count = 0;
        foreach ($clearances as $clearance) {
            $affected = $clearance->checklistItems()
                ->where('office_role', $staff->office_role)
                ->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);

            if ($affected > 0) {
                $this->recalculateClearanceStatus($clearance);
                $count++;
            }
        }

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

        if (!$staff->office_role) {
            return back()->with('warning', 'No office role assigned to your account.');
        }
        
        $query = Clearance::with(['student', 'department', 'checklistItems'])
            ->where('department_id', $staff->department_id)
            ->whereHas('checklistItems', function ($itemQuery) use ($staff) {
                $itemQuery->where('office_role', $staff->office_role);
            });

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $clearances = $query->get();

        $filename = "clearances-" . now()->format('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add headers
        fputcsv($handle, ['Student Name', 'Student Email', 'Department', 'Clearance Title', 'Office/Instructor', 'Approval Location', 'Checklist Items', 'Status', 'Remarks', 'Request Date', 'Last Updated']);

        // Add data
        foreach ($clearances as $clearance) {
            $checklistSummary = $clearance->checklistItems
                ->where('office_role', $staff->office_role)
                ->map(function ($item) {
                    $location = $item->location ? ' (' . $item->location . ')' : '';
                    return $item->item_name . $location . ' [' . ucfirst($item->status) . ']';
                })
                ->implode('; ');

            fputcsv($handle, [
                $clearance->student->name,
                $clearance->student->email,
                $clearance->department->name,
                $clearance->clearance_title ?? '',
                $clearance->office_or_instructor ?? '',
                $clearance->approval_location ?? '',
                $checklistSummary,
                ucfirst($clearance->status),
                $clearance->remarks ?? '',
                $clearance->created_at->format('M d, Y H:i'),
                $clearance->updated_at->format('M d, Y H:i')
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Build checklist items from selected checkbox values plus optional custom item.
     */
    private function buildChecklistItems(
        array $selectedItems,
        ?string $customItemName,
        ?string $customItemContact,
        ?string $customItemLocation,
        ?string $customItemOfficeRole
    ): array
    {
        $items = [];

        foreach ($selectedItems as $entry) {
            $entry = trim((string) $entry);
            if ($entry === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $entry, 4));

            $officeRole = $parts[0] ?? null;
            $itemName = $parts[1] ?? null;
            $contactPerson = $parts[2] ?? null;
            $location = $parts[3] ?? null;

            // Backward compatibility with old "item|contact|location" values.
            if (!array_key_exists($officeRole, User::officeRoles())) {
                $itemName = $parts[0] ?? null;
                $contactPerson = $parts[1] ?? null;
                $location = $parts[2] ?? null;
                $officeRole = $this->guessOfficeRole($itemName);
            }

            if (!$itemName) {
                continue;
            }

            $items[] = [
                'item_name' => $itemName,
                'office_role' => $officeRole,
                'contact_person' => $contactPerson,
                'location' => $location,
            ];
        }

        $customItemName = trim((string) $customItemName);
        $customItemContact = trim((string) $customItemContact);
        $customItemLocation = trim((string) $customItemLocation);
        $customItemOfficeRole = trim((string) $customItemOfficeRole);

        if ($customItemName !== '') {
            $items[] = [
                'item_name' => $customItemName,
                'office_role' => array_key_exists($customItemOfficeRole, User::officeRoles())
                    ? $customItemOfficeRole
                    : $this->guessOfficeRole($customItemName),
                'contact_person' => $customItemContact !== '' ? $customItemContact : null,
                'location' => $customItemLocation !== '' ? $customItemLocation : null,
            ];
        }

        return $items;
    }

    /**
     * Infer office role from checklist item text.
     */
    private function guessOfficeRole(?string $itemName): ?string
    {
        $value = strtolower((string) $itemName);

        if (str_contains($value, 'library') || str_contains($value, 'librarian')) {
            return User::OFFICE_ROLE_LIBRARIAN;
        }

        if (str_contains($value, 'registrar')) {
            return User::OFFICE_ROLE_REGISTRAR;
        }

        if (str_contains($value, 'cashier') || str_contains($value, 'account')) {
            return User::OFFICE_ROLE_CASHIER;
        }

        if (str_contains($value, 'guidance')) {
            return User::OFFICE_ROLE_GUIDANCE_COUNSELOR;
        }

        if (str_contains($value, 'chair')) {
            return User::OFFICE_ROLE_DEPARTMENT_CHAIR;
        }

        if (str_contains($value, 'research')) {
            return User::OFFICE_ROLE_RESEARCH_COORDINATOR;
        }

        if (str_contains($value, 'thesis') || str_contains($value, 'adviser') || str_contains($value, 'advisor')) {
            return User::OFFICE_ROLE_THESIS_ADVISER;
        }

        if (str_contains($value, 'student affairs') || str_contains($value, 'osa')) {
            return User::OFFICE_ROLE_STUDENT_AFFAIRS_OFFICER;
        }

        return null;
    }

    /**
     * Recompute clearance status based on all checklist items.
     */
    private function recalculateClearanceStatus(Clearance $clearance): void
    {
        $clearance->load('checklistItems');

        if ($clearance->checklistItems->isEmpty()) {
            return;
        }

        $allApproved = $clearance->checklistItems->every(fn ($item) => $item->status === 'approved');

        $clearance->update([
            'status' => $allApproved ? 'approved' : 'pending',
            'remarks' => $allApproved ? null : $clearance->remarks,
        ]);
    }
}