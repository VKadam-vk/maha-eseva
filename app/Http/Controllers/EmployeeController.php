<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeTask;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * Employees directory.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Employee::with(['user.permissions', 'branch', 'services'])->withCount(['applications', 'tasks', 'followUps']);

        if ($user->isBranchAdmin() || $user->isEmployee()) {
            $query->where('branch_id', $user->branch_id);
        }

        $employees = $query->paginate(15);
        $branches = $user->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();

        return view('employees.index', compact('employees', 'branches'));
    }

    /**
     * Show create employee form with permissions and services.
     */
    public function create(): View
    {
        $user = Auth::user();
        $branches = $user->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();
        $permissions = Permission::all()->groupBy('module');
        $services = Service::with('category')->where('is_active', true)->orderBy('main_service_name')->get()->groupBy(function ($s) {
            return $s->category?->name ?? 'इतर सेवा (General Services)';
        });

        // Default recommended permissions for counter operator
        $defaultPermissionSlugs = [
            'customers.view', 'customers.create', 'customers.edit',
            'applications.view', 'applications.create', 'applications.edit',
            'documents.view', 'documents.upload',
            'payments.view', 'payments.create',
            'services.view',
        ];
        $defaultPermissionIds = Permission::whereIn('slug', $defaultPermissionSlugs)->pluck('id')->toArray();

        return view('employees.create', compact('branches', 'permissions', 'services', 'defaultPermissionIds'));
    }

    /**
     * Store new employee (Creates User record + Employee record + assigns EMPLOYEE role + specific permissions & services).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'branch_id' => ['required', 'exists:branches,id'],
            'designation' => ['required', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'id_proof_type' => ['nullable', 'string', 'max:50'],
            'id_proof_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:services,id'],
            'all_services' => ['nullable'],
        ]);

        $authUser = Auth::user();
        $tenantId = $authUser->tenant_id;

        // Prevent cross-branch employee creation by Branch Admin
        if ($authUser->isBranchAdmin() && (int) $validated['branch_id'] !== (int) $authUser->branch_id) {
            abort(403, 'Unauthorized: Branch Admin can only create staff within their assigned branch.');
        }

        DB::transaction(function () use ($request, $validated, $tenantId) {
            $user = User::create([
                'tenant_id' => $tenantId,
                'branch_id' => $validated['branch_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'password' => Hash::make($validated['password']),
                'status' => 'ACTIVE',
            ]);

            $employeeRole = Role::where('slug', 'EMPLOYEE')->first();
            if ($employeeRole) {
                $user->roles()->attach($employeeRole->id);
            }

            // Save selected permissions
            if ($request->filled('permissions')) {
                $user->permissions()->sync($request->input('permissions'));
            } elseif ($employeeRole) {
                // If not specified, default to employee role's standard permissions
                $user->permissions()->sync($employeeRole->permissions()->pluck('permissions.id')->toArray());
            }

            $count = Employee::where('tenant_id', $tenantId)->count() + 1;
            $code = sprintf('EMP-%04d', $count);
            while (Employee::where('tenant_id', $tenantId)->where('employee_code', $code)->exists()) {
                $count++;
                $code = sprintf('EMP-%04d', $count);
            }

            $employee = Employee::create([
                'tenant_id' => $tenantId,
                'branch_id' => $validated['branch_id'],
                'user_id' => $user->id,
                'employee_code' => $code,
                'designation' => $validated['designation'],
                'joining_date' => $validated['joining_date'] ?? now()->toDateString(),
                'salary' => $validated['salary'] ?? 0.00,
                'id_proof_type' => $validated['id_proof_type'] ?? null,
                'id_proof_number' => $validated['id_proof_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => 'ACTIVE',
            ]);

            // Save permitted services if not "all services"
            if (!$request->boolean('all_services') && $request->filled('services')) {
                $employee->services()->sync($request->input('services'));
            }

            AuditService::log('EMPLOYEE_CREATED', $employee, null, $employee->toArray());
        });

        return redirect()->route('employees.index')->with('success', 'Employee created successfully with assigned permissions and services.');
    }

    /**
     * Show edit employee form with current permissions and services.
     */
    public function edit(Employee $employee): View
    {
        $authUser = Auth::user();
        if ($authUser->isBranchAdmin() && (int) $employee->branch_id !== (int) $authUser->branch_id) {
            abort(403, 'Unauthorized: Branch Admin can only edit staff within their assigned branch.');
        }

        $employee->load(['user.permissions', 'services', 'branch']);
        $branches = $authUser->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();
        $permissions = Permission::all()->groupBy('module');
        $services = Service::with('category')->where('is_active', true)->orderBy('main_service_name')->get()->groupBy(function ($s) {
            return $s->category?->name ?? 'इतर सेवा (General Services)';
        });

        $assignedPermissionIds = $employee->user->permissions()->pluck('permissions.id')->toArray();
        if (empty($assignedPermissionIds)) {
            $role = $employee->user->roles()->first();
            $assignedPermissionIds = $role ? $role->permissions()->pluck('permissions.id')->toArray() : [];
        }

        $assignedServiceIds = $employee->services()->pluck('services.id')->toArray();
        $hasAllServices = empty($assignedServiceIds);

        return view('employees.edit', compact(
            'employee', 'branches', 'permissions', 'services',
            'assignedPermissionIds', 'assignedServiceIds', 'hasAllServices'
        ));
    }

    /**
     * Update employee profile, permissions and services.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $authUser = Auth::user();
        if ($authUser->isBranchAdmin() && (int) $employee->branch_id !== (int) $authUser->branch_id) {
            abort(403, 'Unauthorized: Branch Admin can only edit staff within their assigned branch.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $employee->user_id],
            'mobile' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
            'branch_id' => ['required', 'exists:branches,id'],
            'designation' => ['required', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'id_proof_type' => ['nullable', 'string', 'max:50'],
            'id_proof_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE,ON_LEAVE'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:services,id'],
            'all_services' => ['nullable'],
        ]);

        DB::transaction(function () use ($request, $validated, $employee) {
            $userUpdates = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'branch_id' => $validated['branch_id'],
                'status' => $validated['status'] === 'ACTIVE' ? 'ACTIVE' : 'INACTIVE',
            ];

            if (!empty($validated['password'])) {
                $userUpdates['password'] = Hash::make($validated['password']);
            }

            $employee->user->update($userUpdates);

            $employee->update([
                'branch_id' => $validated['branch_id'],
                'designation' => $validated['designation'],
                'joining_date' => $validated['joining_date'] ?? $employee->joining_date,
                'salary' => $validated['salary'] ?? 0.00,
                'id_proof_type' => $validated['id_proof_type'] ?? null,
                'id_proof_number' => $validated['id_proof_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'],
            ]);

            // Sync direct user permissions
            $permissions = $request->input('permissions', []);
            $employee->user->permissions()->sync($permissions);

            // Sync services
            if ($request->boolean('all_services')) {
                $employee->services()->detach(); // no restrictions = full access
            } else {
                $services = $request->input('services', []);
                $employee->services()->sync($services);
            }

            AuditService::log('EMPLOYEE_UPDATED', $employee, null, $employee->toArray());
        });

        return redirect()->route('employees.index')->with('success', "Employee {$employee->user->name}'s permissions and services updated successfully.");
    }

    /**
     * Show employee workload and assigned tasks.
     */
    public function show(Employee $employee): View
    {
        $employee->load([
            'user',
            'branch',
            'applications.customer',
            'applications.service',
            'tasks',
            'followUps.customer',
        ]);

        return view('employees.show', compact('employee'));
    }

    /**
     * Create employee task.
     */
    public function storeTask(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:LOW,MEDIUM,HIGH,URGENT'],
            'due_date' => ['nullable', 'date'],
            'application_id' => ['nullable', 'exists:applications,id'],
        ]);

        EmployeeTask::create([
            'tenant_id' => $employee->tenant_id,
            'branch_id' => $employee->branch_id,
            'employee_id' => $employee->id,
            'application_id' => $validated['application_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'status' => 'PENDING',
            'due_date' => $validated['due_date'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Task assigned to employee.');
    }

    /**
     * Update task status.
     */
    public function updateTaskStatus(Request $request, EmployeeTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED'],
            'remarks' => ['nullable', 'string'],
        ]);

        $task->update([
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? $task->remarks,
            'completed_at' => ($validated['status'] === 'COMPLETED') ? now() : null,
        ]);

        return back()->with('success', 'Task status updated.');
    }
}
