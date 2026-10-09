<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\ImportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ImportExportController extends Controller
{
    public function __construct(
        protected ImportExportService $importExportService
    ) {}

    /**
     * Show CSV customer import page.
     */
    public function showImport(): View
    {
        $branches = Auth::user()->tenant?->branches()->where('status', 'ACTIVE')->get() ?? collect();
        return view('import_export.import', compact('branches'));
    }

    /**
     * Process customer CSV import.
     */
    public function processImport(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // 5MB
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $user = Auth::user();

        try {
            $result = $this->importExportService->importCustomers(
                $request->file('file'),
                $user,
                (int) $request->branch_id
            );

            return back()->with('import_result', $result)
                ->with('success', "Import complete! Successfully imported {$result['imported']} customers, skipped {$result['skipped']}.");
        } catch (\Exception $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }
    }
}
