<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScholarshipApplicationUploadRequest;
use App\Models\Scholarship;
use App\Support\StagedUpload;
use Illuminate\Http\JsonResponse;

class ScholarshipApplicationUploadController extends Controller
{
    /**
     * Issue a short-lived link for sending one document straight to storage.
     *
     * The browser PUTs the file to the link, then submits the token with the
     * application form instead of the file, which keeps the form under the
     * 4.5 MB request limit Vercel enforces.
     */
    public function store(StoreScholarshipApplicationUploadRequest $request, Scholarship $scholarship): JsonResponse
    {
        $document = $scholarship->requiredDocuments->firstWhere('id', $request->integer('required_document_id'));

        $upload = StagedUpload::begin($request->user(), $document, $request->string('name')->value());

        return response()->json([
            ...$upload->uploadUrl(),
            'token' => $upload->token(),
        ]);
    }
}
