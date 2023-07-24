<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Documents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use League\CommonMark\Node\Block\Document;
use Illuminate\Support\Facades\Storage;

class DocumentsController extends Controller
{
    protected $controller, $model;

    public function __construct()
    {
        $this->controller = new ImageController();
        $this->model = new Documents();
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

            if ($request->has('file') && !empty($request->file)) {
                $files = $request->files;
                $this->uploadDocuments($documents, $files);
            }

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
        // return $request->all();
        try {
            $document = Documents::find($documents);
            $document->update($request->all());

            if ($request->ocr_text) {
                $document->ocr_text = $request->ocr_text;
                $document->save();
            }
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

    public function uploadDocuments($model, $files)
    {
        foreach ($files as $file) {
            $content_file_name = time() . '_' . (bin2hex(random_bytes(15))) . rand(1, 100) . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs("documents", $file, $content_file_name);
            $extension = $file->getClientOriginalExtension();
            $size = $file->getSize();
            $model->documents()->create([
                'name' => $content_file_name,
                'extension' => $extension,
                'size' => $size,
                'path' => $path
            ]);
        }
    }

    public function show($document)
    {

        $documents = Documents::find($document);

        if ($documents && !empty($documents)) {
            return response()->json([
                'data' => $documents,
                'message' => 'Successfully retrieved documents',
                'status' => 200
            ], 200);
        }

        return response()->json([
            'data' => "Not found",
            'message' => 'No such documents found',
            'status' => 404
        ], 404);
    }
}
