<?php

namespace App\Models;

use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterState extends Model
{
    use HasFactory, SoftDeletes, CommonTrait;

    protected $fillable = [
        'name',
        'language_code',
        'country_id',
        'status'
    ];

    protected $hidden = [
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function country()
    {
        return $this->hasOne(MasterCountry::class, 'id', 'country_id');
    }

    public function district()
    {
        return $this->hasMany(MasterDistrict::class, 'state_id', 'id');
    }
}
