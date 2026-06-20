<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::orderBy('name')->pluck('name');

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'No podés cambiar tu propio rol.');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(Role::pluck('name'))],
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', "Rol de {$user->name} actualizado a \"{$validated['role']}\".");
    }
}
