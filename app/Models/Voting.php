<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voting extends Model
{
    protected $fillable = [
        'train_id',
        'carriages_id',
        'title',
        'description',
        'is_active',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Sebuah voting dimiliki oleh satu carriage.
     */
    public function carriage()
    {
        return $this->belongsTo(Carriages::class, 'carriages_id');
    }
    public function train()
    {
        return $this->belongsTo(Train::class);
    }
    public function options()
    {
        return $this->hasMany(VotingOption::class);
    }

    public function userVotes()
    {
        return $this->hasMany(UserVote::class);
    }

    public function isActive(): bool
    {
        $now = now();
        return $this->is_active &&
            $this->start_time <= $now &&
            $this->end_time >= $now;
    }

    public function getTotalVotesAttribute(): int
    {
        return $this->options->sum('vote_count');
    }
}
