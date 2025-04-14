<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryComplaint extends Model
{
    use HasFactory, SoftDeletes;

    /*
     * Kolom yang dapat diisi secara massal
     * name: Nama kategori keluhan
     * description: Deskripsi kategori keluhan
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /*
     * Menggunakan 'id' sebagai primary key
     * untuk menghubungkan dengan tabel complaints
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'category_complaint_id');
    }
}