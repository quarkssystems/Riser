<?php

namespace App\Models;

use Carbon\Carbon;
use App\Http\Traits\CommonTrait;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentPercentage extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, CommonTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'module_name',
        'role',
        'parent_role',
        'percentage',
        'hiddent_cut_percent',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
