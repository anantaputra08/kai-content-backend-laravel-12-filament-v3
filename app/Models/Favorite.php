<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'content_id'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the content associated with the favorite.
     */
    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_content');
    }

}
