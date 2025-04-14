<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryComplaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    // Relasi ke complaints
    // public function complaints()
    // {
    //     return $this->hasMany(Complaint::class, 'categoryComplain_id');
    // }
}