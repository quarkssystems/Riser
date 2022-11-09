<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterLanguages extends Model
{
    use HasFactory, SoftDeletes, CommonTrait;

    protected $table = 'master_languages';

    protected $fillable = [
        'language_name',
        'short_name',
        'status'
    ];

    protected $hidden = [
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
