<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->latest()->paginate(25),
        ]);
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        abort_if($user->is_admin, 422, 'Administrator accounts cannot be disabled here.');

        $user->update(['is_active' => ! $user->is_active]);

        return to_route('admin.users.index')->with('status', sprintf('%s has been %s.', $user->name, $user->is_active ? 'enabled' : 'disabled'));
    }
}
