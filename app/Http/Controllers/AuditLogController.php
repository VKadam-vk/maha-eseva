<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Audit log trail viewer.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isBusinessOwner()) {
            abort(403, 'Unauthorized: Only administrators can view audit logs.');
        }

        $query = AuditLog::with(['user', 'branch']);

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('ip_address', 'like', "%{$s}%")
                  ->orWhere('entity_type', 'like', "%{$s}%")
                  ->orWhere('event', 'like', "%{$s}%");
            });
        }

        $logs = $query->latest('id')->paginate(25)->withQueryString();

        $events = [
            'LOGIN', 'LOGOUT', 'FAILED_LOGIN', 'LOCKED_LOGIN_ATTEMPT', 'PASSWORD_CHANGED',
            'CUSTOMER_CREATED', 'CUSTOMER_UPDATED', 'CUSTOMER_IMPORT',
            'APPLICATION_CREATED', 'APPLICATION_STATUS_CHANGED',
            'DOCUMENT_UPLOADED', 'DOCUMENT_DOWNLOADED', 'DOCUMENT_VIEWED', 'DOCUMENT_STATUS_UPDATED', 'DOCUMENT_DELETED',
            'PAYMENT_CREATED', 'PAYMENT_REFUNDED', 'INVOICE_CREATED',
            'EMPLOYEE_CREATED', 'BRANCH_CREATED', 'BRANCH_UPDATED',
            'SERVICE_CREATED', 'FOLLOWUP_CREATED', 'LEAD_CREATED', 'LEAD_CONVERTED',
            'EXPORT_CUSTOMERS', 'EXPORT_APPLICATIONS'
        ];

        return view('audit.index', compact('logs', 'events'));
    }
}
