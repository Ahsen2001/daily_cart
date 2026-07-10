<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    public function index(): View
    {
        $admins = User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'Admin'))
            ->latest()
            ->paginate(15);

        return view('admin.management.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('admin.management.admins.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'status' => ['required', 'in:active,suspended,inactive'],
        ]);

        $role = Role::findByName('Admin', 'web');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'role_id' => $role->id,
        ]);

        $user->assignRole('Admin');

        return redirect()->route('super-admin.admins.index')->with('status', 'Admin account created successfully.');
    }

    public function edit(User $admin): View
    {
        abort_if($admin->hasRole('Super Admin'), 403);
        return view('admin.management.admins.edit', [
            'admin' => $admin,
        ]);
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        abort_if($admin->hasRole('Super Admin'), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $admin->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'status' => ['required', 'in:active,suspended,inactive'],
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $admin->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('super-admin.admins.index')->with('status', 'Admin account updated successfully.');
    }

    public function suspend(User $admin): RedirectResponse
    {
        abort_if($admin->hasRole('Super Admin'), 403);

        $newStatus = $admin->status === 'suspended' ? 'active' : 'suspended';
        $admin->update(['status' => $newStatus]);

        return redirect()->route('super-admin.admins.index')
            ->with('status', "Admin status updated to {$newStatus}.");
    }

    public function destroy(User $admin): RedirectResponse
    {
        abort_if($admin->hasRole('Super Admin'), 403);

        $admin->delete();

        return redirect()->route('super-admin.admins.index')->with('status', 'Admin account deleted successfully.');
    }
}
