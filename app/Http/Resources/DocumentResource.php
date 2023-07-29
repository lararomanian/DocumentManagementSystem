<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            "id" => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'file' => $this->file,
            'status' => $this->status,
            'ocr_text' => $this->ocr_text,
            'created_by' => $this->user->id,
            'created_by_user' => $this->user->name ?? null,
            'project' => $this->project->name ?? null,
            'project_id' => $this->project->id ?? null,
            'folder' => $this->folder->name ?? null,
            'folder_id' => $this->folder->id ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'documents' => $this->documents[0]->path ?? null,
        ];
    }
}
