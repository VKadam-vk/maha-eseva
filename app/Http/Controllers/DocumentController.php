<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Services\AuditService;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documentService
    ) {}

    /**
     * Upload document.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'application_id' => ['nullable', 'exists:applications,id'],
            'document_type_name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:15360'], // 15MB
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        try {
            $this->documentService->storeDocument(
                $request->file('file'),
                $customer,
                $request->document_type_name,
                $request->application_id,
                Auth::user()
            );

            return back()->with('success', 'Document uploaded securely to private vault.');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }
    }

    /**
     * Download document securely with authorization check and audit log.
     */
    public function download(Document $document): BinaryFileResponse|StreamedResponse
    {
        $this->authorize('view', $document);

        AuditService::log('DOCUMENT_DOWNLOADED', $document);

        $path = Storage::disk($document->disk)->path($document->storage_path);
        if (!file_exists($path)) {
            abort(404, 'File not found in storage.');
        }

        return response()->download($path, $document->original_filename, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Preview document inline in browser securely.
     */
    public function preview(Document $document): BinaryFileResponse
    {
        $this->authorize('view', $document);

        AuditService::log('DOCUMENT_VIEWED', $document);

        $path = Storage::disk($document->disk)->path($document->storage_path);
        if (!file_exists($path)) {
            abort(404, 'File not found in storage.');
        }

        return response()->file($path, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Verify or reject document.
     */
    public function updateStatus(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('verify', $document);

        $validated = $request->validate([
            'status' => ['required', 'in:VERIFIED,REJECTED,REQUIRED,RECEIVED,NOT_APPLICABLE'],
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $document->update([
            'status' => $validated['status'],
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        AuditService::log('DOCUMENT_STATUS_UPDATED', $document, null, $validated);

        return back()->with('success', "Document status updated to {$validated['status']}.");
    }

    /**
     * Delete document.
     */
    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $this->documentService->deleteDocument($document, Auth::user());

        return back()->with('success', 'Document deleted successfully.');
    }
}
