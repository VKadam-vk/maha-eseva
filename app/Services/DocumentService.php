<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    protected array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
    ];

    protected array $disallowedExtensions = [
        'php', 'phtml', 'phar', 'exe', 'bat', 'sh', 'js', 'py', 'pl', 'cgi', 'htaccess', 'svg'
    ];

    /**
     * Upload and store a private document safely.
     */
    public function storeDocument(
        UploadedFile $file,
        Customer $customer,
        string $documentTypeName,
        ?int $applicationId = null,
        ?User $uploader = null,
        string $status = Document::STATUS_RECEIVED
    ): Document {
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, $this->disallowedExtensions)) {
            throw new \InvalidArgumentException('Uploaded file extension is not allowed for security reasons.');
        }

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $this->allowedMimes)) {
            throw new \InvalidArgumentException('Unsupported file format. Please upload JPG, PNG, WEBP, PDF, DOCX or ZIP.');
        }

        $fileSize = $file->getSize();
        if ($fileSize > 15 * 1024 * 1024) { // 15MB limit
            throw new \InvalidArgumentException('File exceeds the maximum limit of 15MB.');
        }

        $fileHash = hash_file('sha256', $file->getRealPath());
        $tenantId = $customer->tenant_id;
        $branchId = $customer->branch_id;
        
        $randomName = Str::random(40) . '.' . $extension;
        $relativeDir = 'private/documents/' . $tenantId . '/' . date('Y/m');
        $storedPath = $file->storeAs($relativeDir, $randomName, 'local');

        $document = Document::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'application_id' => $applicationId,
            'document_type_name' => $documentTypeName,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $storedPath,
            'disk' => 'local',
            'mime_type' => $mimeType,
            'file_size_bytes' => $fileSize,
            'file_hash' => $fileHash,
            'status' => $status,
            'uploaded_by' => $uploader?->id,
        ]);

        AuditService::log('DOCUMENT_UPLOADED', $document, null, [
            'document_id' => $document->id,
            'type' => $documentTypeName,
            'original_filename' => $file->getClientOriginalName(),
        ]);

        return $document;
    }

    /**
     * Delete document and physical file.
     */
    public function deleteDocument(Document $document, User $user): bool
    {
        AuditService::log('DOCUMENT_DELETED', $document, $document->toArray(), null);

        if (Storage::disk($document->disk)->exists($document->storage_path)) {
            Storage::disk($document->disk)->delete($document->storage_path);
        }

        return $document->delete();
    }
}
