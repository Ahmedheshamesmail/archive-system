<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplyAttachment extends Model
{
    protected $table =
        'reply_attachments';

    protected $fillable = [

        'reply_id',

        'file_name',

        'stored_name',

        'file_path',

        'file_type',

        'file_extension',

        'file_size',

        'uploaded_by'
    ];

    public function reply()
    {
        return $this->belongsTo(
            Reply::class,
            'reply_id'
        );
    }
}
