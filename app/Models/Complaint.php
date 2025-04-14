<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    /*
     * Kolom yang dapat diisi secara massal
     * user_id: ID pengguna yang mengajukan keluhan
     * category_complaint_id: ID kategori keluhan
     * description: Deskripsi keluhan
     * status: Status keluhan (baru, dalam proses, selesai)
     * attachment: Lampiran terkait keluhan
     * resolution_date: Tanggal penyelesaian keluhan
     * resolution_notes: Catatan penyelesaian keluhan
     * assigned_to: ID petugas yang ditugaskan untuk menangani keluhan
     */
    protected $fillable = [
        'user_id',
        'category_complaint_id',
        'description',
        'status',
        'attachment',
        'resolution_date',
        'resolution_notes',
        'assigned_to',
    ];

    /*
     * Menggunakan 'user_id' sebagai foreign key
     * untuk menghubungkan dengan tabel users (pengirim keluhan)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
     * Menggunakan 'category_complaint_id' sebagai foreign key
     * untuk menghubungkan dengan tabel category_complaints
     */
    public function categoryComplaint()
    {
        return $this->belongsTo(CategoryComplaint::class, 'category_complaint_id');
    }

    /*
     * Menggunakan 'assigned_to' sebagai foreign key
     * untuk menghubungkan dengan tabel users (petugas yang menangani keluhan)
     */
    public function assignedTo()
    {
        // return $this->belongsTo(User::class, 'assigned_to');
        return $this->belongsTo(User::class, 'assigned_to')->where('role', 'operator');
    }
}