<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carriages extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function contents()
    {
        return $this->belongsToMany(Content::class, 'carriages_contents');
    }
}
