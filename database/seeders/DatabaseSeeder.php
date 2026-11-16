<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\Employee;
use App\Models\FollowUp;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCustomField;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!app()->environment('testing') && User::where('email', 'admin@mahaeseva.com')->exists()) {
            $this->seedShravaniBranchData();
            return;
        }

        // 1. Seed Roles
        $superAdminRole = Role::create(['name' => 'Platform Super Admin', 'slug' => 'PLATFORM_SUPER_ADMIN', 'description' => 'Platform super administrator with global system access']);
        $businessOwnerRole = Role::create(['name' => 'Business Owner', 'slug' => 'BUSINESS_OWNER', 'description' => 'Business tenant administrator with complete organization access']);
        $branchAdminRole = Role::create(['name' => 'Branch Admin', 'slug' => 'BRANCH_ADMIN', 'description' => 'Branch administrator with full access to branch operations']);
        $employeeRole = Role::create(['name' => 'Employee / Operator', 'slug' => 'EMPLOYEE', 'description' => 'Service counter operator restricted to assigned branch']);
        $customerRole = Role::create(['name' => 'Customer', 'slug' => 'CUSTOMER', 'description' => 'End customer for application tracking']);

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
            $permissionModels[$p['slug']] = Permission::create($p);
        }

        // Attach all permissions to Business Owner & Branch Admin
        $businessOwnerRole->permissions()->sync(array_column($permissionModels, 'id'));
        
        // Branch Admin permissions (cannot create branches, modify subscription)
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
        $tenant = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Maha E-Seva Kendra & Digital Services',
            'slug' => 'maha-e-seva-pune',
            'business_type' => 'MAHA_E_SEVA',
            'contact_name' => 'Ajit Deshmukh',
            'contact_email' => 'admin@mahaeseva.com',
            'contact_mobile' => '9876543210',
            'address' => 'Shop No. 12, Shivaji Commercial Complex, FC Road',
            'city' => 'Pune',
            'district' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411005',
            'gst_number' => '27ABCDE1234F1Z5',
            'status' => 'ACTIVE',
            'subscription_plan' => 'ENTERPRISE',
            'subscription_expires_at' => now()->addYear(),
            'max_branches' => 10,
            'max_users' => 50,
            'max_storage_mb' => 20480,
            'settings_json' => [
                'sms_sender_id' => 'MHSEVA',
                'currency' => 'INR',
                'thermal_receipt_header' => 'MAHA E-SEVA SERVICES - PUNE',
            ],
        ]);

        // 4. Seed Branches
        $mainBranch = Branch::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Main Head Office - FC Road',
            'branch_code' => 'PUNE-FC-01',
            'contact_person' => 'Ajit Deshmukh',
            'contact_mobile' => '9876543210',
            'contact_email' => 'fc_branch@mahaeseva.com',
            'address' => 'Shop 12, FC Road',
            'city' => 'Pune',
            'district' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411005',
            'status' => 'ACTIVE',
            'is_main_branch' => true,
        ]);

        $branch2 = Branch::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Shivaji Nagar Branch',
            'branch_code' => 'PUNE-SN-02',
            'contact_person' => 'Pooja Kulkarni',
            'contact_mobile' => '9876543211',
            'contact_email' => 'shivajinagar@mahaeseva.com',
            'address' => 'Near District Court, Shivaji Nagar',
            'city' => 'Pune',
            'district' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411005',
            'status' => 'ACTIVE',
            'is_main_branch' => false,
        ]);

        $branch3 = Branch::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Hadapsar Branch',
            'branch_code' => 'PUNE-HD-03',
            'contact_person' => 'Sunil Jadhav',
            'contact_mobile' => '9876543212',
            'contact_email' => 'hadapsar@mahaeseva.com',
            'address' => 'Gadital Chowk, Pune-Solapur Road, Hadapsar',
            'city' => 'Pune',
            'district' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411028',
            'status' => 'ACTIVE',
            'is_main_branch' => false,
        ]);

        // 5. Seed Users
        $seedPassword = env('SEED_DEFAULT_PASSWORD', 'Password@123');

        // Platform Super Admin
        $superAdminUser = User::create([
            'tenant_id' => null,
            'branch_id' => null,
            'name' => 'Platform Super Administrator',
            'email' => 'superadmin@mahaeseva.gov.in',
            'mobile' => '9000000000',
            'password' => Hash::make($seedPassword),
            'status' => 'ACTIVE',
        ]);
        $superAdminUser->roles()->attach($superAdminRole->id);

        // Business Owner
        $businessOwnerUser = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Ajit Deshmukh (Owner)',
            'email' => 'admin@mahaeseva.com',
            'mobile' => '9876543210',
            'password' => Hash::make($seedPassword),
            'status' => 'ACTIVE',
        ]);
        $businessOwnerUser->roles()->attach($businessOwnerRole->id);

        // Branch Admin (Shivaji Nagar)
        $branchAdminUser = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch2->id,
            'name' => 'Pooja Kulkarni (Branch Head)',
            'email' => 'branchadmin@mahaeseva.com',
            'mobile' => '9876543211',
            'password' => Hash::make($seedPassword),
            'status' => 'ACTIVE',
        ]);
        $branchAdminUser->roles()->attach($branchAdminRole->id);

        // Employee 1 (Main Branch Operator)
        $emp1User = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Rahul Shinde',
            'email' => 'employee@mahaeseva.com',
            'mobile' => '9876543220',
            'password' => Hash::make($seedPassword),
            'status' => 'ACTIVE',
        ]);
        $emp1User->roles()->attach($employeeRole->id);

        $employee1 = Employee::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $mainBranch->id,
            'user_id' => $emp1User->id,
            'employee_code' => 'EMP-0001',
            'designation' => 'Senior Service Operator',
            'joining_date' => '2024-01-15',
            'salary' => 25000.00,
            'id_proof_type' => 'AADHAAR',
            'id_proof_number' => '4567-8901-2345',
            'status' => 'ACTIVE',
        ]);

        // Operator user alias (operator@mahaeseva.com)
        $operatorUser = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Service Counter Operator',
            'email' => 'operator@mahaeseva.com',
            'mobile' => '9876543225',
            'password' => Hash::make($seedPassword),
            'status' => 'ACTIVE',
        ]);
        $operatorUser->roles()->attach($employeeRole->id);

        Employee::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $mainBranch->id,
            'user_id' => $operatorUser->id,
            'employee_code' => 'EMP-0002',
            'designation' => 'Counter Operator',
            'joining_date' => '2024-02-01',
            'salary' => 22000.00,
            'id_proof_type' => 'PAN',
            'id_proof_number' => 'ABCDE1234F',
            'status' => 'ACTIVE',
        ]);

        // Employee 2 (Shivaji Nagar Operator)
        $emp2User = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch2->id,
            'name' => 'Sneha Patil',
            'email' => 'sneha@mahaeseva.com',
            'mobile' => '9876543221',
            'password' => Hash::make($seedPassword),
            'status' => 'ACTIVE',
        ]);
        $emp2User->roles()->attach($employeeRole->id);

        $employee2 = Employee::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branch2->id,
            'user_id' => $emp2User->id,
            'employee_code' => 'EMP-0003',
            'designation' => 'Counter Executive',
            'joining_date' => '2024-03-01',
            'salary' => 20000.00,
            'id_proof_type' => 'AADHAAR',
            'id_proof_number' => '8901-2345-6789',
            'status' => 'ACTIVE',
        ]);

        // 6. Seed Customer Categories
        $catRegular = CustomerCategory::create(['tenant_id' => $tenant->id, 'name' => 'Regular Citizen', 'code' => 'REG', 'discount_percentage' => 0.00]);
        $catSenior = CustomerCategory::create(['tenant_id' => $tenant->id, 'name' => 'Senior Citizen', 'code' => 'SR', 'discount_percentage' => 10.00]);
        $catStudent = CustomerCategory::create(['tenant_id' => $tenant->id, 'name' => 'Student', 'code' => 'STU', 'discount_percentage' => 15.00]);
        $catBPL = CustomerCategory::create(['tenant_id' => $tenant->id, 'name' => 'BPL Cardholder', 'code' => 'BPL', 'discount_percentage' => 20.00]);

        // 7. Seed Service Categories
        $secCatIdentity = ServiceCategory::create(['tenant_id' => $tenant->id, 'name' => 'Identity & Civil Documents', 'slug' => 'identity-civil', 'icon' => 'id-card', 'sort_order' => 1]);
        $secCatRevenue = ServiceCategory::create(['tenant_id' => $tenant->id, 'name' => 'Revenue, Land & Domicile', 'slug' => 'revenue-land', 'icon' => 'building-bank', 'sort_order' => 2]);
        $secCatLegal = ServiceCategory::create(['tenant_id' => $tenant->id, 'name' => 'Police, Legal & Verification', 'slug' => 'police-legal', 'icon' => 'shield-check', 'sort_order' => 3]);
        $secCatBusiness = ServiceCategory::create(['tenant_id' => $tenant->id, 'name' => 'Business, GST & Licences', 'slug' => 'business-gst', 'icon' => 'briefcase', 'sort_order' => 4]);
        $secCatWelfare = ServiceCategory::create(['tenant_id' => $tenant->id, 'name' => 'Welfare, Ration & Gazette', 'slug' => 'welfare-ration', 'icon' => 'receipt', 'sort_order' => 5]);

        // 8. Seed Standard Services
        $servicesData = [
            [
                'category_id' => $secCatIdentity->id,
                'service_code' => 'PAN-NEW-01',
                'main_service_name' => 'PAN CARD',
                'sub_service_name' => 'New PAN Application (Form 49A)',
                'service_variant' => 'Physical + e-PAN Card',
                'description' => 'Application for fresh PAN Card for Indian citizen with Aadhaar e-KYC',
                'price' => 200.00,
                'govt_fee' => 107.00,
                'service_charge' => 93.00,
                'expected_processing_days' => 10,
                'required_documents_json' => ['Aadhaar Card', 'Passport Size Photograph', 'Signature on White Paper'],
                'service_portal_link' => 'https://www.onlineservices.nsdl.com/paam/endUserRegisterContact.html',
                'custom_fields' => [
                    ['name' => 'Aadhaar Number', 'key' => 'aadhaar_number', 'type' => 'text', 'label' => '12-Digit Aadhaar Number', 'required' => true],
                    ['name' => 'Father Full Name', 'key' => 'father_name', 'type' => 'text', 'label' => "Father's Full Name", 'required' => true],
                    ['name' => 'Date of Birth as per Aadhaar', 'key' => 'dob_aadhaar', 'type' => 'date', 'label' => 'Date of Birth', 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatIdentity->id,
                'service_code' => 'PAN-CORR-02',
                'main_service_name' => 'PAN CARD',
                'sub_service_name' => 'PAN Correction / Change in Data (CSF)',
                'service_variant' => 'Name/DOB/Photo/Address Update',
                'description' => 'Reprint or correction in existing PAN database',
                'price' => 250.00,
                'govt_fee' => 107.00,
                'service_charge' => 143.00,
                'expected_processing_days' => 12,
                'required_documents_json' => ['Existing PAN Card Copy', 'Aadhaar Card', 'Proof of Change Document', 'Photo'],
                'service_portal_link' => 'https://www.onlineservices.nsdl.com/paam/endUserRegisterContact.html',
                'custom_fields' => [
                    ['name' => 'Existing PAN Number', 'key' => 'existing_pan', 'type' => 'text', 'label' => 'Existing 10-Digit PAN', 'required' => true],
                    ['name' => 'Correction Fields', 'key' => 'correction_type', 'type' => 'select', 'label' => 'Items to Correct', 'options' => ['Name Correction', 'Date of Birth', 'Father Name', 'Photo/Signature Update'], 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatRevenue->id,
                'service_code' => 'INC-TAH-01',
                'main_service_name' => 'INCOME CERTIFICATE',
                'sub_service_name' => 'Income Certificate (Tahsildar - 1 Year)',
                'service_variant' => 'Standard Tahsildar Issue',
                'description' => 'Annual Income Certificate issued by Revenue Authority / Tahsildar via Aaple Sarkar',
                'price' => 150.00,
                'govt_fee' => 33.60,
                'service_charge' => 116.40,
                'expected_processing_days' => 15,
                'required_documents_json' => ['Aadhaar Card', 'Ration Card', 'Salary Slip / IT Return / Talathi Report', 'Electricity Bill / Proof of Residence'],
                'service_portal_link' => 'https://aaplesarkar.mahaonline.gov.in',
                'custom_fields' => [
                    ['name' => 'Annual Income (in Rs)', 'key' => 'annual_income', 'type' => 'number', 'label' => 'Stated Annual Family Income', 'required' => true],
                    ['name' => 'Purpose of Certificate', 'key' => 'certificate_purpose', 'type' => 'text', 'label' => 'Purpose (e.g. Scholarship, College Admission)', 'required' => true],
                    ['name' => 'Talathi Verification Done', 'key' => 'talathi_done', 'type' => 'select', 'label' => 'Talathi Report Available?', 'options' => ['Yes', 'No - Needed'], 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatRevenue->id,
                'service_code' => 'DOM-AGE-01',
                'main_service_name' => 'DOMICILE',
                'sub_service_name' => 'Age, Nationality & Domicile Certificate',
                'service_variant' => 'Maharashtra Domicile',
                'description' => 'Official Domicile and Nationality certificate for Maharashtra residents of 15+ years',
                'price' => 200.00,
                'govt_fee' => 33.60,
                'service_charge' => 166.40,
                'expected_processing_days' => 15,
                'required_documents_json' => ['Aadhaar Card', 'School Leaving Certificate (LC)', '15 Years Continuous Residence Proof', 'Ration Card', 'Passport Photo'],
                'service_portal_link' => 'https://aaplesarkar.mahaonline.gov.in',
                'custom_fields' => [
                    ['name' => 'Years of Residence in Maharashtra', 'key' => 'years_residence', 'type' => 'number', 'label' => 'Years in Maharashtra', 'required' => true],
                    ['name' => 'Birth Place', 'key' => 'birth_place', 'type' => 'text', 'label' => 'Place of Birth (Village/City, District)', 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatLegal->id,
                'service_code' => 'RENT-REG-01',
                'main_service_name' => 'RENT AGREEMENT',
                'sub_service_name' => 'Online Registered Rent Agreement (IGR)',
                'service_variant' => 'Biometric e-Registration at Doorstep',
                'description' => 'Legally registered leave and license agreement with Maharashtra IGR portal with biometric authentication',
                'price' => 1800.00,
                'govt_fee' => 1000.00,
                'service_charge' => 800.00,
                'expected_processing_days' => 2,
                'required_documents_json' => ['Owner Aadhaar & PAN', 'Tenant Aadhaar & PAN', '2 Witnesses Aadhaar Cards', 'Electricity Bill / Property Tax Receipt of Flat'],
                'service_portal_link' => 'https://efilingigr.maharashtra.gov.in',
                'custom_fields' => [
                    ['name' => 'Property Address', 'key' => 'property_address', 'type' => 'textarea', 'label' => 'Complete Rented Property Address', 'required' => true],
                    ['name' => 'Monthly Rent Amount', 'key' => 'monthly_rent', 'type' => 'number', 'label' => 'Monthly Rent (Rs)', 'required' => true],
                    ['name' => 'Security Deposit Amount', 'key' => 'deposit_amount', 'type' => 'number', 'label' => 'Security Deposit (Rs)', 'required' => true],
                    ['name' => 'Tenure (in Months)', 'key' => 'agreement_tenure', 'type' => 'number', 'label' => 'Tenure in Months (e.g. 11)', 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatLegal->id,
                'service_code' => 'POL-VER-01',
                'main_service_name' => 'POLICE VERIFICATION',
                'sub_service_name' => 'Police Clearance Certificate (PCC) / Job Verification',
                'service_variant' => 'Character / Pre-Employment Verification',
                'description' => 'Police clearance certificate application through Maharashtra Police Portal',
                'price' => 300.00,
                'govt_fee' => 123.60,
                'service_charge' => 176.40,
                'expected_processing_days' => 14,
                'required_documents_json' => ['Aadhaar Card', 'Current Address Proof', 'Employment / Company Letter', 'Passport Photo'],
                'service_portal_link' => 'https://pcs.mahaonline.gov.in',
                'custom_fields' => [
                    ['name' => 'Police Station Jurisdiction', 'key' => 'police_station', 'type' => 'text', 'label' => 'Nearest Police Station', 'required' => true],
                    ['name' => 'Employer / Organization Name', 'key' => 'employer_name', 'type' => 'text', 'label' => 'Company Requesting PCC', 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatIdentity->id,
                'service_code' => 'VOT-F6-01',
                'main_service_name' => 'VOTER ID',
                'sub_service_name' => 'New Voter Registration (Form 6)',
                'service_variant' => 'Fresh Voter Card',
                'description' => 'Enrollment in Electoral Roll for Indian citizens aged 18+ via ECINET portal',
                'price' => 100.00,
                'govt_fee' => 0.00,
                'service_charge' => 100.00,
                'expected_processing_days' => 20,
                'required_documents_json' => ['Aadhaar Card', 'Age Proof (Birth Certificate / School LC)', 'Address Proof (Light Bill / Ration Card)', 'Passport Photo'],
                'service_portal_link' => 'https://voters.eci.gov.in',
                'custom_fields' => [
                    ['name' => 'Assembly Constituency', 'key' => 'assembly_constituency', 'type' => 'text', 'label' => 'Assembly Constituency (Vidhan Sabha)', 'required' => true],
                    ['name' => 'Family Member Voter EPIC No (if any)', 'key' => 'family_epic', 'type' => 'text', 'label' => "Family Member's Voter ID No.", 'required' => false],
                ],
            ],
            [
                'category_id' => $secCatWelfare->id,
                'service_code' => 'GAZ-NAME-01',
                'main_service_name' => 'GAZETTE',
                'sub_service_name' => 'Name Change Gazette Notification',
                'service_variant' => 'Government of Maharashtra Gazette',
                'description' => 'Official government publication for legal name change after marriage, religion change or spelling correction',
                'price' => 600.00,
                'govt_fee' => 260.00,
                'service_charge' => 340.00,
                'expected_processing_days' => 15,
                'required_documents_json' => ['Aadhaar Card with Old Name', 'Affidavit for Name Change on Rs.100 Stamp Paper', 'Marriage Certificate (if applicable)', 'Photo'],
                'service_portal_link' => 'https://gazette.mahaonline.gov.in',
                'custom_fields' => [
                    ['name' => 'Old Full Name', 'key' => 'old_name', 'type' => 'text', 'label' => 'Old Name (as on existing records)', 'required' => true],
                    ['name' => 'New Full Name', 'key' => 'new_name', 'type' => 'text', 'label' => 'New Name (to be published in Gazette)', 'required' => true],
                    ['name' => 'Reason for Name Change', 'key' => 'change_reason', 'type' => 'select', 'label' => 'Reason', 'options' => ['After Marriage', 'Astrology / Numerology', 'Spelling Correction', 'Religion Change'], 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatRevenue->id,
                'service_code' => 'CAS-VAL-01',
                'main_service_name' => 'CASTE VALIDITY',
                'sub_service_name' => 'Caste Validity Certificate (Scrutiny Committee)',
                'service_variant' => 'Education / Service / Election Purpose',
                'description' => 'Verification & Validity certificate from Divisional Caste Scrutiny Committee Maharashtra (BARTI / TRTI)',
                'price' => 800.00,
                'govt_fee' => 150.00,
                'service_charge' => 650.00,
                'expected_processing_days' => 45,
                'required_documents_json' => ['Original Caste Certificate', 'School LC of Applicant', 'School LC / 1950/1967 Proof of Father/Grandfather', 'Family Tree (Vanshavali) on Stamp Paper', 'Form 16 / College Recommendation'],
                'service_portal_link' => 'https://castecertificate.mahaonline.gov.in',
                'custom_fields' => [
                    ['name' => 'Caste Category', 'key' => 'caste_category', 'type' => 'select', 'label' => 'Category', 'options' => ['SC', 'ST', 'VJNT', 'OBC', 'SBC', 'SEBC'], 'required' => true],
                    ['name' => 'Sub-Caste Name', 'key' => 'sub_caste', 'type' => 'text', 'label' => 'Exact Sub-Caste Name', 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatIdentity->id,
                'service_code' => 'PAS-FRESH-01',
                'main_service_name' => 'PASSPORT',
                'sub_service_name' => 'Fresh Passport Application (Normal)',
                'service_variant' => '36 Pages Standard Adult Passport',
                'description' => 'Online application and PSK / POPSK appointment scheduling for Indian Passport',
                'price' => 2000.00,
                'govt_fee' => 1500.00,
                'service_charge' => 500.00,
                'expected_processing_days' => 20,
                'required_documents_json' => ['Aadhaar Card', 'PAN Card', 'Proof of Date of Birth (Birth Certificate / School LC)', '10th/12th Passing Certificate for Non-ECR', 'Bank Passbook with Photo'],
                'service_portal_link' => 'https://www.passportindia.gov.in',
                'custom_fields' => [
                    ['name' => 'Preferred Passport Seva Kendra (PSK)', 'key' => 'preferred_psk', 'type' => 'select', 'label' => 'Preferred PSK Location', 'options' => ['Pune PSK (Mundhwa)', 'Pune POPSK (Chinchwad)', 'Satara POPSK', 'Kolhapur POPSK'], 'required' => true],
                    ['name' => 'Non-ECR Eligible', 'key' => 'non_ecr', 'type' => 'select', 'label' => '10th Std Passed (Non-ECR)?', 'options' => ['Yes - 10th Passed or Higher', 'No - ECR'], 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatWelfare->id,
                'service_code' => 'RAT-MEMBER-01',
                'main_service_name' => 'RATION CARD',
                'sub_service_name' => 'Member Addition / Deletion in Ration Card (RCMS)',
                'service_variant' => 'RCMS Food & Civil Supplies Portal',
                'description' => 'Addition of new born child or newly married daughter-in-law in existing Ration Card (NFSA / Non-NFSA)',
                'price' => 150.00,
                'govt_fee' => 0.00,
                'service_charge' => 150.00,
                'expected_processing_days' => 15,
                'required_documents_json' => ['Existing Ration Card Copy', 'Aadhaar Card of New Member', 'Birth Certificate / Marriage Certificate', 'Surrender Certificate (if moving from other card)'],
                'service_portal_link' => 'https://rcms.mahafood.gov.in',
                'custom_fields' => [
                    ['name' => 'Existing 12-Digit Ration Card Number (SRC)', 'key' => 'src_number', 'type' => 'text', 'label' => '12-Digit SRC Ration Card Number', 'required' => true],
                    ['name' => 'Head of Family (HOF) Name', 'key' => 'hof_name', 'type' => 'text', 'label' => 'Name of Head of Family', 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatBusiness->id,
                'service_code' => 'FSS-REG-01',
                'main_service_name' => 'FOOD LICENCE',
                'sub_service_name' => 'FSSAI Basic Food Registration (Form A)',
                'service_variant' => 'Annual Turnover up to Rs. 12 Lakhs (1 to 5 Years)',
                'description' => 'FoSCoS FSSAI 14-digit registration for food vendors, canteens, bakeries, snacks centers',
                'price' => 450.00,
                'govt_fee' => 100.00,
                'service_charge' => 350.00,
                'expected_processing_days' => 7,
                'required_documents_json' => ['Proprietor Aadhaar & PAN', 'Passport Size Photo', 'Premises Proof (Shop Electricity Bill / Rent Agreement)', 'Food Category List'],
                'service_portal_link' => 'https://foscos.fssai.gov.in',
                'custom_fields' => [
                    ['name' => 'Food Business Name (Firm Name)', 'key' => 'business_name', 'type' => 'text', 'label' => 'Food Business / Shop Name', 'required' => true],
                    ['name' => 'Kind of Business', 'key' => 'food_kind', 'type' => 'select', 'label' => 'Business Type', 'options' => ['Restaurant / Eatery', 'Bakery / Sweets', 'Dairy Products', 'Grocery Store', 'Food Stall / Hawkers', 'Catering'], 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatBusiness->id,
                'service_code' => 'GST-REG-01',
                'main_service_name' => 'GST REGISTRATION',
                'sub_service_name' => 'New GST Registration (Regular / Composition)',
                'service_variant' => 'Proprietorship / Partnership / Private Limited',
                'description' => '15-digit GSTIN registration on GST common portal with Aadhaar authentication',
                'price' => 1200.00,
                'govt_fee' => 0.00,
                'service_charge' => 1200.00,
                'expected_processing_days' => 7,
                'required_documents_json' => ['PAN Card of Business / Proprietor', 'Aadhaar Card with Mobile Linked', 'Business Address Proof (Electricity Bill + NOC / Rent Agreement)', 'Bank Account Statement / Cancelled Cheque', 'Photo'],
                'service_portal_link' => 'https://www.gst.gov.in',
                'custom_fields' => [
                    ['name' => 'Trade Name', 'key' => 'trade_name', 'type' => 'text', 'label' => 'Business Trade Name', 'required' => true],
                    ['name' => 'Constitution of Business', 'key' => 'business_constitution', 'type' => 'select', 'label' => 'Constitution', 'options' => ['Proprietorship', 'Partnership', 'Private Limited Company', 'LLP'], 'required' => true],
                    ['name' => 'Principal Nature of Business', 'key' => 'business_nature', 'type' => 'select', 'label' => 'Nature of Activity', 'options' => ['Retail Business', 'Wholesale Business', 'Service Provision', 'Manufacturing', 'Works Contract'], 'required' => true],
                ],
            ],
            [
                'category_id' => $secCatBusiness->id,
                'service_code' => 'SHOP-ACT-01',
                'main_service_name' => 'SHOP ACT',
                'sub_service_name' => 'Maharashtra Shop Act Intimation (Gumasta 0-9 Employees)',
                'service_variant' => 'Form F Intimation Receipt (Lifetime Validity)',
                'description' => 'Registration under Maharashtra Shops and Establishments (Regulation of Employment and Conditions of Service) Act, 2017 via Aaple Sarkar',
                'price' => 350.00,
                'govt_fee' => 0.00,
                'service_charge' => 350.00,
                'expected_processing_days' => 2,
                'required_documents_json' => ['Proprietor Aadhaar Card & PAN Card', 'Shop Front Photograph with Nameboard in Marathi', 'Shop Address Proof (Electricity Bill / Property Tax Receipt)', 'Rent Agreement (if rented)'],
                'service_portal_link' => 'https://mahakamgar.maharashtra.gov.in',
                'custom_fields' => [
                    ['name' => 'Establishment Name (English)', 'key' => 'shop_name_en', 'type' => 'text', 'label' => 'Shop Name in English', 'required' => true],
                    ['name' => 'Establishment Name (Regional Script)', 'key' => 'shop_name_mr', 'type' => 'text', 'label' => 'Shop Name in Regional Script', 'required' => true],
                    ['name' => 'Nature of Business', 'key' => 'shop_nature', 'type' => 'text', 'label' => 'Nature of Business (e.g. Stationery & Xerox)', 'required' => true],
                    ['name' => 'Number of Employees', 'key' => 'num_employees', 'type' => 'number', 'label' => 'Total Employees Working', 'required' => true],
                ],
            ],
        ];

        $createdServices = [];
        foreach ($servicesData as $sData) {
            $customFields = $sData['custom_fields'] ?? [];
            unset($sData['custom_fields']);

            $sData['tenant_id'] = $tenant->id;
            $sData['is_active'] = true;
            $serviceModel = Service::create($sData);
            $createdServices[$sData['service_code']] = $serviceModel;

            foreach ($customFields as $idx => $cf) {
                ServiceCustomField::create([
                    'tenant_id' => $tenant->id,
                    'service_id' => $serviceModel->id,
                    'field_name' => $cf['name'],
                    'field_key' => $cf['key'],
                    'field_type' => $cf['type'],
                    'label' => $cf['label'],
                    'options_json' => $cf['options'] ?? null,
                    'is_required' => $cf['required'] ?? false,
                    'sort_order' => $idx + 1,
                    'is_active' => true,
                ]);
            }
        }

        // 9. Seed Demo Customer (Rajesh Patil) & Application
        $demoCustomer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $mainBranch->id,
            'category_id' => $catRegular->id,
            'customer_code' => 'CUST-00001',
            'name' => 'Rajesh Anant Patil',
            'mobile' => '9822012345',
            'email' => 'rajesh.patil@example.com',
            'gender' => 'MALE',
            'birth_date' => '1988-06-15',
            'occupation' => 'FARMER / BUSINESS',
            'star_rating' => 5,
            'address' => 'At Post Vadgaon, Taluka Haveli, District Pune - 411041',
        ]);

        $panService = $createdServices['PAN-NEW-01'] ?? Service::where('tenant_id', $tenant->id)->first();
        if ($panService) {
            Application::create([
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $mainBranch->id,
                'customer_id' => $demoCustomer->id,
                'service_id' => $panService->id,
                'assigned_employee_id' => $mainEmployee->id ?? null,
                'application_number' => 'SR-' . date('Y') . '-00001',
                'application_date' => now()->toDateString(),
                'sub_service' => $panService->sub_service_name,
                'work_status' => 'IN_PROGRESS',
                'payment_status' => Application::PAYMENT_PAID,
                'base_amount' => 200.00,
                'total_amount' => 200.00,
                'received_amount' => 200.00,
                'remaining_amount' => 0.00,
                'work_details' => 'New PAN Form 49A submitted with Aadhaar biometric e-KYC',
                'documents_list' => 'Aadhaar Card, Passport Photo',
            ]);
        }

        $this->seedShravaniBranchData();
    }

    /**
     * Seed comprehensive realistic test data for Shravani E-Seva Kendra and Vinod B Kadam.
     */
    public function seedShravaniBranchData(): void
    {
        $tenant = Tenant::first();
        if (!$tenant) {
            return;
        }

        // 1. Shravani E-Seva Kendra Branch
        $shravaniBranch = Branch::firstOrCreate(
            ['name' => 'Shravani E-Seva Kendra'],
            [
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_code' => 'MH-SEVA-SHRAVANI',
                'contact_person' => 'VINOD B KADAM',
                'contact_mobile' => '9876543211',
                'contact_email' => 'branchadmin@mahaeseva.com',
                'address' => 'Shop No. 5, Near Bus Stand, Pune-Solapur Road',
                'city' => 'Pune',
                'district' => 'Pune',
                'state' => 'Maharashtra',
                'pincode' => '411028',
                'status' => 'ACTIVE',
                'is_main_branch' => false,
            ]
        );

        // 2. VINOD B KADAM User
        $branchAdminRole = Role::where('slug', 'BRANCH_ADMIN')->first();
        $seedPassword = env('SEED_DEFAULT_PASSWORD', 'Password@123');

        $vinodUser = User::where('email', 'branchadmin@mahaeseva.com')->first();
        if ($vinodUser) {
            $vinodUser->update([
                'name' => 'VINOD B KADAM',
                'branch_id' => $shravaniBranch->id,
            ]);
            if ($branchAdminRole && !$vinodUser->roles()->where('slug', 'BRANCH_ADMIN')->exists()) {
                $vinodUser->roles()->syncWithoutDetaching([$branchAdminRole->id]);
            }
        } else {
            $vinodUser = User::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $shravaniBranch->id,
                'name' => 'VINOD B KADAM',
                'email' => 'branchadmin@mahaeseva.com',
                'mobile' => '9876543211',
                'password' => Hash::make($seedPassword),
                'status' => 'ACTIVE',
            ]);
            if ($branchAdminRole) {
                $vinodUser->roles()->attach($branchAdminRole->id);
            }
        }

        $vinodAlias = User::firstOrCreate(
            ['email' => 'vinod@mahaeseva.com'],
            [
                'tenant_id' => $tenant->id,
                'branch_id' => $shravaniBranch->id,
                'name' => 'VINOD B KADAM',
                'mobile' => '9876543299',
                'password' => Hash::make($seedPassword),
                'status' => 'ACTIVE',
            ]
        );
        if ($branchAdminRole && !$vinodAlias->roles()->where('slug', 'BRANCH_ADMIN')->exists()) {
            $vinodAlias->roles()->syncWithoutDetaching([$branchAdminRole->id]);
        }

        // 3. Ensure required testing services exist
        $serviceSpecs = [
            ['code' => 'PF-KYC-01', 'name' => 'PF', 'sub' => 'KYC', 'price' => 200.00],
            ['code' => 'PF-ADV-01', 'name' => 'PF', 'sub' => 'ADVANCE WITHDRAWAL', 'price' => 300.00],
            ['code' => 'FARM-SAT-01', 'name' => 'FARMAR', 'sub' => 'SATBARA', 'price' => 50.00],
            ['code' => 'FARM-ID-01', 'name' => 'FARMAR', 'sub' => 'FARMAR ID Registration', 'price' => 200.00],
            ['code' => 'MAHA-ID-01', 'name' => 'MAHA ID CARD', 'sub' => 'REGISTRATION + SMARTCARD', 'price' => 200.00],
            ['code' => 'AADHAR-UPD-01', 'name' => 'ADHARCARD', 'sub' => 'ADDRESS UPDATE', 'price' => 200.00],
            ['code' => 'GST-REG-01', 'name' => 'GST REGISTRATION', 'sub' => 'NEW', 'price' => 1800.00],
            ['code' => 'RENT-5YR-01', 'name' => 'RENT AGREEMENT', 'sub' => '5 Years Registered 2000+ Gov Fee', 'price' => 2000.00],
        ];

        $serviceMap = [];
        $generalCat = ServiceCategory::firstOrCreate(
            ['slug' => 'general-services', 'tenant_id' => $tenant->id],
            ['name' => 'General Citizen Services', 'icon' => 'fa-solid fa-file-shield']
        );

        foreach ($serviceSpecs as $spec) {
            $srv = Service::firstOrCreate(
                ['service_code' => $spec['code'], 'tenant_id' => $tenant->id],
                [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $generalCat->id,
                    'main_service_name' => $spec['name'],
                    'sub_service_name' => $spec['sub'],
                    'govt_fee' => 0.00,
                    'service_charge' => $spec['price'],
                    'price' => $spec['price'],
                    'expected_processing_days' => 5,
                    'is_active' => true,
                ]
            );
            $serviceMap[$spec['name'] . '::' . $spec['sub']] = $srv;
        }

        // Check if customer directory already has records
        if (Customer::where('branch_id', $shravaniBranch->id)->count() >= 50) {
            return;
        }

        $defaultCat = CustomerCategory::firstOrCreate(
            ['slug' => 'regular-citizen', 'tenant_id' => $tenant->id],
            ['name' => 'Regular Citizen']
        );

        // 4. Primary 16 Follow-up Customers from screenshot
        $primaryCustomers = [
            [
                'code' => 'CUST-2026-00027',
                'name' => 'PRADIP PANDHARINATH PAYGUDE',
                'mobile' => '8329155138',
                'occupation' => 'PRIVATE SERVICE',
                'service' => 'PF',
                'sub' => 'KYC',
                'rate' => 200.00,
                'received' => 0.00,
                'status' => 'INQUIRY',
                'follow_date' => now()->toDateString(),
                'work_details' => 'PF UAN KYC document submission pending',
            ],
            [
                'code' => 'CUST-2026-00035',
                'name' => 'PRASHANT ANKUSH BORKAR',
                'mobile' => '8262071719',
                'occupation' => 'FARMER',
                'service' => 'FARMAR',
                'sub' => 'SATBARA',
                'rate' => 50.00,
                'received' => 0.00,
                'status' => 'INQUIRY',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Digital 7/12 & 8A Ferfar Nakal print inquiry',
            ],
            [
                'code' => 'CUST-2026-00046',
                'name' => 'MACHINDRA SAMBHAJI SAKHARE',
                'mobile' => '9527621851',
                'occupation' => 'FARMER',
                'service' => 'FARMAR',
                'sub' => 'FARMAR ID Registration',
                'rate' => 200.00,
                'received' => 0.00,
                'status' => 'INQUIRY',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Farmer ID registration and land verification',
            ],
            [
                'code' => 'CUST-2026-00071',
                'name' => 'SOMNATH RAVINDRA SARVADE',
                'mobile' => '9307007147',
                'occupation' => 'SALARIED',
                'service' => 'PF',
                'sub' => 'ADVANCE WITHDRAWAL',
                'rate' => 300.00,
                'received' => 0.00,
                'status' => 'INQUIRY',
                'follow_date' => now()->toDateString(),
                'work_details' => 'PF Form 31 Advance withdrawal claim apply',
            ],
            [
                'code' => 'CUST-2026-00038',
                'name' => 'VIJAY BHIMRAO NANAWARE',
                'mobile' => '7757997079',
                'occupation' => 'BUSINESS',
                'service' => 'PF',
                'sub' => 'KYC',
                'rate' => 200.00,
                'received' => 200.00,
                'status' => 'IN_PROGRESS',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Bank KYC seeded successfully, waiting for employer approval',
            ],
            [
                'code' => 'CUST-2026-00041',
                'name' => 'Pratik Raju Warpe',
                'mobile' => '7020715056',
                'occupation' => 'STUDENT',
                'service' => 'PF',
                'sub' => 'KYC',
                'rate' => 200.00,
                'received' => 200.00,
                'status' => 'IN_PROGRESS',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Aadhaar name mismatch rectified in EPFO',
            ],
            [
                'code' => 'CUST-2026-00077',
                'name' => 'SATISH BHAGWAT KSHIRSAGAR',
                'mobile' => '9767162368',
                'occupation' => 'BUSINESS',
                'service' => 'MAHA ID CARD',
                'sub' => 'REGISTRATION + SMARTCARD',
                'rate' => 200.00,
                'received' => 200.00,
                'status' => 'IN_PROGRESS',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Maha ID card registration completed, card printing pending',
            ],
            [
                'code' => 'CUST-2026-00066',
                'name' => 'DNYANESHWAR NARAYAN SHEDGE',
                'mobile' => '8975424571',
                'occupation' => 'FARMER',
                'service' => 'ADHARCARD',
                'sub' => 'ADDRESS UPDATE',
                'rate' => 200.00,
                'received' => 200.00,
                'status' => 'IN_PROGRESS',
                'follow_date' => now()->toDateString(),
                'work_details' => 'UIDAI address update token generated',
            ],
            [
                'code' => 'CUST-2026-00083',
                'name' => 'Indrani Abhay Kale',
                'mobile' => '8484986130',
                'occupation' => 'HOMEMAKER',
                'service' => 'MAHA ID CARD',
                'sub' => 'REGISTRATION + SMARTCARD',
                'rate' => 200.00,
                'received' => 200.00,
                'status' => 'IN_PROGRESS',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Biometric verified, dispatch scheduled',
            ],
            [
                'code' => 'CUST-2026-00016',
                'name' => 'NAGNATH SHANKAR KAMBLE',
                'mobile' => '9657192543',
                'occupation' => 'BUSINESS',
                'service' => 'GST REGISTRATION',
                'sub' => 'NEW',
                'rate' => 1800.00,
                'received' => 1800.00,
                'status' => 'PENDING',
                'follow_date' => now()->toDateString(),
                'work_details' => 'GST portal TRN generated, electricity bill uploaded',
            ],
            [
                'code' => 'CUST-2026-00008',
                'name' => 'RUPESH NARAYAN SUBHEDAR',
                'mobile' => '9881479719',
                'occupation' => 'BUSINESS',
                'service' => 'RENT AGREEMENT',
                'sub' => '5 Years Registered 2000+ Gov Fee',
                'rate' => 2000.00,
                'received' => 0.00,
                'status' => 'IN_PROGRESS',
                'follow_date' => now()->toDateString(),
                'work_details' => '5-year registered rent agreement draft prepared',
            ],
            [
                'code' => 'CUST-2026-00019',
                'name' => 'Kailas Bhimrao Jagtap',
                'mobile' => '9822144551',
                'occupation' => 'FARMER',
                'service' => 'FARMAR',
                'sub' => 'SATBARA',
                'rate' => 50.00,
                'received' => 50.00,
                'status' => 'IN_PROGRESS',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Online ferfar entry verification',
            ],
            [
                'code' => 'CUST-2026-00022',
                'name' => 'Sunil Mahadev Thorat',
                'mobile' => '9822144552',
                'occupation' => 'SALARIED',
                'service' => 'PF',
                'sub' => 'KYC',
                'rate' => 200.00,
                'received' => 0.00,
                'status' => 'INQUIRY',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Joint declaration document submit',
            ],
            [
                'code' => 'CUST-2026-00029',
                'name' => 'Vilas Pandurang More',
                'mobile' => '9822144553',
                'occupation' => 'BUSINESS',
                'service' => 'GST REGISTRATION',
                'sub' => 'NEW',
                'rate' => 1800.00,
                'received' => 900.00,
                'status' => 'PENDING',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Clarification reply submit on GST portal',
            ],
            [
                'code' => 'CUST-2026-00031',
                'name' => 'Anil Tukaram Shinde',
                'mobile' => '9822144554',
                'occupation' => 'FARMER',
                'service' => 'ADHARCARD',
                'sub' => 'ADDRESS UPDATE',
                'rate' => 200.00,
                'received' => 0.00,
                'status' => 'INQUIRY',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Gazetted officer sign on address certificate',
            ],
            [
                'code' => 'CUST-2026-00034',
                'name' => 'Gajanan Dnyandev Pawar',
                'mobile' => '9822144555',
                'occupation' => 'PRIVATE SERVICE',
                'service' => 'PF',
                'sub' => 'ADVANCE WITHDRAWAL',
                'rate' => 300.00,
                'received' => 300.00,
                'status' => 'IN_PROGRESS',
                'follow_date' => now()->toDateString(),
                'work_details' => 'Passbook photo re-upload in member portal',
            ],
        ];

        $appSeq = 10;
        $recSeq = 10;

        foreach ($primaryCustomers as $pc) {
            $cust = Customer::create([
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $shravaniBranch->id,
                'category_id' => $defaultCat->id,
                'customer_code' => $pc['code'],
                'name' => $pc['name'],
                'mobile' => $pc['mobile'],
                'gender' => 'MALE',
                'occupation' => $pc['occupation'],
                'star_rating' => 5,
                'created_by' => $vinodUser->id,
            ]);

            $srvKey = $pc['service'] . '::' . $pc['sub'];
            $srv = $serviceMap[$srvKey] ?? Service::first();

            $rem = max(0, $pc['rate'] - $pc['received']);
            $payStatus = $pc['received'] >= $pc['rate'] ? 'PAID' : ($pc['received'] > 0 ? 'PARTIAL' : 'PENDING');

            $app = Application::create([
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $shravaniBranch->id,
                'customer_id' => $cust->id,
                'service_id' => $srv->id,
                'application_number' => 'SR-' . date('Y') . '-' . str_pad($appSeq++, 5, '0', STR_PAD_LEFT),
                'application_date' => now()->toDateString(),
                'sub_service' => $pc['sub'],
                'work_status' => $pc['status'],
                'payment_status' => $payStatus,
                'base_amount' => $pc['rate'],
                'total_amount' => $pc['rate'],
                'received_amount' => $pc['received'],
                'remaining_amount' => $rem,
                'payment_mode' => 'Cash',
                'follow_up_date' => $pc['follow_date'],
                'work_details' => $pc['work_details'],
                'created_by' => $vinodUser->id,
                'updated_by' => $vinodUser->id,
            ]);

            FollowUp::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $shravaniBranch->id,
                'customer_id' => $cust->id,
                'application_id' => $app->id,
                'follow_up_date' => $pc['follow_date'],
                'reason' => $pc['service'] . ' - ' . $pc['sub'],
                'remarks' => $pc['work_details'],
                'status' => FollowUp::STATUS_PENDING,
                'created_by' => $vinodUser->id,
            ]);

            if ($pc['received'] > 0) {
                Payment::create([
                    'uuid' => (string) Str::uuid(),
                    'tenant_id' => $tenant->id,
                    'branch_id' => $shravaniBranch->id,
                    'customer_id' => $cust->id,
                    'application_id' => $app->id,
                    'receipt_number' => 'REC-' . date('Y') . '-' . str_pad($recSeq++, 5, '0', STR_PAD_LEFT),
                    'payment_date' => now()->toDateString(),
                    'amount' => $pc['received'],
                    'payment_mode' => 'CASH',
                    'payment_status' => 'SUCCESS',
                    'received_by' => $vinodUser->id,
                ]);
            }
        }

        // 5. Seed additional 65 realistic customer profiles for Shravani E-Seva Kendra
        $extraNames = [
            ['Suresh Ramchandra Patil', 'FARMER', '9823112201'],
            ['Ganesh Dattatray Shinde', 'BUSINESS', '9823112202'],
            ['Sandip Babanrao Chavan', 'PRIVATE SERVICE', '9823112203'],
            ['Anil Kashinath Jadhav', 'DAILY WAGES', '9823112204'],
            ['Pravin Shankar More', 'STUDENT', '9823112205'],
            ['Sunita Pandurang Gaikwad', 'HOMEMAKER', '9823112206'],
            ['Ramesh Balasaheb Kadam', 'FARMER', '9823112207'],
            ['Amol Vishwanath Pawar', 'SELF EMPLOYED', '9823112208'],
            ['Santosh Nivrutti Thorat', 'BUSINESS', '9823112209'],
            ['Vikas Tukaram Jagtap', 'SALARIED', '9823112210'],
            ['Sunil Maruti Bhosale', 'FARMER', '9823112211'],
            ['Dipak Madhukar Raut', 'PRIVATE SERVICE', '9823112212'],
            ['Ajay Chandrakant Salunkhe', 'BUSINESS', '9823112213'],
            ['Mahesh Bhagwan Shinde', 'STUDENT', '9823112214'],
            ['Nitin Anandrao Deshmukh', 'SALARIED', '9823112215'],
            ['Sachin Popat Kale', 'FARMER', '9823112216'],
            ['Manoj Laxman Ghadge', 'BUSINESS', '9823112217'],
            ['Rahul Namdeo Mohite', 'PRIVATE SERVICE', '9823112218'],
            ['Ashok Janardan Mane', 'SENIOR CITIZEN', '9823112219'],
            ['Kiran Govind Sawant', 'STUDENT', '9823112220'],
            ['Balasaheb Sopan Nalawade', 'FARMER', '9823112221'],
            ['Shrikant Haribhau Koli', 'DAILY WAGES', '9823112222'],
            ['Pandurang Sakharam Sutar', 'SELF EMPLOYED', '9823112223'],
            ['Vitthal Ganpat Lohar', 'SELF EMPLOYED', '9823112224'],
            ['Dattatray Mahadev Kumbhar', 'BUSINESS', '9823112225'],
            ['Chandrakant Baburao Sonar', 'BUSINESS', '9823112226'],
            ['Bhausaheb Vasantrao Tambe', 'FARMER', '9823112227'],
            ['Kishor Sadashiv Ghule', 'SALARIED', '9823112228'],
            ['Tukaram Eknath Dhoble', 'FARMER', '9823112229'],
            ['Babasaheb Bhaurao Wagh', 'FARMER', '9823112230'],
            ['Kavita Sharad Kadam', 'HOMEMAKER', '9823112231'],
            ['Rekha Sunil Jadhav', 'HOMEMAKER', '9823112232'],
            ['Pooja Sachin Bhosale', 'STUDENT', '9823112233'],
            ['Manisha Pravin Pawar', 'PRIVATE SERVICE', '9823112234'],
            ['Ujwala Ganesh Jagtap', 'HOMEMAKER', '9823112235'],
            ['Surekha Suresh Shinde', 'HOMEMAKER', '9823112236'],
            ['Anita Nitin Chavan', 'HOMEMAKER', '9823112237'],
            ['Shobha Ramesh Gaikwad', 'SENIOR CITIZEN', '9823112238'],
            ['Mangal Santosh More', 'HOMEMAKER', '9823112239'],
            ['Swati Amol Raut', 'SALARIED', '9823112240'],
            ['Rohit Dilip Salve', 'STUDENT', '9823112241'],
            ['Swapnil Sanjay Gite', 'PRIVATE SERVICE', '9823112242'],
            ['Akshay Subhash Tamboli', 'BUSINESS', '9823112243'],
            ['Suraj Prakash Kasbe', 'DAILY WAGES', '9823112244'],
            ['Prashant Vilas Kamthe', 'FARMER', '9823112245'],
            ['Mayur Mohan Kulkarni', 'SALARIED', '9823112246'],
            ['Abhijit Uttam Godse', 'BUSINESS', '9823112247'],
            ['Haridas Narayan Zende', 'SENIOR CITIZEN', '9823112248'],
            ['Anandrao Bhikaji Kadam', 'SENIOR CITIZEN', '9823112249'],
            ['Sambhaji Raghunath Shitole', 'FARMER', '9823112250'],
            ['Shantaram Kisan Walke', 'FARMER', '9823112251'],
            ['Gorakhnath Bapu Bandal', 'FARMER', '9823112252'],
            ['Nathuram Tanaji Malpote', 'FARMER', '9823112253'],
            ['Bhagwan Yashwant Temghare', 'FARMER', '9823112254'],
            ['Dattatraya Shripati Konde', 'BUSINESS', '9823112255'],
            ['Rajendra Pandurang Marne', 'BUSINESS', '9823112256'],
            ['Dnyaneshwar Bhimaji Jedhe', 'FARMER', '9823112257'],
            ['Bhimrao Ganpat Gaikwad', 'SENIOR CITIZEN', '9823112258'],
            ['Shivaji Ramchandra Kadu', 'FARMER', '9823112259'],
            ['Khanderao Balu Hargude', 'DAILY WAGES', '9823112260'],
            ['Avinash Prabhakar Joshi', 'SALARIED', '9823112261'],
            ['Hemant Vasant Deshpande', 'SALARIED', '9823112262'],
            ['Gajanan Anant Kulkarni', 'BUSINESS', '9823112263'],
            ['Shridhar Keshav Bapat', 'SENIOR CITIZEN', '9823112264'],
            ['Yogesh Vilas Phadke', 'PRIVATE SERVICE', '9823112265'],
        ];

        $allServices = Service::where('tenant_id', $tenant->id)->get();
        if ($allServices->isEmpty()) {
            return;
        }

        $custSeq = count($primaryCustomers) + 1;
        $statuses = [
            'READY', 'DELIVERED', 'READY', 'DELIVERED', // completed (~58)
            'IN_PROGRESS', 'IN_PROCESS', 'SUBMITTED', 'NEW', 'DOCUMENT_PENDING', // pending (~84)
        ];

        foreach ($extraNames as $idx => $item) {
            $extraCust = Customer::create([
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $shravaniBranch->id,
                'category_id' => $defaultCat->id,
                'customer_code' => 'CUST-' . date('Y') . '-' . str_pad($custSeq++, 5, '0', STR_PAD_LEFT),
                'name' => $item[0],
                'mobile' => $item[2],
                'gender' => ($idx % 5 === 0) ? 'FEMALE' : 'MALE',
                'occupation' => $item[1],
                'star_rating' => 5,
                'created_by' => $vinodUser->id,
            ]);

            // Create 1-2 applications for each customer
            $appCount = ($idx % 3 === 0) ? 2 : 1;
            for ($k = 0; $k < $appCount; $k++) {
                $chosenService = $allServices[($idx + $k) % $allServices->count()];
                $status = $statuses[($idx * 2 + $k) % count($statuses)];
                $price = (float) ($chosenService->price > 0 ? $chosenService->price : 200.00);

                $isCompleted = in_array($status, ['READY', 'DELIVERED', 'APPROVED']);
                $received = $isCompleted ? $price : (($idx % 2 === 0) ? $price * 0.5 : 0.00);
                $rem = max(0, $price - $received);
                $payStatus = $received >= $price ? 'PAID' : ($received > 0 ? 'PARTIAL' : 'PENDING');

                $extraApp = Application::create([
                    'uuid' => (string) Str::uuid(),
                    'tenant_id' => $tenant->id,
                    'branch_id' => $shravaniBranch->id,
                    'customer_id' => $extraCust->id,
                    'service_id' => $chosenService->id,
                    'application_number' => 'SR-' . date('Y') . '-' . str_pad($appSeq++, 5, '0', STR_PAD_LEFT),
                    'application_date' => now()->subDays($idx % 15)->toDateString(),
                    'sub_service' => $chosenService->sub_service_name,
                    'work_status' => $status,
                    'payment_status' => $payStatus,
                    'base_amount' => $price,
                    'total_amount' => $price,
                    'received_amount' => $received,
                    'remaining_amount' => $rem,
                    'payment_mode' => ($idx % 2 === 0) ? 'Cash' : 'UPI',
                    'work_details' => 'Application processing counter submission',
                    'created_by' => $vinodUser->id,
                    'updated_by' => $vinodUser->id,
                ]);

                if ($received > 0) {
                    Payment::create([
                        'uuid' => (string) Str::uuid(),
                        'tenant_id' => $tenant->id,
                        'branch_id' => $shravaniBranch->id,
                        'customer_id' => $extraCust->id,
                        'application_id' => $extraApp->id,
                        'receipt_number' => 'REC-' . date('Y') . '-' . str_pad($recSeq++, 5, '0', STR_PAD_LEFT),
                        'payment_date' => now()->subDays($idx % 10)->toDateString(),
                        'amount' => $received,
                        'payment_mode' => ($idx % 2 === 0) ? 'CASH' : 'UPI',
                        'payment_status' => 'SUCCESS',
                        'received_by' => $vinodUser->id,
                    ]);
                }
            }
        }
    }
}
