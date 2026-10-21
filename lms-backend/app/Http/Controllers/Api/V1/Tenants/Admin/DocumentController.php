<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function index()
    {
        $document = Document::first();

        return $this->sendResponse($document, 'Return current document', 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'show' => 'required',
        ]);

        $document = Document::create([
            'content' => $request->content,
            'show' => $request->show
        ]);

        return $this->sendResponse($document, 'You have successfully added a new docuemnt.', 201);

    }

    public function update(Request $request, $id)
    {
        $document = Document::find($id);

        if (!$document) {
            return $this->sendResponse([], 'No document.', 404);
        }

        $is_changed = $document->content !== $request->content;

        $document->update([
            'content' => $request->content,
            'show' => $request->show
        ]);

        if($is_changed) {
            User::where('has_accepted_terms', 1)->update(['has_accepted_terms' => 0]);
        }

        return $this->sendResponse($document, 'You have successfully updated a new docuemnt.', 201);

    }
}
