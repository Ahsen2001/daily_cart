<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if(in_array($role->name, ['Super Admin', 'Admin', 'Vendor', 'Customer', 'Rider'], true) && $request->user()->hasPrimaryRole('Admin'), 403);

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('status', 'Role permissions updated.');
    }
}
