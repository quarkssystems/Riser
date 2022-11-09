<?php

namespace App\Models;

use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterBannerCategory extends Model
{
    use HasFactory, SoftDeletes, CommonTrait;

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function banners()
    {
        return $this->belongsToMany(Banner::class, 'banner_category', 'banner_id', 'category_id');
    }
}
