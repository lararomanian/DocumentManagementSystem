<?php

namespace App\Models;

use App\Traits\UserStampTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\File as ModelsFile;

class Documents extends Model
{
    use HasFactory, UserStampTrait;

    protected $fillable = [
        "title",
        "description",
        "slug",
        "file",
        "status",
        "created_by",
        "updated_by",
        "project_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "created_by", "id");
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $value ? $value : Str::slug($this->name);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, "project_id", "id");
    }

    public function getCreatedAtAttribute($value)
    {
        return date("d-m-Y H:i:s", strtotime($value));
    }

    public function documents()
    {
        return $this->morphMany(ModelsFile::class, 'model');
    }
}
