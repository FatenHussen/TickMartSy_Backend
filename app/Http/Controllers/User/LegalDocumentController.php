<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LegalDocument;

class LegalDocumentController extends Controller
{
    public function show(Request $request, $key)
    {
        $document = LegalDocument::where('key', $key)->first();

        if (!$document) {
            return $this->sendError(message: __('custom.documents.not_found'), code: 404);
        }
        return $this->sendResponse(data: [
            'key' => $document->key,
            'title' => $document->title,
            'content' => $document->content,
        ]);
    }
}
