<?php

namespace App\Http\Controllers;

use App\Models\CashAdjustment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashAdjustmentController extends Controller
{
    /**
     * Store a quick receive or spend cash adjustment.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:RECEIVE,SPEND'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $branchId = $user->branch_id ?? 1;
        $tenantId = $user->tenant_id ?? 1;

        $adjustment = CashAdjustment::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'] ? trim($validated['reason']) : null,
            'entry_date' => now()->toDateString(),
        ]);

        $formattedAmount = '₹' . number_format($adjustment->amount, 2);
        $typeLabel = $adjustment->type === 'RECEIVE' ? 'Receive (जमा)' : 'Spend (खर्च)';
        $msg = "{$typeLabel}: {$formattedAmount} यशस्वीरीत्या नोंदवले.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'adjustment' => $adjustment,
            ]);
        }

        return back()->with('success', $msg);
    }
}

