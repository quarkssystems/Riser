<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsPages extends Model
{
       use HasFactory, SoftDeletes, CommonTrait;

        protected $fillable = [
            'page_title',
            'slug',
            'page_content',
            'status',
        ];

        protected $hidden = [
            'created_at',
            'updated_at',
            'deleted_at',
        ];
}
