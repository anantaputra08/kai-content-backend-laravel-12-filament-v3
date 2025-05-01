<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class Reaction extends Model
{
    protected $fillable = [
        'user_id',
        'content_id',
        'reaction_type',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
