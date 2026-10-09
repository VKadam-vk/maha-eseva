# MAHA E-SEVA ERP MANAGEMENT SYSTEM
### Production-Ready Enterprise Multi-Tenant SaaS ERP + CRM Architecture

---

## 📌 1. Project Overview

**Maha E-Seva ERP Management System** is a full-featured, secure, multi-tenant digital service center management platform built on **PHP 8.2+**, **Laravel 12**, **MySQL 8+ / SQLite**, **Blade**, and **Modern JavaScript**.

Designed specifically for **Grahak Seva Kendras**, **Setu Suvidha Kendras**, **Aaple Sarkar Seva Kendras**, and digital service enterprises across Maharashtra and India, it consolidates:
- **Fast Counter Daily Entry**: High-speed, Excel-like inline entry grid with keyboard shortcuts for rapid citizen on-boarding.
- **Customer CRM**: Single citizen profile supporting unlimited services, dynamic custom fields, document archives, and payment histories.
- **Service Request Engine**: Configurable workflow state machine (14 discrete statuses) with append-only audit tracking.
- **Private Document Vault**: Zero-exposure private storage (`storage/app/private`), SHA-256 cryptographic verification, MIME checking, and streaming authorization.
- **Billing & Payment Gateway**: Automated invoice generation, partial payment tracking, thermal receipt printing (58mm/80mm), and refund auditing.
- **Citizen Tracking Portal**: Public OTP-authenticated tracking portal (`/track`) with zero IDOR exposure and rate-limited SMS/WhatsApp notification simulation.
- **Security & Multi-Tenancy**: Database-level global scopes (`TenantScope`, `BranchScope`), strict Role-Based Access Control (RBAC), security headers (CSP, HSTS, X-Frame-Options), and comprehensive append-only audit logging.

---

## 🏛️ 2. Architectural Hierarchy

```
PLATFORM (Platform Super Admin)
   └── BUSINESS OWNER / TENANT (Maha E-Seva Kendra Owner)
         ├── BRANCHES (Head Office, शिवाजी नगर, हडपसर...)
         │     ├── BRANCH ADMIN (Center Manager)
         │     └── EMPLOYEES (Operators / Executives)
         └── CUSTOMERS (Citizens / Applicants)
               ├── Service Applications (PAN, Income, Domicile...)
               ├── Document Vault (Encrypted / Private storage)
               ├── Invoices & Receipts
               └── Follow-ups & Reminders
```

---

## 🔐 3. User Roles & Permission Matrix

| Role Slug | Scope | Key Permissions & Responsibilities |
| :--- | :--- | :--- |
| `PLATFORM_SUPER_ADMIN` | Global Platform | Tenant provisioning, global system settings, billing subscriptions, platform audit logs. |
| `BUSINESS_OWNER` | Tenant Wide | Full control over all branches, staff, services, pricing, financial reports, imports/exports, and custom fields. |
| `BRANCH_ADMIN` | Single Branch | Managing counter operators, local applications, customer CRM, collections, and follow-ups. Restricted from creating branches or modifying subscriptions. |
| `EMPLOYEE` | Assigned Branch | Processing service requests, uploading documents, recording payments, and resolving daily tasks. |
| `CUSTOMER` | Public Citizen | Accesses only verified applications via `/track` and 6-digit cryptographic mobile OTP. No internal ERP access. |

---

## 📦 4. Initial Seeded E-Seva Services

The system comes pre-configured with **14 standardized digital citizen services**:
1. **PAN CARD** (New Form 49A, Physical + e-PAN Card)
2. **RENT AGREEMENT** (Online Registered Rent Agreement with Biometric Verification)
3. **INCOME CERTIFICATE** (Tahasildar 1-Year / 3-Year Income Certificate)
4. **DOMICILE & AGE-NATIONALITY CERTIFICATE** (Maharashtra Resident Proof)
5. **POLICE VERIFICATION (PCC)** (Job / Passport Character Verification)
6. **VOTER ID CARD** (Form 6 New Voter Registration)
7. **GOVERNMENT GAZETTE** (Name Change / Religion / Date of Birth Gazette)
8. **CASTE VALIDITY CERTIFICATE** (Scrutiny Committee Application)
9. **PASSPORT SEVA** (Fresh 36-Page Normal Passport Application)
10. **RATION CARD SERVICES** (Addition of Family Member / New Ration Card)
11. **FSSAI FOOD LICENCE** (Registration for Food Vendors / Small Businesses)
12. **GST REGISTRATION** (Proprietorship / MSME GSTIN)
13. **SHOP ACT (GUMASTA)** (Maharashtra Intimation / Registration Form)
14. **NON-CREAMY LAYER CERTIFICATE** (OBC / VJNT Certificate)

