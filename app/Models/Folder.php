<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'parent_id', 'project_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function subfolders()
    {
        return $this->hasMany(Folder::class, 'parent_id', 'id')->with('subfolders')->with('documents');
    }

    public function documents()
    {
        return $this->hasMany(Documents::class, 'folder_id')->where('status', 1);
        // return $this->hasMany(Documents::class, 'folder_id');

    }

    public function getCreatedAtAttribute($value)
    {
        return date("d-m-Y H:i:s", strtotime($value));
    }

    public function getShortInfo()
    {
        return [
            'project_id' => $this->project_id,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'id' => $this->id,
            'documents' => $this->documents,
            'subfolders' => $this->subfolders->map->getShortInfo()
        ];
    }
}
