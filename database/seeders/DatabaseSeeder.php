<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CustomerCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's clean production baseline database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'PLATFORM_SUPER_ADMIN'],
            ['name' => 'Platform Super Admin', 'description' => 'Platform super administrator with global system access']
        );
        $businessOwnerRole = Role::firstOrCreate(
            ['slug' => 'BUSINESS_OWNER'],
            ['name' => 'Business Owner', 'description' => 'Business tenant administrator with complete organization access']
        );
        $branchAdminRole = Role::firstOrCreate(
            ['slug' => 'BRANCH_ADMIN'],
            ['name' => 'Branch Admin', 'description' => 'Branch administrator with full access to branch operations']
        );
        $employeeRole = Role::firstOrCreate(
            ['slug' => 'EMPLOYEE'],
            ['name' => 'Employee / Operator', 'description' => 'Service counter operator restricted to assigned branch']
        );
        $customerRole = Role::firstOrCreate(
            ['slug' => 'CUSTOMER'],
            ['name' => 'Customer', 'description' => 'End customer for application tracking']
        );

        // 2. Seed Permissions
        $permissions = [
            // Customers
            ['name' => 'View Customers', 'slug' => 'customers.view', 'module' => 'customers'],
            ['name' => 'Create Customers', 'slug' => 'customers.create', 'module' => 'customers'],
            ['name' => 'Edit Customers', 'slug' => 'customers.edit', 'module' => 'customers'],
            ['name' => 'Delete Customers', 'slug' => 'customers.delete', 'module' => 'customers'],

            // Applications
            ['name' => 'View Applications', 'slug' => 'applications.view', 'module' => 'applications'],
            ['name' => 'Create Applications', 'slug' => 'applications.create', 'module' => 'applications'],
            ['name' => 'Edit Applications', 'slug' => 'applications.edit', 'module' => 'applications'],
            ['name' => 'Delete Applications', 'slug' => 'applications.delete', 'module' => 'applications'],
            ['name' => 'Assign Applications', 'slug' => 'applications.assign', 'module' => 'applications'],

            // Documents
            ['name' => 'View Documents', 'slug' => 'documents.view', 'module' => 'documents'],
            ['name' => 'Upload Documents', 'slug' => 'documents.upload', 'module' => 'documents'],
            ['name' => 'Verify Documents', 'slug' => 'documents.verify', 'module' => 'documents'],
            ['name' => 'Delete Documents', 'slug' => 'documents.delete', 'module' => 'documents'],

            // Payments
            ['name' => 'View Payments', 'slug' => 'payments.view', 'module' => 'payments'],
            ['name' => 'Create Payments', 'slug' => 'payments.create', 'module' => 'payments'],
            ['name' => 'Edit Payments', 'slug' => 'payments.edit', 'module' => 'payments'],
            ['name' => 'Refund Payments', 'slug' => 'payments.refund', 'module' => 'payments'],

            // Employees
            ['name' => 'View Employees', 'slug' => 'employees.view', 'module' => 'employees'],
            ['name' => 'Create Employees', 'slug' => 'employees.create', 'module' => 'employees'],
            ['name' => 'Edit Employees', 'slug' => 'employees.edit', 'module' => 'employees'],

            // Branches
            ['name' => 'View Branches', 'slug' => 'branches.view', 'module' => 'branches'],
            ['name' => 'Create Branches', 'slug' => 'branches.create', 'module' => 'branches'],
            ['name' => 'Edit Branches', 'slug' => 'branches.edit', 'module' => 'branches'],

            // Services
            ['name' => 'View Services', 'slug' => 'services.view', 'module' => 'services'],
            ['name' => 'Create Services', 'slug' => 'services.create', 'module' => 'services'],
            ['name' => 'Edit Services', 'slug' => 'services.edit', 'module' => 'services'],
            ['name' => 'Delete Services', 'slug' => 'services.delete', 'module' => 'services'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'reports'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'module' => 'reports'],

            // Settings & Subscriptions
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'module' => 'settings'],
            ['name' => 'Manage Subscription', 'slug' => 'subscription.manage', 'module' => 'subscription'],
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['slug']] = Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Attach all permissions to Business Owner
        $businessOwnerRole->permissions()->sync(array_map(fn($p) => $p->id, $permissionModels));

        // Branch Admin permissions
        $branchAdminPerms = array_filter($permissionModels, function ($p) {
            return !in_array($p->slug, ['branches.create', 'subscription.manage', 'services.delete', 'customers.delete']);
        });
        $branchAdminRole->permissions()->sync(array_map(fn($p) => $p->id, $branchAdminPerms));

        // Employee permissions
        $employeePerms = array_filter($permissionModels, function ($p) {
            return in_array($p->slug, [
                'customers.view', 'customers.create', 'customers.edit',
                'applications.view', 'applications.create', 'applications.edit',
                'documents.view', 'documents.upload',
                'payments.view', 'payments.create',
                'services.view',
            ]);
        });
        $employeeRole->permissions()->sync(array_map(fn($p) => $p->id, $employeePerms));

        // 3. Seed Tenant (Business Organization)
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'maha-e-seva'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Maha E-Seva Kendra & Digital Services',
                'business_type' => 'MAHA_E_SEVA',
                'contact_name' => 'Ajit Deshmukh',
                'contact_email' => 'admin@mahaeseva.com',
                'contact_mobile' => '9876543210',
                'address' => 'Shop No. 1, Main Market Road',
                'city' => 'Pune',
                'district' => 'Pune',
                'state' => 'Maharashtra',
                'pincode' => '411001',
                'status' => 'ACTIVE',
                'subscription_plan' => 'ENTERPRISE',
                'subscription_expires_at' => now()->addYears(5),
                'max_branches' => 10,
                'max_users' => 50,
                'max_storage_mb' => 20480,
                'settings_json' => [
                    'sms_sender_id' => 'MHSEVA',
                    'currency' => 'INR',
                    'thermal_receipt_header' => 'MAHA E-SEVA SERVICES',
                    'whatsapp_default_template_lang' => 'mr',
                    'whatsapp_broadcast_default_service' => 'शासकीय सेवा व योजना',
                    'whatsapp_broadcast_default_status' => 'नवीन नोंदणी सुरू आहे',
                    'whatsapp_broadcast_default_app_no' => 'MAHA-ALERT',
                    'advertising_banner_enabled' => false,
                ],
            ]
        );

        // 4. Seed 1 Clean Main Head Office Branch
        $mainBranch = Branch::firstOrCreate(
            ['branch_code' => 'HO-01'],
            [
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'name' => 'Main Head Office',
                'contact_person' => 'Ajit Deshmukh',
                'contact_mobile' => '9876543210',
                'contact_email' => 'admin@mahaeseva.com',
                'address' => 'Shop No. 1, Main Market Road',
                'city' => 'Pune',
                'district' => 'Pune',
                'state' => 'Maharashtra',
                'pincode' => '411001',
                'status' => 'ACTIVE',
                'is_main_branch' => true,
            ]
        );

        // 5. Seed Users
        $seedPassword = env('SEED_DEFAULT_PASSWORD', 'Password@123');

        // Platform Super Admin
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@mahaeseva.gov.in'],
            [
                'tenant_id' => null,
                'branch_id' => null,
                'name' => 'Platform Super Administrator',
                'mobile' => '9000000000',
                'password' => Hash::make($seedPassword),
                'status' => 'ACTIVE',
            ]
        );
        $superAdminUser->roles()->sync([$superAdminRole->id]);

        // Business Owner
        $businessOwnerUser = User::firstOrCreate(
            ['email' => 'admin@mahaeseva.com'],
            [
                'tenant_id' => $tenant->id,
                'branch_id' => $mainBranch->id,
                'name' => 'Ajit Deshmukh (Owner)',
                'mobile' => '9876543210',
                'password' => Hash::make($seedPassword),
                'status' => 'ACTIVE',
            ]
        );
        $businessOwnerUser->roles()->sync([$businessOwnerRole->id]);

        // 6. Seed Standard Customer Categories
        CustomerCategory::firstOrCreate(['tenant_id' => $tenant->id, 'code' => 'REG'], ['name' => 'Regular Citizen', 'discount_percentage' => 0.00]);
        CustomerCategory::firstOrCreate(['tenant_id' => $tenant->id, 'code' => 'SR'], ['name' => 'Senior Citizen', 'discount_percentage' => 10.00]);
        CustomerCategory::firstOrCreate(['tenant_id' => $tenant->id, 'code' => 'STU'], ['name' => 'Student', 'discount_percentage' => 15.00]);
        CustomerCategory::firstOrCreate(['tenant_id' => $tenant->id, 'code' => 'BPL'], ['name' => 'BPL Cardholder', 'discount_percentage' => 20.00]);

        // 7. Seed Standard Service Categories (Empty Services List)
        ServiceCategory::firstOrCreate(['tenant_id' => $tenant->id, 'slug' => 'identity-civil'], ['name' => 'Identity & Civil Documents', 'icon' => 'id-card', 'sort_order' => 1]);
        ServiceCategory::firstOrCreate(['tenant_id' => $tenant->id, 'slug' => 'revenue-land'], ['name' => 'Revenue, Land & Domicile', 'icon' => 'building-bank', 'sort_order' => 2]);
        ServiceCategory::firstOrCreate(['tenant_id' => $tenant->id, 'slug' => 'police-legal'], ['name' => 'Police, Legal & Verification', 'icon' => 'shield-check', 'sort_order' => 3]);
        ServiceCategory::firstOrCreate(['tenant_id' => $tenant->id, 'slug' => 'business-gst'], ['name' => 'Business, GST & Licences', 'icon' => 'briefcase', 'sort_order' => 4]);
        ServiceCategory::firstOrCreate(['tenant_id' => $tenant->id, 'slug' => 'welfare-ration'], ['name' => 'Welfare, Ration & Gazette', 'icon' => 'receipt', 'sort_order' => 5]);
    }
}
