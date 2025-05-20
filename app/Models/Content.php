<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'thumbnail_path',
        'type',
        'status',
        'view_count',
        'total_watch_time',
        'rank',
        'like',
        'dislike',
        'airing_time_start',
        'airing_time_end',
    ];

    protected $casts = [
        'view_count' => 'integer',
        'total_watch_time' => 'integer',
        'rank' => 'integer',
        'like' => 'integer',
        'dislike' => 'integer',
        'airing_time_start' => 'datetime:H:i',
        'airing_time_end' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function carriages()
    {
        return $this->belongsToMany(Carriages::class, 'carriages_contents');
    }
}
