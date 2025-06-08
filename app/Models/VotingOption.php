<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingOption extends Model
{
    protected $fillable = [
        'voting_id',
        'content_id',
        'vote_count'
    ];

    public function voting()
    {
        return $this->belongsTo(Voting::class);
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function userVotes()
    {
        return $this->hasMany(UserVote::class);
    }

    public function getVotePercentageAttribute(): float
    {
        $totalVotes = $this->voting->total_votes;
        return $totalVotes > 0 ? round(($this->vote_count / $totalVotes) * 100, 1) : 0;
    }
}
