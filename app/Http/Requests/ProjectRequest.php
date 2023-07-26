<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
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
        $projectId = $this->route('project');

        return [
            // 'name' => [
            //     'sometimes',
            //     'required',
            //     Rule::unique('projects')->ignore($projectId),
            // ],
            'name' => 'required',
            "description" => "required",
            "image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg",
            "slug" => "required",
            "status" => "required",
        ];
    }

    public function attributes()
    {
        return [
            "name" => "Name",
            "description" => "Description",
            "image" => "Image",
            "slug" => "Slug",
            "status" => "Status",
        ];
    }
    public function messages()
    {
        return [
            "required" => "The :attribute is required",
            "unique" => "The :attribute already exists",
            "image" => "The :attribute must be an image",
        ];
    }
}
