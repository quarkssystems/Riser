<?php

namespace App\Models;

use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes, CommonTrait;

    protected $fillable = [
        'name',
        'banner_image',
        'description',
        'status',
    ];

    protected $appends = [
        'banner_image_url',
    ];

    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getBannerImageUrlAttribute()
    {
        if(fileExists($this->banner_image)) {
            return getFileURL($this->banner_image);
        }

        return null;
    }

    public function bannerCategories()
    {
        return $this->belongsToMany(MasterBannerCategory::class, 'banner_category', 'category_id', 'banner_id');
    }
}
