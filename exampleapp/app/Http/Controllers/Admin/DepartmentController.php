<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments
     */
    public function index()
    {
        $departments = Department::with('college')->get();
        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Show form to create new department
     */
    public function create()
    {
        $colleges = College::all();
        return view('admin.departments.create', compact('colleges'));
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => 'required|string|max:255|unique:departments,name,NULL,id,college_id,' . $request->college_id
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Show form to edit department
     */
    public function edit(Department $department)
    {
        $colleges = College::all();
        return view('admin.departments.edit', compact('department', 'colleges'));
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id . ',id,college_id,' . $request->college_id
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department
     */
    public function destroy(Department $department)
    {
        // Check if department has staff
        if ($department->staff()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Cannot delete department with assigned staff.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    /**
     * Get departments by college (for AJAX requests)
     */
    public function getByCollege(College $college)
    {
        return response()->json($college->departments);
    }
}