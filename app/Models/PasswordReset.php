<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table      = 'password_resets';
    protected $primaryKey = 'password_reset_id';
    public    $timestamps = false; // Bảng chỉ có created_at, không có updated_at

    protected $fillable = [
        'user_id',
        'role_id',
        'otp',
        'expired_at',
        'is_used',
        'attempts',
        'created_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}