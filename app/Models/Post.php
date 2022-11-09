<?php

namespace App\Models;

use App\Http\Traits\CommonTrait;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, CommonTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'media_url',
        'media_type',
        'library_id',
        'video_id',
        'country_id',
        'state_id',
        'district_id',
        'taluka_id',
        'latitude',
        'longitude',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'media_thumbnail',
        'media_webp',
        'already_like',
    ];

    public function getMediaUrlAttribute($value)
    {
        if($this->status === config('constant.status.active_value')){
            return $this->video_id ? config('constant.video_base_url').$this->video_id.'/playlist.m3u8' : NULL;
        }else{
            return getFileURL($value);
        }
    }

    public function getMediaThumbnailAttribute()
    {
        return $this->video_id ? config('constant.video_base_url').$this->video_id.'/thumbnail.jpg' : NULL;
    }

    public function getMediaWebpAttribute()
    {
        return $this->video_id ? config('constant.video_base_url').$this->video_id.'/preview.webp' : NULL;
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'iUserId');
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

    public function categories()
    {
        return $this->belongsToMany(MasterCategories::class, 'category_post', 'post_id', 'category_id');
    }

    public function languages()
    {
        return $this->belongsToMany(MasterLanguages::class, 'language_post', 'post_id', 'language_id');
    }

    public function hashtags()
    {
        return $this->belongsToMany(MasterHashtags::class, 'hashtag_post', 'post_id', 'hashtag_id');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'post_likes', 'post_id', 'user_id')->withTimestamps();
    }

    public function report()
    {
        return $this->belongsToMany(User::class, 'post_reports', 'post_id', 'user_id');
    }

    public function block()
    {
        return $this->belongsToMany(User::class, 'block_post', 'post_id', 'user_id');
    }

    public function getAlreadyLikeAttribute()
    {
        $alreadyLike = 0;
        $userId = 0;
        if(auth('sanctum')->check()){
            $userId = auth('sanctum')->user()->iUserId;
        }else if(auth()->check()){
            $userId = auth()->user()->iUserId;
        }

        if(auth()->check() || auth('sanctum')->check()){
            $alreadyLike = $this->likes();
            $alreadyLike->where('user_id', $userId);
            $alreadyLike = $alreadyLike->count();
        }


        return $alreadyLike > 0 ? true : false;
    }

    public function scopeNearest($query, $centerLat, $centerLng)
    {
        return $query
            ->addSelect(\DB::raw("posts.id,posts.latitude,posts.longitude,(6371  *
                                acos(
                                        cos( radians(" . $centerLat . ") ) *
                                        cos( radians( posts.latitude ) ) *
                                        cos( radians( posts.longitude ) - radians(" . $centerLng . ") ) +
                                        sin( radians(" . $centerLat . ")) *
                                        sin( radians( posts.latitude ) )
                                    )
                            )
                            AS distance"))
            ->orderByRaw('distance ASC,posts.created_at DESC');
    }
}
