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

class CallBooking extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, CommonTrait;

    protected $appends = ['is_link_enabled'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'creator_id',
        'call_package_id',
        'booking_date',
        'start_time',
        'end_time',
        'booking_message',
        'booking_amount',
        'refresh_token',
        'meeting_id',
        'meeting_link',
        'trasaction_id',
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

    public function getStartTimeAttribute($value)
    {
        return Carbon::parse($value)->format('h:i A');
    }

    public function getEndTimeAttribute($value)
    {
        return Carbon::parse($value)->format('h:i A');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'iUserId');
    }

    public function creator(){
        return $this->belongsTo(User::class, 'creator_id', 'iUserId');
    }

    public function callPackage(){
        return $this->belongsTo(CallPackage::class);
    }

    public function transaction(){
        return $this->hasMany(PaymentTransaction::class, 'call_booking_id', 'id');
    }


    public function getIsLinkEnabledAttribute(): bool
    {
        $action = false;
        //if (!is_null($this->meeting_link)){

            $bookingStartDateTime = Carbon::parse($this->booking_date)->setTimeFromTimeString(Carbon::parse($this->start_time)->format('H:i'))->timezone('Asia/Kolkata')->addHours(-5)->addMinutes(-35);
            $bookingEndDateTime = Carbon::parse($this->booking_date)->setTimeFromTimeString(Carbon::parse($this->end_time)->format('H:i'))->timezone('Asia/Kolkata')->addHours(-5)->addMinutes(-30);
            $now = Carbon::createFromFormat('Y-m-d H:i:s', (Carbon::now())->toDateTimeString())->setTimezone('Asia/Kolkata');
            if ($now->between($bookingStartDateTime,  $bookingEndDateTime)){
                $action = true;
            }
        //}
        return $action;
    }

    public function scopeFilter($query, $request = null)
    {
        if (!empty($request->filter_by)) {

            if ($request->filter_by == config('constant.filter_by.today')) {
                $today = Carbon::now()->format('Y-m-d');
                $query->whereRaw("DATE_FORMAT(booking_date, '%Y-%m-%d') = '".$today."'");
            }

            if ($request->filter_by == config('constant.filter_by.yesterday')) {
                $yesterday = Carbon::yesterday()->format('Y-m-d');
                $query->whereRaw("DATE_FORMAT(booking_date, '%Y-%m-%d') = '".$yesterday."'");
            }

            if ($request->filter_by == config('constant.filter_by.7-day')) {
                $today = Carbon::now()->format('Y-m-d');
                $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                $query->whereRaw("DATE_FORMAT(booking_date, '%Y-%m-%d') > '".$sevenDays."'");
                $query->whereRaw("DATE_FORMAT(booking_date, '%Y-%m-%d') <= '".$today."'");
            }

            if ($request->filter_by == config('constant.filter_by.this-month')) {
                $thisMonth = Carbon::now()->format('Y-m');
                $query->whereRaw("DATE_FORMAT(booking_date, '%Y-%m') = '".$thisMonth."'");
            }

            if ($request->filter_by == config('constant.filter_by.last-month')) {
                $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                $query->whereRaw("DATE_FORMAT(booking_date, '%Y-%m') = '".$lastMonth."'");
            }

        }
        return $query;
    }
}
