<?php

namespace App\Models;

use App\Traits\UserStampTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, UserStampTrait;

    protected $fillable = ["name", "description", "image", "slug", "status", "created_by"];

    protected $table = "projects";

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function getJoinedAtAttribute()
    {
        return $this->created_at;
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = strtolower($value);
    }
}
