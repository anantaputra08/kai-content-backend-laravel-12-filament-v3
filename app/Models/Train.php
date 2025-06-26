<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Train extends Model
{
    protected $fillable = [
        'name', // Contoh: Argo Bromo Anggrek
        'route', // Contoh: Gambir - Surabaya Pasarturi
        'departure_time', // Waktu keberangkatan
        'arrival_time', // Waktu kedatangan
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Define the many-to-many relationship with Content model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contents()
    {
        return $this->belongsToMany(Content::class, 'content_train');
    }

    /**
     * Get the streams associated with the train.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function streams()
    {
        return $this->hasMany(Stream::class, 'train_id');
    }
}
