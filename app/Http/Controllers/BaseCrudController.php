<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
// use Spatie\Activitylog\Models\Activity;

class BaseCrudController extends Controller
{

    protected $model;
    protected $request;
    protected $with = [];
    protected $query;
    protected $search_terms = [];
    protected $sort_term;
    protected $resource;

    public function __construct()
    {
        $this->setup();
        $this->query = $this->model->query();
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

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->request->rules(),
            $this->request->messages(),
            $this->request->attributes(),
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only($this->model->getFillable());
        $data = array_merge($data, $this->defaultData('create'));
        $item = $this->model->create($data);
        return response()->json(['message' => 'Data Created Successfully !!!', 'data' => $item], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->request->rules(),
            $this->request->messages(),
            $this->request->attributes(),
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = $this->model->find($request->id);
        if (!$item) {
            return response()->json(['errors' => 'Bad Request'], 404);
        }
        $data = $request->only($this->model->getFillable());
        $data = array_merge($data, $this->defaultData('update'));
        $item->update($data);
        return response()->json(['message' => 'Data Updated Successfully', 'data' => $item], 200);
    }

    public function delete($id)
    {
        $item = $this->model->find($id);
        if (!$item) {
            return response()->json(['error' => 'Bad Request !!'], 404);
        }
        if ($item->delete()) {
            return response()->json(['message' => 'Data Deleted !!']);
        }
        return response()->json(['error' => 'Error Deleting Data !!']);
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

    public function defaultData($operation)
    {
        $data = array(
            'create' => [],
            'update' => [],
        );

        return $data[$operation];
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
}
