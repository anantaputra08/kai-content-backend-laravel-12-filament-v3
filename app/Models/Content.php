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
        'status',
        'view_count',
        'total_watch_time',
        'rank',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship to category
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * @see \App\Models\Category
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Relationship to favorites
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @see \App\Models\Favorite
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

}
