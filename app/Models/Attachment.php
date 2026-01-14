<?php

namespace App\Models;

use App\Models\Task;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['task_id', 'file_path', 'file_type'];

    public function task() {
        return $this->belongsTo(Task::class) ;
    }
}
