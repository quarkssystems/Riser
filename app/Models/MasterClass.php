<?php

namespace App\Models;

use Carbon\Carbon;
use App\Http\Traits\CommonTrait;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterClass extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, CommonTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'banner_image',
        'title',
        'start_date',
        'start_time',
        'end_time',
        'amount',
        'meeting_link',
        'refresh_token',
        'meeting_id',
        'duration',
        'user_id',
        'payment_settled',
        'notification_sent',
        'is_updatable',
        'is_master_class_started',
        'is_master_class_ended',
        'updated_by',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        //'pivot',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'is_promoted',
        'promoter_id',
        'already_purchased',
        'is_link_enabled',
        'joined_users_count'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'iUserId')->select('iUserId','vFirstName','vLastName','vImage','vEmail','vOccupation','vPhoneNumber');
    }

    public function userMasterClass()
    {
        return $this->belongsToMany(User::class, 'master_class_users', 'master_class_id', 'user_id')->whereNull('promoter_id')->withTimestamps()->withPivot('promoter_id')->select('iUserId','vFirstName','vLastName','vImage','vEmail','vOccupation','vPhoneNumber');
    }

    public function userMasterClassAdmin()
    {
        return $this->belongsToMany(User::class, 'master_class_users', 'master_class_id', 'user_id')->withTimestamps()->withPivot('promoter_id')->select('iUserId','vFirstName','vLastName','vImage','vEmail','vOccupation','vPhoneNumber');
    }

    public function promoteMasterClass()
    {
        return $this->belongsToMany(User::class, 'master_class_promoters', 'master_class_id', 'user_id')->withTimestamps()->select('iUserId','vFirstName','vLastName','vImage','vEmail','vOccupation','vPhoneNumber');
    }

    public function myAffilitorUsers()
    {
        return $this->belongsToMany(User::class, 'master_class_users', 'master_class_id', 'user_id')->whereNotNull('promoter_id')->withTimestamps()->withPivot('promoter_id')->select('iUserId','vFirstName','vLastName','vImage','vEmail','vOccupation','vPhoneNumber');
    }

    public function promoterMyUsers()
    {
        return $this->belongsToMany(User::class, 'master_class_users', 'master_class_id', 'user_id')->where('promoter_id', request()->user()->iUserId)->withTimestamps()->withPivot('promoter_id')->select('iUserId','vFirstName','vLastName','vImage','vEmail','vOccupation','vPhoneNumber');
    }

    public function promoterMyAffilitorUsers()
    {
        return $this->belongsToMany(User::class, 'master_class_users', 'master_class_id', 'user_id')->where(function($q){
            //$q->whereNull('promoter_id')->orWhere('promoter_id','!=' ,request()->user()->iUserId);
            $q->whereNull('promoter_id');
        })->withTimestamps()->withPivot('promoter_id')->select('iUserId','vFirstName','vLastName','vImage','vEmail','vOccupation','vPhoneNumber');
    }

    public function bookingUsers()
    {
        return $this->belongsToMany(User::class, 'master_class_users', 'master_class_id', 'user_id')->where('master_class_users.status', config('constant.status.booked_value'))->select('iUserId','vFirstName','vLastName','vImage','vEmail','vOccupation','vPhoneNumber');
    }

    public function categories()
    {
        return $this->belongsToMany(MasterCategories::class, 'category_master_class', 'master_class_id', 'category_id');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'master_class_id', 'id');
    }

    public function getBannerImageAttribute($value)
    {
        if(fileExists($value)) {
            return getFileURL($value);
        }

        return url('default-images/masterclass.png');

    }

    public function getStartTimeAttribute($value)
    {
        return Carbon::parse($value)->format('h:i A');
    }

    public function getEndTimeAttribute($value)
    {
        $value = $value ?? $this->start_time;
        $duration = $this->duration ?? 0;
        return Carbon::parse($value)->addMinutes($duration)->format('h:i A');
    }

    public function getIsPromotedAttribute()
    {
        $promoter = 0;
        $userId = 0;

        if(request()->user_id && request()->user_id != $this->user_id){
            $userId = request()->user_id;
        }else if(auth('sanctum')->check()){
            $userId = auth('sanctum')->user()->iUserId;
        }else if(auth()->check()){
            $userId = auth()->user()->iUserId;
        }

        if(auth()->check() || auth('sanctum')->check()){
            $promoter = $this->promoteMasterClass();
            $promoter->where('user_id', $userId);
            $promoter = $promoter->count();
        }

        return $promoter > 0 ? true : false;
    }

    public function getPromoterIdAttribute()
    {
        $promoter = 0;
        $userId = 0;

        if(request()->user_id && request()->user_id != $this->user_id){
            $userId = request()->user_id;
        }else if(auth('sanctum')->check()){
            $userId = auth('sanctum')->user()->iUserId;
        }else if(auth()->check()){
            $userId = auth()->user()->iUserId;
        }

        if(auth()->check() || auth('sanctum')->check()){
            $promoter = $this->promoteMasterClass();
            $promoter->where('user_id', $userId);
            $promoter = $promoter->count();
        }

        return $promoter > 0 ? (int)$userId : 0;
    }

    public function getAlreadyPurchasedAttribute()
    {
        $alreadyPurchased = 0 ;
        $userId = 0;

        if(request()->user_id && request()->user_id != $this->user_id){
            $userId = request()->user_id;
        }else if(auth('sanctum')->check()){
            $userId = auth('sanctum')->user()->iUserId;
        }else if(auth()->check()){
            $userId = auth()->user()->iUserId;
        }

        if(auth()->check() || auth('sanctum')->check()){
            // $alreadyPurchased = $this->userMasterClass();
            $alreadyPurchased = $this->bookingUsers();
            $alreadyPurchased->where('user_id', $userId);
            $alreadyPurchased = $alreadyPurchased->count();
        }

        return $alreadyPurchased > 0 ? true : false;
    }

    public function getJoinedUsersCountAttribute()
    {
        return $this->bookingUsers()->count();
    }

    public function scopeFilter($query, $request = null)
    {
        if (!empty($request->filter_by)) {

            $query->with('userMasterClass',function($q)use($request){
                if ($request->filter_by == config('constant.filter_by.today')) {
                    $today = Carbon::now()->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$today."'");
                    $q->whereNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.yesterday')) {
                    $yesterday = Carbon::yesterday()->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$yesterday."'");
                    $q->whereNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.7-day')) {
                    $today = Carbon::now()->format('Y-m-d');
                    $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') > '".$sevenDays."'");
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') <= '".$today."'");
                    $q->whereNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.this-month')) {
                    $thisMonth = Carbon::now()->format('Y-m');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$thisMonth."'");
                    $q->whereNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.last-month')) {
                    $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$lastMonth."'");
                    $q->whereNull('promoter_id');
                }
            });

            $query->with('myAffilitorUsers',function($q)use($request){
                if ($request->filter_by == config('constant.filter_by.today')) {
                    $today = Carbon::now()->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$today."'");
                    $q->whereNotNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.yesterday')) {
                    $yesterday = Carbon::yesterday()->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$yesterday."'");
                    $q->whereNotNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.7-day')) {
                    $today = Carbon::now()->format('Y-m-d');
                    $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') > '".$sevenDays."'");
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') <= '".$today."'");
                    $q->whereNotNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.this-month')) {
                    $thisMonth = Carbon::now()->format('Y-m');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$thisMonth."'");
                    $q->whereNotNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.last-month')) {
                    $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$lastMonth."'");
                    $q->whereNotNull('promoter_id');
                }
            });


        }
        return $query;
    }

    public function scopeFilterAffiliator($query, $request = null)
    {
        if (!empty($request->filter_by)) {

            $query->with('promoterMyUsers',function($q)use($request){
                if ($request->filter_by == config('constant.filter_by.today')) {
                    $today = Carbon::now()->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$today."'");
                    $q->where('promoter_id', request()->user()->iUserId);
                }

                if ($request->filter_by == config('constant.filter_by.yesterday')) {
                    $yesterday = Carbon::yesterday()->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$yesterday."'");
                    $q->where('promoter_id', request()->user()->iUserId);
                }

                if ($request->filter_by == config('constant.filter_by.7-day')) {
                    $today = Carbon::now()->format('Y-m-d');
                    $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') > '".$sevenDays."'");
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') <= '".$today."'");
                    $q->where('promoter_id', request()->user()->iUserId);
                }

                if ($request->filter_by == config('constant.filter_by.this-month')) {
                    $thisMonth = Carbon::now()->format('Y-m');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$thisMonth."'");
                    $q->where('promoter_id', request()->user()->iUserId);
                }

                if ($request->filter_by == config('constant.filter_by.last-month')) {
                    $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$lastMonth."'");
                    $q->where('promoter_id', request()->user()->iUserId);
                }
            });

            $query->with('promoterMyAffilitorUsers',function($q)use($request){
                if ($request->filter_by == config('constant.filter_by.today')) {
                    $today = Carbon::now()->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$today."'");
                    $q->whereNotNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.yesterday')) {
                    $yesterday = Carbon::yesterday()->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$yesterday."'");
                    $q->whereNotNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.7-day')) {
                    $today = Carbon::now()->format('Y-m-d');
                    $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') > '".$sevenDays."'");
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') <= '".$today."'");
                    $q->whereNotNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.this-month')) {
                    $thisMonth = Carbon::now()->format('Y-m');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$thisMonth."'");
                    $q->whereNotNull('promoter_id');
                }

                if ($request->filter_by == config('constant.filter_by.last-month')) {
                    $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$lastMonth."'");
                    $q->whereNotNull('promoter_id');
                }
            });


        }
        return $query;
    }

    public function scopeFilterPromoted($query, $request = null)
    {
        if (!empty($request->filter_by)) {

            if ($request->filter_by == config('constant.filter_by.today')) {
                $today = Carbon::now()->format('Y-m-d');
                $query->whereRaw("DATE_FORMAT(master_classes.start_date, '%Y-%m-%d') = '".$today."'");
            }

            if ($request->filter_by == config('constant.filter_by.yesterday')) {
                $yesterday = Carbon::yesterday()->format('Y-m-d');
                $query->whereRaw("DATE_FORMAT(master_classes.start_date, '%Y-%m-%d') = '".$yesterday."'");
            }

            if ($request->filter_by == config('constant.filter_by.7-day')) {
                $today = Carbon::now()->format('Y-m-d');
                $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                $query->whereRaw("DATE_FORMAT(master_classes.start_date, '%Y-%m-%d') > '".$sevenDays."'");
                $query->whereRaw("DATE_FORMAT(master_classes.start_date, '%Y-%m-%d') <= '".$today."'");
            }

            if ($request->filter_by == config('constant.filter_by.this-month')) {
                $thisMonth = Carbon::now()->format('Y-m');
                $query->whereRaw("DATE_FORMAT(master_classes.start_date, '%Y-%m') = '".$thisMonth."'");
            }

            if ($request->filter_by == config('constant.filter_by.last-month')) {
                $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                $query->whereRaw("DATE_FORMAT(master_classes.start_date, '%Y-%m') = '".$lastMonth."'");
            }

        }
        return $query;
    }

    public function getIsLinkEnabledAttribute(): bool
    {
        $action = false;
        if ($this->is_master_class_ended == "0"){
            $bookingStartDateTime = Carbon::parse($this->start_date)->setTimeFromTimeString(Carbon::parse($this->start_time)->format('H:i'))->timezone('Asia/Kolkata')->addHours(-5)->addMinutes(-35);
            $bookingEndDateTime = Carbon::parse($this->start_date)->setTimeFromTimeString(Carbon::parse($this->start_time)->addMinutes($this->duration ?? 30)->format('H:i'))->timezone('Asia/Kolkata')->addHours(-5)->addMinutes(0);
            $now = Carbon::createFromFormat('Y-m-d H:i:s', (Carbon::now())->toDateTimeString())->setTimezone('Asia/Kolkata');
            if ($now->between($bookingStartDateTime,  $bookingEndDateTime)){
                $action = true;
            }
        }
        return $action;
    }
}
