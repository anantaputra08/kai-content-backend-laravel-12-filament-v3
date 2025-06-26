<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    protected $fillable = [
        'content_id',
        'train_id',
        'carriage_id',
        'start_airing_time',
        'end_airing_time',
    ];

    /**
     * Get the train that owns the stream.
     */
    public function train()
    {
        return $this->belongsTo(Train::class, 'train_id');
    }

    /**
     * Get the carriege that owns the stream.
     */
    public function carriage()
    {
        return $this->belongsTo(Carriages::class, 'carriage_id');
    }

    /**
     * Get the content that is being streamed.
     */
    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
