<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'to',
        'subject',
        'mailable_class',
        'status',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'to' => 'array',
        'sent_at' => 'datetime',
    ];

    public function getMailableShortNameAttribute(): string
    {
        if (empty($this->mailable_class)) {
            return '—';
        }
        return class_basename($this->mailable_class);
    }
}
