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

class PaymentPayout extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, CommonTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'module_name',
        'module_id',
        'role',
        'percentage',
        'payout_amount',
        'parent_user_id',
        'parent_role',
        'parent_percentage',
        'parent_payout_amount',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    public function creator(){
        return $this->belongsTo(User::class, 'creator_id', 'iUserId');
    }

    public function masterClasses()
    {
        return $this->hasOne(MasterClass::class, 'id', 'module_id');
    }

    public function callBookings()
    {
        return $this->hasOne(CallBooking::class, 'id', 'module_id');
    }

    public function scopeFilter($query, $request = null)
    {
        if (!empty($request->filter_by)) {            
            
            if ($request->filter_by == config('constant.filter_by.today')) {
                $today = Carbon::now()->format('Y-m-d');
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') = '".$today."'");
            }

            if ($request->filter_by == config('constant.filter_by.yesterday')) {
                $yesterday = Carbon::yesterday()->format('Y-m-d');
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') = '".$yesterday."'");
            }

            if ($request->filter_by == config('constant.filter_by.7-day')) {
                $today = Carbon::now()->format('Y-m-d');
                $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') > '".$sevenDays."'");
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') <= '".$today."'");
            }

            if ($request->filter_by == config('constant.filter_by.this-month')) {
                $thisMonth = Carbon::now()->format('Y-m');
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = '".$thisMonth."'");
            }

            if ($request->filter_by == config('constant.filter_by.last-month')) {
                $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = '".$lastMonth."'");
            }                           
            
        }
        return $query;
    }
}
