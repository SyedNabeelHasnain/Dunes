<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriberGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSubscriberGroupController extends Controller
{
    /**
     * Display groups listing.
     */
    public function index()
    {
        $groups = SubscriberGroup::withCount([
            'subscribers',
            'subscribers as active_subscribers_count' => function ($q) {
                $q->where('status', 'subscribed');
            }
        ])->get();

        return view('admin.subscriber-groups.index', compact('groups'));
    }

    /**
     * Store new group.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:subscriber_groups,name',
            'description' => 'nullable|string|max:500',
        ]);

        $group = SubscriberGroup::create([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        return redirect()->route('admin.subscriber-groups.index')
            ->with('success', "Group '{$group->name}' created successfully.");
    }

    /**
     * Update an existing group.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $group = SubscriberGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => "required|string|max:150|unique:subscriber_groups,name,{$id}",
            'description' => 'nullable|string|max:500',
        ]);

        $group->update([
            'name' => trim($validated['name']),
            'slug' => $group->is_system ? $group->slug : Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.subscriber-groups.index')
            ->with('success', "Group '{$group->name}' updated successfully.");
    }

    /**
     * Delete a group (protecting system groups).
     */
    public function destroy(int $id): RedirectResponse
    {
        $group = SubscriberGroup::findOrFail($id);

        if ($group->is_system) {
            return back()->with('error', "System group '{$group->name}' cannot be deleted.");
        }

        $name = $group->name;
        $group->subscribers()->detach();
        $group->delete();

        return redirect()->route('admin.subscriber-groups.index')
            ->with('success', "Group '{$name}' deleted successfully.");
    }

    /**
     * Redirect create request to index modal.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.subscriber-groups.index');
    }

    /**
     * Show subscribers belonging to this group.
     */
    public function show(int $id): RedirectResponse
    {
        return redirect()->route('admin.subscribers.index', ['group_id' => $id]);
    }

    /**
     * Redirect edit request to index modal.
     */
    public function edit(int $id): RedirectResponse
    {
        return redirect()->route('admin.subscriber-groups.index');
    }
}
