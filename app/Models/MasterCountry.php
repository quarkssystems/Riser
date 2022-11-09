<?php

namespace App\Models;

use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCountry extends Model
{
    use HasFactory, SoftDeletes, CommonTrait;

    protected $fillable = [
        'name',
        'status'
    ];

    protected $hidden = [
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function state()
    {
        return $this->hasMany(MasterState::class, 'country_id', 'id');
    }

}