---

## 🛡️ 5. Critical Security Implementation

- **Tenant Isolation**: Handled automatically at Eloquent layer via [TenantScope.php](file:///c:/ESeva/app/Models/Scopes/TenantScope.php) and verified by `EnsureTenantAccess` middleware.
- **Branch Isolation**: Handled automatically via [BranchScope.php](file:///c:/ESeva/app/Models/Scopes/BranchScope.php) and verified by `EnforceBranchAccess` middleware.
- **Cryptographic Document Vault**: Files are stored with randomized UUID filenames in `storage/app/private/documents/` and never directly accessible via HTTP web root. Served via signed, streaming controllers with SHA-256 checksums.
- **Credential Encryption**: External portal credentials (`portal_password_encrypted`, `portal_username_encrypted`) use AES-256-GCM application encryption (`encrypted` Eloquent cast).
- **Public Tracking OTP Security**: OTPs are 6-digit random codes hashed with SHA-256, 10-minute validity, 60-second resend cooldown, and account attempt limits (5 max attempts).
- **Audit Logging**: Append-only [AuditLog](file:///c:/ESeva/app/Models/AuditLog.php) table captures user ID, IP address, user agent, entity type, entity ID, old attributes, and new attributes.
- **HTTP Security Headers**: `Content-Security-Policy`, `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, and `Permissions-Policy`.

---

## 🚀 6. Installation & Quick Setup

### Prerequisites
- PHP 8.2 or PHP 8.3
- Composer 2.x
- SQLite 3 (or MySQL 8.0+)
- OpenSSL, PDO, Mbstring, Fileinfo PHP Extensions

### Step-by-Step Setup

1. **Clone or navigate to workspace**:
   ```bash
   cd c:\ESeva
   ```

2. **Environment Configuration**:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. **Run Migrations & Seed Database**:
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Link Storage (Optional for public assets)**:
   ```bash
   php artisan storage:link
   ```

5. **Start Development Server**:
   ```bash
   php artisan serve
   ```
   Open `http://127.0.0.1:8000` in your browser.

---

## 🔑 7. Default Seeded Credentials

| Role | Email | Password | Access URL |
| :--- | :--- | :--- | :--- |
| **Business Owner** | `admin@mahaeseva.com` | `Password@123` | `http://127.0.0.1:8000/login` |
| **Branch Admin** | `branchadmin@mahaeseva.com` | `Password@123` | `http://127.0.0.1:8000/login` |
| **Operator Employee** | `employee@mahaeseva.com` | `Password@123` | `http://127.0.0.1:8000/login` |
| **Platform Super Admin** | `superadmin@mahaeseva.gov.in` | `Password@123` | `http://127.0.0.1:8000/login` |
| **Citizen Portal** | *Public Access* | *6-Digit OTP* | `http://127.0.0.1:8000/track` |

*Quick Citizen Tracking Demo Credentials:*
- **Application / SR Number**: `SR-2026-00001`
- **Registered Mobile**: `9822012345`

---

## 🧪 8. Automated Test Execution

Run the complete test suite:
```bash
php artisan test
```

### Test Suite Summary:
- `AuthenticationTest`: Login, rate-limiting, account lockout after 5 failures, session regeneration, and logout invalidation.
- `TenantIsolationTest`: Cross-tenant access attempts between Kendra A and Kendra B fail with 403/404.
- `BranchIsolationTest`: Cross-branch access between Branch 1 and Branch 2 fail with 403/404; branch admin forbidden from creating branches.
- `CustomerTest`: Quick customer creation, duplicate mobile lookup API, idempotency on duplicate submission, profile rendering, and update.
- `ApplicationTest`: Dynamic custom field values, SR number generation, financial balance formulas, status transitions, and history audits.
- `DocumentTest`: Private vault storage, SHA-256 hash generation, authorized download streaming, and cross-tenant download blocking.
- `CustomerPortalTest`: Public tracking, OTP generation, OTP verification, IDOR prevention, and tracking token invalidation.
- `PaymentAndInvoiceTest`: DB transaction for payments, automatic balance recalculation, thermal receipt generation, and payment refunds.

---

## 🔄 9. Production Deployment & Operational Recommendations

1. **Production Environment**:
   - Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
   - Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache`.
2. **Queue Worker Configuration**:
   - Run `php artisan queue:work --tries=3 --timeout=90` managed via Supervisor or Windows Service for background SMS/WhatsApp notifications and report generation.
3. **Database & Storage Backups**:
   - Schedule daily automated SQL database backups (`mysqldump`) and archive private document storage directory (`storage/app/private`).
