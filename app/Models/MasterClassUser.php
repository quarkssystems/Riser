<?php

namespace App\Models;

use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterClassUser extends Model
{
    use HasFactory, CommonTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'master_class_id',
        'promoter_id',
    ];

    public function masterClass()
    {
        return $this->hasMany(MasterClass::class, 'id', 'master_class_id');
    }
}
