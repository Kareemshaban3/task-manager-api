<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'completed',
        'user_id',
    ];

    // العلاقة: كل مهمة مرتبطة بمستخدم واحد
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
