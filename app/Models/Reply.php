<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reply extends Model
{
    use SoftDeletes;
    protected $table =
        'replies';

    protected $fillable = [

        'letter_id',

        'reply_number',

        'reply_date',

        'notes',

        'created_by'
    ];

    protected $casts = [
        'reply_date' =>
            'date'
    ];

    public function letter()
    {
        return $this->belongsTo(
            Letter::class,
            'letter_id'
        );
    }

    public function attachments()
    {
        return $this->hasMany(
            ReplyAttachment::class,
            'reply_id'
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}