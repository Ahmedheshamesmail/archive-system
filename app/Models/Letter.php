<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Letter extends Model
{
    use SoftDeletes;
    protected $table = 'letters';

    protected $fillable = [
        'letter_number',
        'subject',
        'category',
        'sender_entity',
        'letter_date',
        'created_by'
    ];

    protected $casts = [
        'letter_date' => 'date'
    ];

    public function replies()
    {
        return $this->hasMany(
            Reply::class,
            'letter_id'
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

        public function attachments()
    {
        return $this->hasMany(
            LetterAttachment::class,
            'letter_id'
        );
    }
}
