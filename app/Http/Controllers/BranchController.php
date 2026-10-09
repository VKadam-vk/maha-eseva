<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BranchController extends Controller
{
    /**
     * Branches listing.
     */
    public function index(): View
    {
        $branches = Branch::withCount(['users', 'employees', 'customers', 'applications'])->paginate(15);
        return view('branches.index', compact('branches'));
    }

    /**
     * Show create branch form.
     */
    public function create(): View
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isBusinessOwner()) {
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
        if (!$user->isSuperAdmin() && !$user->isBusinessOwner()) {
            abort(403, 'Unauthorized: Only business owners can create new branches.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'branch_code' => ['required', 'string', 'max:30'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_mobile' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:255'],
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

        $branch = Branch::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'branch_code' => strtoupper($validated['branch_code']),
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

        AuditService::log('BRANCH_CREATED', $branch, null, $branch->toArray());

        return redirect()->route('branches.index')->with('success', "Branch {$branch->name} created successfully.");
    }

    /**
     * Show edit branch form.
     */
    public function edit(Branch $branch): View
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isBusinessOwner()) {
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
        if (!$user->isSuperAdmin() && !$user->isBusinessOwner()) {
            abort(403, 'Unauthorized: Only business owners can edit branch settings.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'branch_code' => ['required', 'string', 'max:30'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_mobile' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        $branch->update($validated);
        AuditService::log('BRANCH_UPDATED', $branch);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }
}
