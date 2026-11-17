<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::withCount('students')
            ->latest()
            ->paginate(20)
            ->through(fn ($o) => [
                'id'             => $o->id,
                'name'           => $o->name,
                'slug'           => $o->slug,
                'status'         => $o->status,
                'students_count' => $o->students_count,
                'created_at'     => $o->created_at->format('M d, Y'),
            ]);

        return Inertia::render('Admin/Organizations/Index', compact('organizations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        Organization::create([
            'name'   => $data['name'],
            'slug'   => Str::slug($data['name']) . '-' . Str::random(4),
            'status' => $data['status'],
        ]);

        return back()->with('success', 'Organization created successfully.');
    }

    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $organization->update($data);

        return back()->with('success', 'Organization updated.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return back()->with('success', 'Organization deleted.');
    }
}
