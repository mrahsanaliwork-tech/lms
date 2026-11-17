<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomationLog;
use App\Models\AutomationWorkflow;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AutomationWorkflowController extends Controller
{
    public function index()
    {
        $workflows = AutomationWorkflow::withCount('logs')
            ->latest()
            ->get()
            ->map(fn($w) => [
                'id'           => $w->id,
                'name'         => $w->name,
                'trigger_type' => $w->trigger_type,
                'is_active'    => $w->is_active,
                'last_run_at'  => $w->last_run_at?->toISOString(),
                'logs_count'   => $w->logs_count,
                'actions'      => count($w->actions ?? []),
            ]);

        $recentLogs = AutomationLog::with('workflow:id,name')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($l) => [
                'id'            => $l->id,
                'workflow_name' => $l->workflow?->name ?? 'Deleted',
                'trigger'       => $l->trigger,
                'status'        => $l->status,
                'error'         => $l->error,
                'created_at'    => $l->created_at->toISOString(),
            ]);

        return Inertia::render('Admin/Automation/Index', [
            'workflows'  => $workflows,
            'recentLogs' => $recentLogs,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Automation/Builder', [
            'workflow' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'trigger_type' => 'required|string',
            'conditions'   => 'nullable|array',
            'actions'      => 'required|array|min:1',
            'is_active'    => 'boolean',
        ]);

        $workflow = AutomationWorkflow::create($data);

        return redirect()->route('admin.automation.index')
            ->with('success', "Workflow \"{$workflow->name}\" created.");
    }

    public function edit(AutomationWorkflow $automation)
    {
        return Inertia::render('Admin/Automation/Builder', [
            'workflow' => $automation,
        ]);
    }

    public function update(Request $request, AutomationWorkflow $automation)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'trigger_type' => 'required|string',
            'conditions'   => 'nullable|array',
            'actions'      => 'required|array|min:1',
            'is_active'    => 'boolean',
        ]);

        $automation->update($data);

        return redirect()->route('admin.automation.index')
            ->with('success', "Workflow updated.");
    }

    public function toggle(AutomationWorkflow $automation)
    {
        $automation->update(['is_active' => !$automation->is_active]);

        return back()->with('success', 'Workflow ' . ($automation->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function destroy(AutomationWorkflow $automation)
    {
        $automation->delete();

        return back()->with('success', 'Workflow deleted.');
    }
}
