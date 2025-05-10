<?php

namespace App\Http\Controllers;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\InvestorStartupDocument;
use App\Models\StartupToMatchInvestorDocument;

class DocumentController extends Controller
{


    public function store(Request $request)
    {
        try {
            // Log incoming request data for debugging
            Log::info('Incoming request data:', $request->all());

            // Validate the incoming request
            $request->validate([
                'match_id' => 'required|exists:startup_to_match_investor,id',
                'pitch_deck' => 'nullable|file|mimes:pdf,ppt,pptx',
                'other_document' => 'nullable|file|mimes:pdf,docx,doc',
                'signature' => 'nullable|string',
            ]);

            $data = ['match_id' => $request->match_id];

            // Log the data that will be saved to the database
            Log::info('Data to be saved:', $data);

            // Handle file uploads
            if ($request->hasFile('pitch_deck')) {
                $data['pitch_deck'] = $request->file('pitch_deck')->store('documents/pitch_decks', 'public');
                Log::info('Pitch deck uploaded:', ['file_path' => $data['pitch_deck']]);
            }

            if ($request->hasFile('other_document')) {
                $data['other_document'] = $request->file('other_document')->store('documents/others', 'public');
                Log::info('Other document uploaded:', ['file_path' => $data['other_document']]);
            }

            if ($request->signature) {
                $data['signature'] = $request->signature;
                Log::info('Signature received:', ['signature' => $data['signature']]);
            }

            // Create the document record
            StartupToMatchInvestorDocument::create($data);

            // Log success
            Log::info('Documents saved successfully.');

            return response()->json(['message' => 'Documents saved successfully!']);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error while saving documents: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllDocuments()
    {
        $documents = StartupToMatchInvestorDocument::with('match')->orderBy('created_at', 'desc')->get();

        if ($documents->isEmpty()) {
            return response()->json([
                'message' => 'No documents found.',
            ], 404);
        }

        return response()->json([
            'message' => 'All documents retrieved successfully.',
            'data' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'match_id' => $doc->match_id,
                    'pitch_deck' => $doc->pitch_deck,
                    'other_document' => $doc->other_document,
                    'signature' => $doc->signature,
                    'status' => $doc->status,
                    'admin_comment' => $doc->admin_comment,
                    'created_at' => $doc->created_at,
                ];
            }),
        ]);
    }
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_comment' => 'nullable|string',
        ]);

        $document = StartupToMatchInvestorDocument::find($id);

        if (!$document) {
            return response()->json([
                'message' => 'Document not found.'
            ], 404);
        }

        $document->status = $request->status;
        $document->admin_comment = $request->admin_comment;
        $document->save();

        return response()->json([
            'message' => 'Document status updated successfully.',
            'data' => $document
        ]);
    }

    public function uploadDocuments(Request $request)
    {
        try {
            Log::info('Upload document request started.', [
                'match_id' => $request->match_id,
                'has_pitch_deck' => $request->hasFile('pitch_deck'),
                'has_other_document' => $request->hasFile('other_document'),
                'has_signature' => !empty($request->signature),
            ]);

            $request->validate([
                'match_id' => 'required|exists:investor_startup_matches,id',
                'pitch_deck' => 'nullable|file|mimes:pdf,ppt,pptx',
                'other_document' => 'nullable|file|mimes:pdf,doc,docx',
                'signature' => 'nullable|string',
            ]);

            $data = ['match_id' => $request->match_id];

            if ($request->hasFile('pitch_deck')) {
                $data['pitch_deck'] = $request->file('pitch_deck')->store('pitch_decks', 'public');
                Log::info('Pitch deck uploaded.', ['path' => $data['pitch_deck']]);
            }

            if ($request->hasFile('other_document')) {
                $data['other_document'] = $request->file('other_document')->store('other_documents', 'public');
                Log::info('Other document uploaded.', ['path' => $data['other_document']]);
            }

            if ($request->signature) {
                $data['signature'] = $request->signature;
                Log::info('Signature received as base64 string.');
            }

            $document = InvestorStartupDocument::create($data);

            Log::info('Document record created.', ['document_id' => $document->id]);

            return response()->json([
                'message' => 'Documents submitted successfully.',
                'document_id' => $document->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading documents: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function investorGetAllDocuments()
    {
        $documents = InvestorStartupDocument::all()->map(function ($doc) {
            return [
                'id' => $doc->id,
                'match_id' => $doc->match_id,
                'pitch_deck' => $doc->pitch_deck,
                'other_document' => $doc->other_document,
                'signature' => $doc->signature,
                'status' => $doc->status,
                'admin_comment' => $doc->admin_comment,
                'created_at' => $doc->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'documents' => $documents
        ], 200);
    }


    // PUT /api/investor/update-document-status/{id}
    public function investorDocumentStatusUpdate(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $document = InvestorStartupDocument::find($id);

        if (!$document) {
            return response()->json(['success' => false, 'message' => 'Document not found'], 404);
        }

        $document->status = $request->status;
        $document->save();

        return response()->json([
            'success' => true,
            'message' => 'Document status updated',
            'document' => $document
        ], 200);
    }



}
