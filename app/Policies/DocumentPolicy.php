<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('documents.view');
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $document->tenant_id) {
            return false;
        }

        if ($user->isBusinessOwner()) {
            return $user->hasPermission('documents.view');
        }

        if ($user->branch_id && $user->branch_id !== $document->branch_id) {
            return false;
        }

        return $user->hasPermission('documents.view');
    }

    public function upload(User $user): bool
    {
        return $user->hasPermission('documents.upload');
    }

    public function verify(User $user, Document $document): bool
    {
        if ($user->tenant_id !== $document->tenant_id) {
            return false;
        }

        return $user->hasPermission('documents.verify');
    }

    public function delete(User $user, Document $document): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $document->tenant_id) {
            return false;
        }

        return $user->hasPermission('documents.delete');
    }
}
