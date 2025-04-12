<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'title',
        'description',
        'category_id',
        'file_path',
        'status',
        'view_count',
        'total_watch_time',
        'rank',
    ];

    protected $casts = [
        // 'categories' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship to category
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
