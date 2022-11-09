<?php

namespace App\Models;

use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComment extends Model
{
    use HasFactory, SoftDeletes, CommonTrait;

    protected $fillable = [
        'post_id',
        'user_id',
        'comments',
        'parent_id',
        'status',
    ];

    protected $hidden = [
        'pivot',
        'status',
        'updated_at',
        'deleted_at',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'iUserId');
    }

    // loads only direct children - 1 level
    public function children()
    {
        return $this->hasMany(PostComment::class, 'parent_id');
    }

    // recursive, loads all descendants
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
        // which is equivalent to:
        // return $this->hasMany('Survey', 'parent')->with('childrenRecursive);
    }

    // parent
    public function parent()
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    // all ascendants
    public function parentRecursive()
    {
        return $this->parent()->with('parentRecursive');
    }
}
