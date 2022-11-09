<?php

namespace App\Models;

use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class TempUser extends Model
{
    use HasFactory, HasRoles, SoftDeletes, CommonTrait;

    protected $fillable = [
        'user_id',
        'user_role',
        'first_name',
        'last_name',
        'email',
        'username',
        'profile_picture',
        'gender',
        'profession',
        'contact_number',
        'whatsapp_number',
        'about_me',
        'user_skills',
        'user_experience',
        'business_name',
        'facebook_link',
        'twitter_link',
        'linkedin_link',
        'instagram_link',
        'youtube_link',
        'latitude',
        'longitude',
        'country_id',
        'state_id',
        'district_id',
        'taluka_id',
        'user_status',
        'user_note',
        'refer_code',
        'refer_user_id',
        'team_id',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'user_id');
    }
}
