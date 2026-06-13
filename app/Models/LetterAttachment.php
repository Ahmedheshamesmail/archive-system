<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterAttachment extends Model
{
    protected $table =
        'letter_attachments';

    protected $fillable = [
        'letter_id',
        'file_name',
        'stored_name',
        'file_path',
        'file_type',
        'file_extension',
        'file_size',
        'uploaded_by',
    ];

    public function letter()
    {
        return $this->belongsTo(
            Letter::class,
            'letter_id'
        );
    }

    public function uploader()
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}