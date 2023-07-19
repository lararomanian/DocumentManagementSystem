<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Documents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use League\CommonMark\Node\Block\Document;

class DocumentsController extends Controller
{
    protected $controller;

    public function __construct()
    {
        $this->controller = new ImageController();
    }

    public function index()
    {
        $documents = Documents::all();
        return response()->json([
            'data' => $documents,
            'message' => 'Successfully retrieved documents',
            'status' => 200
        ], 200);
    }

    public function store(DocumentRequest $request)
    {
        $validator = Validator::make($request->all(),  $request->rules(), $request->messages());

        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'message' => 'Validation failed',
                'status' => 422
            ], 422);
        }

        try {
            // return response()->json($request->all());
            $documents = Documents::create($request->all());
            $documents->ocr_text = $this->controller->convertPdfToImage($request->file, $request->lang);
            $documents->save();
            return response()->json([
                'data' => $documents,
                'message' => 'Successfully created documents',
                'status' => 200
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => $th->getMessage(),
                'message' => 'Failed to create documents',
                'status' => 422
            ], 422);
        }
    }

    public function update(Request $request, $documents)
    {
        // $validator = Validator::make($request->all(),  $request->rules(), $request->messages());

        // if ($validator->fails()) {
        //     return response()->json([
        //         'data' => $validator->errors(),
        //         'message' => 'Validation failed',
        //         'status' => 422
        //     ], 422);
        // }

        try {
            $document = Documents::find($documents);
            return $request->all();
            $document->update($request->all());
            return response()->json([
                'data' => $documents,
                'message' => 'Successfully updated documents',
                'status' => 200
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => $th->getMessage(),
                'message' => 'Failed to update documents',
                'status' => 422
            ], 422);
        }
    }
}
