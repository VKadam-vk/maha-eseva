<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class BranchController extends Controller
{
    /**
     * Branches listing.
     */
    public function index(): View
    {
        $user = Auth::user();
        if ((!$user->isSuperAdmin() && !$user->isBusinessOwner()) || $user->isBranchAdmin()) {
            abort(403, 'Unauthorized: Only business owners can manage branches.');
        }

        $branches = Branch::where('tenant_id', $user->tenant_id)
            ->withCount(['users', 'employees', 'customers', 'applications'])
            ->paginate(15);
        return view('branches.index', compact('branches'));
    }

    /**
     * Show create branch form.
     */
    public function create(): View
    {
        $user = Auth::user();
        if ((!$user->isSuperAdmin() && !$user->isBusinessOwner()) || $user->isBranchAdmin()) {
            abort(403, 'Unauthorized: Only business owners can create new branches.');
        }

        return view('branches.create');
    }

    /**
     * Store new branch.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ((!$user->isSuperAdmin() && !$user->isBusinessOwner()) || $user->isBranchAdmin()) {
            abort(403, 'Unauthorized: Only business owners can create new branches.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'branch_code' => ['required', 'string', 'max:30'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'aadhaar_number' => ['nullable', 'string', 'max:20'],
            'shop_licence_number' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_mobile' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
        ]);

        $tenantId = $user->tenant_id;

        // Check subscription branch limit
        $existingCount = Branch::where('tenant_id', $tenantId)->count();
        $maxBranches = $user->tenant?->max_branches ?? 5;
        if ($existingCount >= $maxBranches) {
            return back()->withErrors(['name' => "Branch limit of {$maxBranches} reached for your plan. Please upgrade your subscription."]);
        }

        $branchPassword = !empty($validated['password']) ? $validated['password'] : '12345678';
        $createdUserEmail = null;

        $branch = DB::transaction(function () use ($validated, $tenantId, $existingCount, $branchPassword, &$createdUserEmail) {
            $branch = Branch::create([
                'tenant_id' => $tenantId,
                'name' => $validated['name'],
                'branch_code' => strtoupper($validated['branch_code']),
                'owner_name' => $validated['owner_name'] ?? null,
                'pan_number' => !empty($validated['pan_number']) ? strtoupper(trim($validated['pan_number'])) : null,
                'aadhaar_number' => $validated['aadhaar_number'] ?? null,
                'shop_licence_number' => $validated['shop_licence_number'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'contact_mobile' => $validated['contact_mobile'] ?? null,
                'contact_email' => $validated['contact_email'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'district' => $validated['district'] ?? null,
                'pincode' => $validated['pincode'] ?? null,
                'status' => 'ACTIVE',
                'is_main_branch' => ($existingCount === 0),
            ]);

            // Auto-create Branch Admin user account if email is provided
            $adminEmail = $validated['contact_email'] ?? null;
            if ($adminEmail) {
                $existingUser = User::where('email', $adminEmail)->first();
                if (!$existingUser) {
                    $branchUser = User::create([
                        'tenant_id' => $tenantId,
                        'branch_id' => $branch->id,
                        'name' => $validated['contact_person'] ?: ($validated['owner_name'] ?: $branch->name . ' Admin'),
                        'email' => $adminEmail,
                        'mobile' => $validated['contact_mobile'] ?: '',
                        'password' => Hash::make($branchPassword),
                        'status' => 'ACTIVE',
                    ]);

                    $branchAdminRole = Role::where('slug', 'BRANCH_ADMIN')->first();
                    if ($branchAdminRole) {
                        $branchUser->roles()->attach($branchAdminRole->id);
                        $branchUser->permissions()->sync($branchAdminRole->permissions()->pluck('permissions.id')->toArray());
                    }
                    $createdUserEmail = $adminEmail;
                }
            }

            return $branch;
        });

        AuditService::log('BRANCH_CREATED', $branch, null, $branch->toArray());

        $successMsg = "Branch {$branch->name} created successfully.";
        if ($createdUserEmail) {
            $successMsg .= " Branch Admin login created for {$createdUserEmail} with default password: {$branchPassword}.";
        }

        return redirect()->route('branches.index')->with('success', $successMsg);
    }

    /**
     * Show edit branch form.
     */
    public function edit(Branch $branch): View
    {
        $user = Auth::user();
        if ((!$user->isSuperAdmin() && !$user->isBusinessOwner()) || $user->isBranchAdmin()) {
            abort(403, 'Unauthorized: Only business owners can edit branch settings.');
        }

        return view('branches.edit', compact('branch'));
    }

    /**
     * Update branch.
     */
    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $user = Auth::user();
        if ((!$user->isSuperAdmin() && !$user->isBusinessOwner()) || $user->isBranchAdmin()) {
            abort(403, 'Unauthorized: Only business owners can edit branch settings.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'branch_code' => ['required', 'string', 'max:30'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'aadhaar_number' => ['nullable', 'string', 'max:20'],
            'shop_licence_number' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_mobile' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        if (!empty($validated['pan_number'])) {
            $validated['pan_number'] = strtoupper(trim($validated['pan_number']));
        }
        $validated['branch_code'] = strtoupper(trim($validated['branch_code']));

        $branch->update($validated);
        AuditService::log('BRANCH_UPDATED', $branch);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }
}
