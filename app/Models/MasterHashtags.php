<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterHashtags extends Model
{
      use HasFactory, SoftDeletes, CommonTrait;

      protected $fillable = [
        'hashtag_name',
      ];

      protected $hidden = [
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
      ];
}
