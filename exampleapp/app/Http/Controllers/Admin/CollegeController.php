<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use Illuminate\Http\Request;

class CollegeController extends Controller
{
    /**
     * Display a listing of colleges
     */
    public function index(Request $request)
    {
        $query = College::withCount('departments');

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sort = $request->get('sort', 'name_asc');
        match ($sort) {
            'name_desc' => $query->orderBy('name', 'desc'),
            'departments_desc' => $query->orderBy('departments_count', 'desc'),
            'departments_asc' => $query->orderBy('departments_count', 'asc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('name', 'asc'), // name_asc
        };

        $colleges = $query->get();
        return view('admin.colleges.index', compact('colleges'));
    }

    /**
     * Show form to create new college
     */
    public function create()
    {
        return view('admin.colleges.create');
    }

    /**
     * Store a newly created college
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:colleges'
        ]);

        College::create($validated);

        return redirect()->route('admin.colleges.index')
            ->with('success', 'College created successfully.');
    }

    /**
     * Show form to edit college
     */
    public function edit(College $college)
    {
        return view('admin.colleges.edit', compact('college'));
    }

    /**
     * Update the specified college
     */
    public function update(Request $request, College $college)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:colleges,name,' . $college->id
        ]);

        $college->update($validated);

        return redirect()->route('admin.colleges.index')
            ->with('success', 'College updated successfully.');
    }

    /**
     * Remove the specified college
     */
    public function destroy(College $college)
    {
        // Check if college has departments
        if ($college->departments()->count() > 0) {
            return redirect()->route('admin.colleges.index')
                ->with('error', 'Cannot delete college with existing departments.');
        }

        $college->delete();

        return redirect()->route('admin.colleges.index')
            ->with('success', 'College deleted successfully.');
    }
}