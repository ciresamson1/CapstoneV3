<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationCustom extends Model
{
    protected $table = 'notifications_custom';

    protected $fillable = [
        'user_id',
        'type',
        'related_id',
        'message',
        'is_read'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}