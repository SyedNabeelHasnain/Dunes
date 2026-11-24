<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Governance\ConfigAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserRoleController extends Controller
{
    public function __construct(
        protected ConfigAuditService $audit,
    ) {}

    public function index()
    {
        $users = User::orderBy('id')->get();
        $roles = User::roleDefinitions();
        $permissions = User::availablePermissions();

        return view('admin.governance.users', compact('users', 'roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $roles = array_keys(User::roleDefinitions());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in($roles)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(User::availablePermissions()))],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? [],
            'is_active' => true,
        ]);

        $this->audit->log(
            section: 'rbac',
            action: 'created',
            settingKey: "user:{$user->id}",
            oldValue: null,
            newValue: ['name' => $user->name, 'email' => $user->email, 'role' => $user->role],
            entityType: User::class,
            entityId: $user->id,
            summary: "Created CMS staff account {$user->email} with role {$user->role}",
        );

        return redirect()->route('admin.governance.users.index')->with('success', "Staff account '{$user->name}' created.");
    }

    public function updateRole(Request $request, User $user)
    {
        $roles = array_keys(User::roleDefinitions());
        $validPermissions = array_keys(User::availablePermissions());

        $validated = $request->validate([
            'role' => ['required', Rule::in($roles)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($validPermissions)],
        ]);

        if ($user->id === auth()->id() && $validated['role'] !== User::ROLE_SUPER_ADMIN) {
            return back()->withErrors(['role' => 'You cannot downgrade your own Super Administrator role.']);
        }

        if ($user->role === User::ROLE_SUPER_ADMIN && $validated['role'] !== User::ROLE_SUPER_ADMIN) {
            $otherSuperAdmins = User::where('role', User::ROLE_SUPER_ADMIN)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();
            if ($otherSuperAdmins === 0) {
                return back()->withErrors(['role' => 'At least one active Super Administrator is required.']);
            }
        }

        $old = ['role' => $user->role, 'permissions' => $user->permissions ?? []];
        $new = ['role' => $validated['role'], 'permissions' => $validated['permissions'] ?? []];

        $user->update($new);

        $this->audit->log(
            section: 'rbac',
            action: 'updated',
            settingKey: "user:{$user->id}",
            oldValue: $old,
            newValue: $new,
            entityType: User::class,
            entityId: $user->id,
            summary: "Updated role/permissions for {$user->email} ({$old['role']} -> {$new['role']})",
        );

        return redirect()->route('admin.governance.users.index')->with('success', "Updated role for {$user->name}.");
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        if ($user->role === User::ROLE_SUPER_ADMIN && $user->is_active) {
            $otherSuperAdmins = User::where('role', User::ROLE_SUPER_ADMIN)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();
            if ($otherSuperAdmins === 0) {
                return back()->withErrors(['user' => 'Cannot deactivate the last active Super Administrator.']);
            }
        }

        $oldState = (bool) ($user->is_active ?? true);
        $user->update(['is_active' => ! $oldState]);

        $this->audit->log(
            section: 'rbac',
            action: 'updated',
            settingKey: "user:{$user->id}:is_active",
            oldValue: $oldState ? 'active' : 'inactive',
            newValue: $user->is_active ? 'active' : 'inactive',
            entityType: User::class,
            entityId: $user->id,
            summary: ($user->is_active ? 'Activated' : 'Deactivated')." CMS account {$user->email}",
        );

        return redirect()->route('admin.governance.users.index')->with('success', "Account status updated for {$user->name}.");
    }
}
