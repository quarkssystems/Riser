<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCategories extends Model
{
    use HasFactory, SoftDeletes, CommonTrait;

    protected $fillable = [
        'category_name',
        'category_image',
        'category_description',
        'status'
    ];

    protected $appends = [
        'category_image_url',
    ];

    protected $hidden = [
        'pivot',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function masterClass()
    {
        return $this->belongsToMany(MasterClass::class, 'category_master_class', 'category_id', 'master_class_id');
    }

    public function getCategoryImageUrlAttribute()
    {
        if(fileExists($this->category_image)) {
            return getFileURL($this->category_image);
        }

        return null;
    }
}
