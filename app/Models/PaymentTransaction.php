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

class PaymentTransaction extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, CommonTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'payment_gateway',
        'transaction_id',
        'master_class_id',
        'call_booking_id',
        'sub_total',
        'tax',
        'discount_amount',
        'discount_code',
        'total',
        'payment_type',
        'affiliate_user_id',
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

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'iUserId')->select('iUserId','vFirstName','vLastName','vImage','vEmail','vOccupation','vPhoneNumber');
    }
    
    public function affiliateUser(){
        return $this->belongsTo(User::class, 'affiliate_user_id', 'iUserId')->select('iUserId','vFirstName','vLastName','vImage','vEmail','vOccupation','vPhoneNumber');
    }

    public function masterClasses()
    {
        return $this->hasOne(MasterClass::class, 'id', 'master_class_id');
    }

    public function callBookings()
    {
        return $this->hasOne(CallBooking::class, 'id', 'call_booking_id');
    }
}
