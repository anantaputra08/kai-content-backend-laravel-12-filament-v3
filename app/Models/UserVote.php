<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVote extends Model
{
    protected $fillable = [
        'voting_id',
        'voting_option_id',
        'user_identifier'
    ];

    public function voting()
    {
        return $this->belongsTo(Voting::class);
    }

    public function votingOption()
    {
        return $this->belongsTo(VotingOption::class);
    }

}
