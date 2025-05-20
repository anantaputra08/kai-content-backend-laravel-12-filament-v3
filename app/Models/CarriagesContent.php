<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarriagesContent extends Model
{
    protected $table = 'carriages_contents';

    protected $fillable = [
        'carriages_id',
        'content_id',
    ];

    public function carriages()
    {
        return $this->belongsTo(Carriages::class);
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
