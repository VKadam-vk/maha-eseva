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
        if (User::where('email', 'admin@mahaeseva.com')->exists()) {
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
            'employee_code' => 'EMP-0002',
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
                    ['name' => 'Establishment Name (Marathi - देवनागरी)', 'key' => 'shop_name_mr', 'type' => 'text', 'label' => 'दुकानाचे नाव (मराठीत)', 'required' => true],
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

        // 9. Seed Demo Customers
        $customersData = [
            [
                'name' => 'Sanjay Ramchandra Patil',
                'mobile' => '9822012345',
                'gender' => 'MALE',
                'email' => 'sanjay.patil@example.com',
                'address' => 'Flat 302, Sai Angan, Kothrud',
                'city' => 'Pune',
                'pincode' => '411038',
                'category_id' => $catRegular->id,
            ],
            [
                'name' => 'Anuradha Vijay Deshpande',
                'mobile' => '9822054321',
                'gender' => 'FEMALE',
                'email' => 'anuradha.d@example.com',
                'address' => 'Bungalow 4, Prabhat Road, Lane 6',
                'city' => 'Pune',
                'pincode' => '411004',
                'category_id' => $catSenior->id,
            ],
            [
                'name' => 'Vikram Suresh Jagtap',
                'mobile' => '9822099887',
                'gender' => 'MALE',
                'email' => 'vikram.j@example.com',
                'address' => 'House No. 45, Ghorpadi Peth',
                'city' => 'Pune',
                'pincode' => '411042',
                'category_id' => $catStudent->id,
            ],
            [
                'name' => 'Mangesh Kashinath Shinde',
                'mobile' => '9822077665',
                'gender' => 'MALE',
                'email' => 'mangesh.shinde@example.com',
                'address' => 'Plot 88, Near Maruti Mandir, Hadapsar',
                'city' => 'Pune',
                'pincode' => '411028',
                'category_id' => $catRegular->id,
            ],
        ];

        $createdCustomers = [];
        foreach ($customersData as $idx => $cData) {
            $code = sprintf('CUST-%s-%05d', date('Y'), $idx + 1);
            $cData['tenant_id'] = $tenant->id;
            $cData['branch_id'] = $mainBranch->id;
            $cData['customer_code'] = $code;
            $cData['created_by'] = $businessOwnerUser->id;
            $cData['updated_by'] = $businessOwnerUser->id;

            $createdCustomers[] = Customer::create($cData);
        }

        // 10. Seed Demo Applications & Status Histories
        $demoApps = [
            [
                'customer' => $createdCustomers[0],
                'service' => $createdServices['PAN-NEW-01'],
                'work_status' => Application::STATUS_IN_PROCESS,
                'payment_status' => Application::PAYMENT_PAID,
                'employee' => $employee1,
                'ext_ack' => 'N-129481928471',
                'received' => 200.00,
            ],
            [
                'customer' => $createdCustomers[1],
                'service' => $createdServices['INC-TAH-01'],
                'work_status' => Application::STATUS_SUBMITTED,
                'payment_status' => Application::PAYMENT_PAID,
                'employee' => $employee1,
                'ext_ack' => 'REV2026/INC/98214',
                'received' => 150.00,
            ],
            [
                'customer' => $createdCustomers[2],
                'service' => $createdServices['RENT-REG-01'],
                'work_status' => Application::STATUS_DOCUMENT_PENDING,
                'payment_status' => Application::PAYMENT_PARTIAL,
                'employee' => $employee2,
                'ext_ack' => null,
                'received' => 500.00,
            ],
            [
                'customer' => $createdCustomers[3],
                'service' => $createdServices['SHOP-ACT-01'],
                'work_status' => Application::STATUS_APPROVED,
                'payment_status' => Application::PAYMENT_PAID,
                'employee' => $employee2,
                'ext_ack' => 'MH/PUNE/SHOP/2026/481',
                'received' => 350.00,
            ],
        ];

        foreach ($demoApps as $idx => $dApp) {
            $sr = sprintf('SR-%s-%05d', date('Y'), $idx + 1);
            $service = $dApp['service'];
            $total = (float) $service->price;
            $received = (float) $dApp['received'];
            $remaining = max(0, $total - $received);

            $app = Application::create([
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $dApp['customer']->branch_id,
                'customer_id' => $dApp['customer']->id,
                'service_id' => $service->id,
                'application_number' => $sr,
                'application_date' => now()->subDays(rand(1, 10))->toDateString(),
                'assigned_employee_id' => $dApp['employee']->id,
                'work_status' => $dApp['work_status'],
                'payment_status' => $dApp['payment_status'],
                'base_amount' => $service->price,
                'govt_fee' => $service->govt_fee,
                'service_charge' => $service->service_charge,
                'total_amount' => $total,
                'received_amount' => $received,
                'remaining_amount' => $remaining,
                'external_acknowledgement_no' => $dApp['ext_ack'],
                'due_date' => now()->addDays(5)->toDateString(),
                'expected_completion_date' => now()->addDays(7)->toDateString(),
                'delivery_status' => ($dApp['work_status'] === Application::STATUS_APPROVED) ? 'READY_FOR_PICKUP' : 'PENDING',
                'created_by' => $businessOwnerUser->id,
            ]);

            // Status history
            ApplicationStatusHistory::create([
                'tenant_id' => $tenant->id,
                'application_id' => $app->id,
                'old_status' => null,
                'new_status' => Application::STATUS_NEW,
                'changed_by' => $businessOwnerUser->id,
                'remarks' => 'Application received and registered at counter',
                'created_at' => $app->created_at,
            ]);

            if ($app->work_status !== Application::STATUS_NEW) {
                ApplicationStatusHistory::create([
                    'tenant_id' => $tenant->id,
                    'application_id' => $app->id,
                    'old_status' => Application::STATUS_NEW,
                    'new_status' => $app->work_status,
                    'changed_by' => $dApp['employee']->user_id,
                    'remarks' => 'Processed on government portal with acknowledgement',
                    'created_at' => now()->subDays(1),
                ]);
            }

            // Payment
            if ($received > 0) {
                $recNo = sprintf('REC-%s-%05d', date('Y'), $idx + 1);
                Payment::create([
                    'uuid' => (string) Str::uuid(),
                    'tenant_id' => $tenant->id,
                    'branch_id' => $app->branch_id,
                    'customer_id' => $app->customer_id,
                    'application_id' => $app->id,
                    'receipt_number' => $recNo,
                    'payment_date' => $app->application_date,
                    'amount' => $received,
                    'payment_mode' => 'UPI',
                    'transaction_reference' => 'UPI-' . rand(10000000, 99999999),
                    'payment_status' => 'SUCCESS',
                    'received_by' => $businessOwnerUser->id,
                ]);
            }

            // Follow-up
            if ($app->work_status === Application::STATUS_DOCUMENT_PENDING) {
                FollowUp::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $app->branch_id,
                    'customer_id' => $app->customer_id,
                    'application_id' => $app->id,
                    'assigned_employee_id' => $dApp['employee']->id,
                    'follow_up_date' => now()->addDays(1)->toDateString(),
                    'follow_up_time' => '11:00:00',
                    'reason' => 'Pending Owner Electricity Bill & Witness Aadhaar',
                    'remarks' => 'Call tenant to collect flat light bill copy before registration date',
                    'status' => FollowUp::STATUS_PENDING,
                    'created_by' => $businessOwnerUser->id,
                ]);
            }
        }
    }
}
