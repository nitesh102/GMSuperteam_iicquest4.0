<?php

namespace App\Http\Controllers;

use App\Models\ComplaintCategory;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ComplaintCategory/Index', [
            'categories' => ComplaintCategory::with('department')->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $validated['created_by'] = $request->user()->id;

        ComplaintCategory::create($validated);

        return redirect()->route('complaint-categories.index')
            ->with('success', 'Complaint category created successfully.');
    }

    public function update(Request $request, ComplaintCategory $complaintCategory): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $validated['updated_by'] = $request->user()->id;

        $complaintCategory->update($validated);

        return redirect()->route('complaint-categories.index')
            ->with('success', 'Complaint category updated successfully.');
    }

    public function destroy(ComplaintCategory $complaintCategory): RedirectResponse
    {
        $complaintCategory->delete();

        return redirect()->route('complaint-categories.index')
            ->with('success', 'Complaint category deleted successfully.');
    }
}
