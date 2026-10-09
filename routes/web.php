<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FastEntryController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

// Health Check Routes (for cloud platforms like Railway & Render)
Route::get('/up', function () {
    return response('OK', 200);
});
Route::get('/health', function () {
    return response('OK', 200);
});

// Redirect root to dashboard if logged in, or to login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Customer Tracking Portal (OTP Verified)
Route::get('/track', [CustomerPortalController::class, 'index'])->name('portal.track');
Route::post('/api/portal/request-otp', [CustomerPortalController::class, 'requestOtp'])->name('portal.request_otp');
Route::post('/api/portal/verify-otp', [CustomerPortalController::class, 'verifyOtp'])->name('portal.verify_otp');
Route::get('/portal/status/{token}', [CustomerPortalController::class, 'status'])->name('portal.status');

// Public Lead Capture API
Route::post('/api/leads/capture', [LeadController::class, 'store'])->name('api.leads.capture');

// Authenticated & Tenant Scoped ERP Management System Routes
Route::middleware(['auth', 'tenant'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & Security
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Customer CRM
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index')->middleware('permission:customers.view');
        Route::get('/create', [CustomerController::class, 'create'])->name('create')->middleware('permission:customers.create');
        Route::post('/', [CustomerController::class, 'store'])->name('store')->middleware('permission:customers.create');
        Route::get('/check-duplicate', [CustomerController::class, 'checkDuplicate'])->name('check_duplicate');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show')->middleware('permission:customers.view');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit')->middleware('permission:customers.edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update')->middleware('permission:customers.edit');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy')->middleware('permission:customers.delete');
    });

    // Services Master & Dynamic Custom Fields
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index')->middleware('permission:services.view');
        Route::get('/create', [ServiceController::class, 'create'])->name('create')->middleware('permission:services.create');
        Route::post('/', [ServiceController::class, 'store'])->name('store')->middleware('permission:services.create');
        Route::get('/{service}', [ServiceController::class, 'show'])->name('show')->middleware('permission:services.view');
        Route::post('/{service}/custom-fields', [ServiceController::class, 'storeCustomField'])->name('custom_fields.store')->middleware('permission:services.edit');
        Route::delete('/custom-fields/{field}', [ServiceController::class, 'deleteCustomField'])->name('custom_fields.destroy')->middleware('permission:services.edit');
        Route::post('/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('toggle_status')->middleware('permission:services.edit');
        Route::get('/{service}/schema', [ServiceController::class, 'schema'])->name('schema');
    });

    // Applications & State Workflow
    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [ApplicationController::class, 'index'])->name('index')->middleware('permission:applications.view');
        Route::get('/create', [ApplicationController::class, 'create'])->name('create')->middleware('permission:applications.create');
        Route::post('/', [ApplicationController::class, 'store'])->name('store')->middleware('permission:applications.create');
        Route::get('/{application}', [ApplicationController::class, 'show'])->name('show')->middleware('permission:applications.view');
        Route::post('/{application}/status', [ApplicationController::class, 'updateStatus'])->name('status.update')->middleware('permission:applications.edit');
        Route::put('/{application}/details', [ApplicationController::class, 'updateDetails'])->name('details.update')->middleware('permission:applications.edit');
    });

    // Private Document Vault
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::post('/upload', [DocumentController::class, 'store'])->name('upload')->middleware('permission:documents.upload');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download')->middleware('permission:documents.view');
        Route::get('/{document}/preview', [DocumentController::class, 'preview'])->name('preview')->middleware('permission:documents.view');
        Route::post('/{document}/status', [DocumentController::class, 'updateStatus'])->name('status')->middleware('permission:documents.verify');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy')->middleware('permission:documents.delete');
    });

    // Billing, Payments & Invoices
    Route::prefix('billing')->group(function () {
        Route::get('/payments', [BillingController::class, 'payments'])->name('payments.index')->middleware('permission:payments.view');
        Route::post('/payments/record', [BillingController::class, 'recordPayment'])->name('payments.record')->middleware('permission:payments.create');
        Route::get('/payments/{payment}/receipt', [BillingController::class, 'printReceipt'])->name('payments.receipt')->middleware('permission:payments.view');
        Route::post('/payments/{payment}/refund', [BillingController::class, 'refund'])->name('payments.refund')->middleware('permission:payments.refund');

        Route::get('/invoices', [BillingController::class, 'invoices'])->name('invoices.index')->middleware('permission:payments.view');
        Route::get('/invoices/create', [BillingController::class, 'createInvoice'])->name('invoices.create')->middleware('permission:payments.create');
        Route::post('/invoices', [BillingController::class, 'storeInvoice'])->name('invoices.store')->middleware('permission:payments.create');
        Route::get('/invoices/{invoice}', [BillingController::class, 'showInvoice'])->name('invoices.show')->middleware('permission:payments.view');
        Route::get('/invoices/{invoice}/print', [BillingController::class, 'printInvoice'])->name('invoices.print')->middleware('permission:payments.view');
    });

    // Fast Spreadsheet-Style Daily Entry
    Route::prefix('fast-entry')->name('fast_entry.')->group(function () {
        Route::get('/', [FastEntryController::class, 'index'])->name('index')->middleware('permission:applications.create');
        Route::post('/store-row', [FastEntryController::class, 'storeRow'])->name('store_row')->middleware('permission:applications.create');
    });

    // Staff & Employee Management
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index')->middleware('permission:employees.view');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create')->middleware('permission:employees.create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store')->middleware('permission:employees.create');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show')->middleware('permission:employees.view');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit')->middleware('permission:employees.edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update')->middleware('permission:employees.edit');
        Route::post('/{employee}/tasks', [EmployeeController::class, 'storeTask'])->name('tasks.store')->middleware('permission:employees.edit');
        Route::post('/tasks/{task}/status', [EmployeeController::class, 'updateTaskStatus'])->name('tasks.status');
    });

    // Branch Management
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index')->middleware('permission:branches.view');
        Route::get('/create', [BranchController::class, 'create'])->name('create')->middleware('permission:branches.create');
        Route::post('/', [BranchController::class, 'store'])->name('store')->middleware('permission:branches.create');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit')->middleware('permission:branches.edit');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('update')->middleware('permission:branches.edit');
    });

    // Follow-ups & Reminders
    Route::prefix('followups')->name('followups.')->group(function () {
        Route::get('/', [FollowUpController::class, 'index'])->name('index');
        Route::post('/', [FollowUpController::class, 'store'])->name('store');
        Route::post('/{followUp}/status', [FollowUpController::class, 'updateStatus'])->name('status');
    });

    // Leads & Website Enquiries CRM
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::post('/', [LeadController::class, 'store'])->name('store');
        Route::post('/{lead}/status', [LeadController::class, 'updateStatus'])->name('status');
        Route::post('/{lead}/convert', [LeadController::class, 'convert'])->name('convert');
    });

    // Comprehensive Reports Suite
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index')->middleware('permission:reports.view');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers')->middleware('permission:reports.view');
        Route::get('/applications', [ReportController::class, 'applications'])->name('applications')->middleware('permission:reports.view');
        Route::get('/collections', [ReportController::class, 'collections'])->name('collections')->middleware('permission:reports.view');
        Route::get('/employees', [ReportController::class, 'employees'])->name('employees')->middleware('permission:reports.view');
    });

    // Excel / CSV Bulk Import & Export
    Route::prefix('import-export')->name('import_export.')->group(function () {
        Route::get('/import/customers', [ImportExportController::class, 'showImport'])->name('import.customers');
        Route::post('/import/customers', [ImportExportController::class, 'processImport'])->name('import.customers.process');
    });

    // Append-Only Audit Logging Trail
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index')->middleware('permission:settings.manage');

    // Organization & Branch Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index')->middleware('permission:settings.manage');
        Route::put('/update', [SettingController::class, 'update'])->name('update')->middleware('permission:settings.manage');
    });
});
