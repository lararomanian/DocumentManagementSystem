<?php

namespace App\Models;

use App\Traits\UserStampTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, UserStampTrait;

    protected $fillable = ["name", "description", "image", "slug", "status", "created_by"];

    protected $table = "projects";

    public function users()
    {
        return $this->hasMany(User::class); // One-to-many relationship
    }

    public function projectUsers()
    {
        return $this->belongsToMany(User::class, ); // Many-to-many relationship
    }

    public function getJoinedAtAttribute()
    {
        return $this->created_at;
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $value ? $value : Str::slug($this->name);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }

    public function getAllDocumentCount()
    {
        return $this->folders()->withCount(['documents' => function ($query) {
            $query->where('status', 1);
        }])->get()->sum('documents_count');
    }
}
