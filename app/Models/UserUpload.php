<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserUpload extends Model
{
    use HasFactory;

    const UPLOAD_STATUS_PENDING = 1;
    const UPLOAD_STATUS_IN_PROCESS = 2;
    const UPLOAD_STATUS_FAILED = 3;
    const UPLOAD_STATUS_COMPLETED = 4;

    protected $fillable = [
        'user_id',
        'filename',
        'status',
    ];
}
