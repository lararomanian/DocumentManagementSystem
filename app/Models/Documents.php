<?php

namespace App\Models;

use App\Traits\UserStampTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
}
