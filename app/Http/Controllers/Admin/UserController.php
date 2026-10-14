<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Access\GrantResolver;
use App\Domain\Access\RoleGrant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('roleGrants')
            ->orderBy('name')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'agency_unit' => $user->agency_unit,
                'phone' => $user->phone,
                'is_active' => $user->is_active,
                'two_factor_enabled' => $user->hasEnabledTwoFactorAuthentication(),
                'role_grants' => $user->roleGrants->map(fn ($g) => [
                    'id' => $g->id,
                    'role' => $g->role,
                    'project_id' => $g->project_id,
                    'program_id' => $g->program_id,
                    'psgc_code' => $g->psgc_code,
                    'created_at' => $g->created_at->format('M d, Y'),
                ]),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => [
                GrantResolver::ROLE_SUPER_ADMIN => 'Super Admin',
                GrantResolver::ROLE_PROJECT_MANAGER => 'Project Manager',
                GrantResolver::ROLE_REVIEWER => 'Regional Reviewer',
                GrantResolver::ROLE_ENCODER => 'Field Encoder',
                GrantResolver::ROLE_PARTNER => 'Contractor / Partner',
                GrantResolver::ROLE_EXECUTIVE_VIEWER => 'Executive Viewer',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'agency_unit' => 'nullable|string|max:128',
            'phone' => 'nullable|string|max:32',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'agency_unit' => $validated['agency_unit'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', "User account for {$user->name} created successfully.");
    }

    public function storeGrant(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in([
                GrantResolver::ROLE_SUPER_ADMIN,
                GrantResolver::ROLE_PROJECT_MANAGER,
                GrantResolver::ROLE_REVIEWER,
                GrantResolver::ROLE_ENCODER,
                GrantResolver::ROLE_PARTNER,
                GrantResolver::ROLE_EXECUTIVE_VIEWER,
            ])],
            'project_id' => 'nullable|integer',
            'program_id' => 'nullable|integer',
            'psgc_code' => 'nullable|string|max:10',
        ]);

        $user->roleGrants()->create([
            'role' => $validated['role'],
            'project_id' => $validated['project_id'] ?? null,
            'program_id' => $validated['program_id'] ?? null,
            'psgc_code' => $validated['psgc_code'] ?? null,
            'granted_by' => $request->user()?->id,
        ]);

        return redirect()->back()->with('success', "Role grant assigned to {$user->name}.");
    }

    public function destroyGrant(RoleGrant $grant): RedirectResponse
    {
        $grant->delete();
        return redirect()->back()->with('success', 'Role grant revoked.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "User account {$user->name} has been {$status}.");
    }
}
