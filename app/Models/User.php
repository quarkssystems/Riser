<?php

namespace App\Models;

use Carbon\Carbon;
use App\Http\Traits\CommonTrait;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use williamcruzme\FCM\Traits\HasDevices;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;

class User extends Authenticatable implements Wallet, WalletFloat
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, CommonTrait, HasDevices, HasWallet, HasWalletFloat;

    protected $guarded = ['iUserId'];
    protected $table = "tbl_users";
    protected $primaryKey = 'iUserId';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'roles',
        //'pivot',
        'password',
        'vPassword',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'created_at' => 'datetime:Y-m-d h:i:s',
        // 'updated_at' => 'datetime:Y-m-d h:i:s',
        // 'deleted_at' => 'datetime:Y-m-d h:i:s',
    ];

    protected $appends = [
        'profile_picture_url',
        'full_name',
        'following_this_user'
    ];

    public function adminlte_image()
    {
        if(fileExists($this->vImage)) {
            return getFileURL($this->vImage);
        }else if(filter_var($this->vImage, FILTER_VALIDATE_URL)){
            return $this->vImage;
        }

        return url('default-images/profile-pictures.png');
    }

    public function adminlte_desc()
    {
        return $this->vEmail;
    }

    public function adminlte_profile_url()
    {
        return 'admin/user/'.auth()->id();
    }

    public function socialAccounts(){
        return $this->hasMany(SocialAccount::class, 'user_id', 'iUserId');
    }

    public function country()
    {
        return $this->hasOne(MasterCountry::class, 'id', 'country_id');
    }

    public function state()
    {
        return $this->hasOne(MasterState::class, 'id', 'state_id');
    }

    public function district()
    {
        return $this->hasOne(MasterDistrict::class, 'id', 'district_id');
    }

    public function taluka()
    {
        return $this->hasOne(MasterTaluka::class, 'id', 'taluka_id');
    }

    public function getProfilePictureUrlAttribute()
    {
//        if(fileExists($this->vImage)) {
        // removed file exists condition due to performance issue
        if(!is_null($this->vImage) && Str::startsWith($this->vImage, 'profile-pictures')) {
            return getFileURL($this->vImage);
        }else if(filter_var($this->vImage, FILTER_VALIDATE_URL)){
            return $this->vImage;
        }

        return url('default-images/profile-pictures.png');
    }

    public function getFullNameAttribute()
    {
        return $this->vFirstName.' '.$this->vLastName;
    }

    public function getFollowingThisUserAttribute()
    {
        $follower = 0;
        $userId = 0;
        if(auth('sanctum')->check()){
            $userId = auth('sanctum')->user()->iUserId;
        }else if(auth()->check()){
            $userId = auth()->user()->iUserId;
        }

        if(auth()->check() || auth('sanctum')->check()){
            $follower = $this->follower();
            $follower->where('follower_id', $userId);
            $follower = $follower->count();
        }

        return $follower > 0 ? true : false;
    }

    public function follower()
    {
        return $this->belongsToMany(self::class, 'followers', 'user_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(self::class, 'followers', 'follower_id', 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'iUserId')->with('user')->orderBy('id', 'DESC');
    }

    public function masterClass()
    {
        return $this->hasMany(MasterClass::class, 'user_id', 'iUserId')->with('user');
    }

    public function masterClassPurchased()
    {
        return $this->belongsToMany(MasterClass::class, 'master_class_users', 'user_id', 'master_class_id')->withTimestamps()->withPivot('status')->with('user');
    }

    public function promotedMasterClass()
    {
        return $this->belongsToMany(MasterClass::class, 'master_class_promoters', 'user_id', 'master_class_id')->withTimestamps()->with('user');
    }

    public function report()
    {
        return $this->belongsToMany(self::class, 'profile_reports', 'user_id', 'reported_by');
    }

    public function block()
    {
        return $this->belongsToMany(User::class, 'block_user', 'block_user_id', 'user_id');
    }

    public function callBooking()
    {
        return $this->hasMany(CallBooking::class, 'user_id', 'iUserId')->with('user');
    }

    public function creatorCallBooking()
    {
        return $this->hasMany(CallBooking::class, 'creator_id', 'iUserId');
    }

    public function scopeFilterBooking($query, $request = null)
    {
        if (!empty($request->filter_by)) {

            $query->with('masterClassPurchased',function($q)use($request){
                if ($request->filter_by == config('constant.filter_by.today')) {
                    $today = Carbon::now()->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$today."'");
                }

                if ($request->filter_by == config('constant.filter_by.yesterday')) {
                    $yesterday = Carbon::yesterday()->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$yesterday."'");
                }

                if ($request->filter_by == config('constant.filter_by.7-day')) {
                    $today = Carbon::now()->format('Y-m-d');
                    $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') > '".$sevenDays."'");
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') <= '".$today."'");
                }

                if ($request->filter_by == config('constant.filter_by.this-month')) {
                    $thisMonth = Carbon::now()->format('Y-m');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$thisMonth."'");
                }

                if ($request->filter_by == config('constant.filter_by.last-month')) {
                    $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                    $q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$lastMonth."'");
                }
            });

        }
        return $query;
    }
}
