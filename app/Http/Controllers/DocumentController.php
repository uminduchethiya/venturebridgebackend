<?php

namespace App\Http\Controllers;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function getAllDocuments()
    {
        $documents = Document::with('user:id,email,type')->get();

        return response()->json([
            'status' => true,
            'message' => 'Documents fetched successfully.',
            'documents' => $documents
        ], 200);
    }

}
