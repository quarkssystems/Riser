<?php

namespace App\Models;

use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDistrict extends Model
{
    use HasFactory, SoftDeletes, CommonTrait;

    protected $fillable = [
        'name',
        'state_id',
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
        return $this->hasOne(MasterState::class, 'id', 'state_id');
    }

    public function taluka()
    {
        return $this->hasMany(MasterTaluka::class, 'district_id', 'id');
    }
}
