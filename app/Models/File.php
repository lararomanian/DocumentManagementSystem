<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'model_type',
        'model_id',
        'name',
        'path',
        'size',
        'extension'
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public static function boot()
    {
        parent::boot();
        try {
            static::creating(function ($file) {
                $file->size = $file->size;
                $file->extension = $file->extension;
            });
        } catch (\Exception $e) {
            //  dd($e);
        }
    }
}
