<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carriages extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function contents()
    {
        return $this->belongsToMany(Content::class, 'carriages_contents');
    }

    /**
     * Sebuah carriage bisa memiliki banyak sesi voting.
     */
    public function votings()
    {
        return $this->hasMany(Voting::class, 'carriages_id');
    }

    /**
     * Mendapatkan sesi voting yang sedang aktif untuk carriage ini.
     */
    public function activeVoting()
    {
        return $this->hasOne(Voting::class, 'carriages_id')->where('is_active', true);
    }

    /**
     * Mendapatkan sesi streaming yang terkait dengan carriage ini.
     */
    public function streams()
    {
        return $this->hasMany(Stream::class, 'carriage_id');
    }
}
