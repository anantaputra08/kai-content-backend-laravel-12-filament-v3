<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Train extends Model
{
    protected $fillable = [
        'name', // Contoh: Argo Bromo Anggrek
        'route', // Contoh: Gambir - Surabaya Pasarturi
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
}
