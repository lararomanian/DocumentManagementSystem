<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules()
    {
        $documentId = $this->route('documents');

        return [
            'file' => [
                'required',
                // 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
                Rule::unique('documents')->ignore($documentId),
            ],
            'title' => [
                'required',
                Rule::unique('documents')->ignore($documentId),
            ],
            'description' => 'nullable',
            'slug' => 'required',
            'status' => 'required',
            "project_id" => "required",
        ];
    }


    public function messages()
    {
        return [
            "required" => "The :attribute is required",
            "unique" => "The :attribute already exists",
            "mimes" => "The :attribute must be a file of type: :values.",
        ];
    }

    public function attributes()
    {
        return [
            "file" => "File",
            "title" => "Title",
            "description" => "Description",
            "slug" => "Slug",
            "status" => "Status",
            "lang" => "Language",
            "project_id" => "Project",
        ];
    }
}
