<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Documents;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use League\CommonMark\Node\Block\Document;
use Illuminate\Support\Facades\Storage;

class DocumentsController extends Controller
{
    protected $pdf_controller, $controller, $model, $query, $resource;
    protected $with = [];
    protected $search_terms = [];

    public function __construct()
    {
        $this->controller = new ImageController();
        $this->pdf_controller = new PdfActivitiesController();
        $this->model = new Documents();
        $this->query = $this->model->query();
        $this->resource = new DocumentResource($this->model);
    }

    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 10;
        if (count($this->with)) {
            $this->query->with(implode(',', $this->with));
        }

        if ($request->sort) {
            $this->sort($request->sort);
        }

        if ($request->search) {
            $this->search($request->search);
        }

        if ($request->filters) {
            $this->filter($request->filters);
        }

        // return response()->json($this->model->paginate($per_page));
        return $this->returnResponse($per_page);
    }

    public function sort($sort)
    {
        switch ($sort) {
            case 'a-z':
                $this->query->orderBy($this->sort_term, 'ASC');
                break;
            case 'n-o':
                $this->query->orderBy('created_at', 'DESC');
                break;
            case 'o-n':
                $this->query->orderBy('created_at', 'ASC');
                break;

            default:

                break;
        }
    }

    public function returnResponse($per_page)
    {
        return $this->resource::collection($this->query->orderBy('created_at', 'desc')->paginate($per_page)->appends(request()->query()));
    }

    public function search($search)
    {
        $search_terms = $this->search_terms;
        $this->query->where(function ($query) use ($search_terms, $search) {
            foreach ($search_terms as $term) {
                $query->orWhere($term, 'like', '%' . $search . '%');
            }
        });
    }

    public function filter($filters)
    {
        $filters = json_decode($filters);
        if ($filters->from_date) {
            $this->query->where('created_at', '>=', $filters->from_date);
        }
        if ($filters->to_date) {
            $this->query->where('created_at', '<=', $filters->to_date);
        }
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

            $folder = Folder::find($request->folder_id);
            if (!$folder) {
                return response()->json([
                    'data' => "Not found",
                    'message' => 'No such folder found',
                    'status' => 404
                ], 404);
            }

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

    public function update(Request $request)
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
            // return $request->all();

            $folder = Folder::find($request->folder_id);
            if (!$folder) {
                return response()->json([
                    'data' => "Not found",
                    'message' => 'No such folder found',
                    'status' => 404
                ], 404);
            }

            $document = Documents::find($request->id);
            $document->update($request->all());
            if ($request->ocr_text) {
                $document->ocr_text = $request->ocr_text;
                $document->save();
            }
            return response()->json([
                'data' => $document,
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

    public function delete($document)
    {
        $documents = Documents::find($document);

        if ($documents && !empty($documents)) {
            $documents->delete();
            return response()->json([
                'message' => 'Successfully deleted documents',
                'status' => 200
            ], 200);
        }

        return response()->json([
            'data' => "Not found",
            'message' => 'No such documents found',
            'status' => 404
        ], 404);
    }


    public function exportPDF($document)
    {
        $documents = Documents::find($document);

        if ($documents && !empty($documents)) {
        return    $this->pdf_controller->exportPDF($documents);

            return response()->json([
                'message' => 'Successfully exported pdf',
                'status' => 200
            ], 200);
        }

        return response()->json([
            'data' => "Not found",
            'message' => 'No such documents found',
            'status' => 404
        ], 404);
    }

    public function exportFolder($folder)
    {
        $folder = Folder::where('id', $folder)->with('subfolders')->first();

        if ($folder && !empty($folder)) {
            return $this->pdf_controller->exportFolder($folder);
            return response()->json([
                'message' => 'Successfully exported folder',
                'status' => 200
            ], 200);
        }

        return response()->json([
            'data' => "Not found",
            'message' => 'No such folder found',
            'status' => 404
        ], 404);
    }

}
