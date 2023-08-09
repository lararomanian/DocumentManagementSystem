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
        // return $this->hasMany(Folder::class, 'parent_id', 'id')->where('project_id', $this->project_id)->with('subfolders')->with('documents');
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

    public function getAllSubfoldersAndDocuments()
    {
        $allData = $this->getShortInfo();

        if ($this->subfolders) {
            foreach ($this->subfolders as $subfolder) {
                $allData['subfolders'] = array_merge($allData['subfolders'], $subfolder->getAllSubfoldersAndDocuments()['subfolders']);
                $allData['documents'] = array_merge($allData['documents'], $subfolder->getAllSubfoldersAndDocuments()['documents']);
            }
        }

        return $allData;
    }

    // public function getShortInfo()
    // {
    //     return [
    //         'fileExt' => '',
    //         'is_dir' => true,
    //         'names' => $this->name,
    //         'paths' => "./files/A1_Main/{$this->name}",
    //         'the_time' => date('F j, Y \a\t g:i A', strtotime($this->created_at)), // Format the date as desired
    //         'subfolders' => $this->subfolders->map->getShortInfo(),
    //         'documents' => $this->documents->map->getDocumentInfo(),
    //     ];
    // }
}
