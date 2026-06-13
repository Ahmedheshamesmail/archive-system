<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReplyAttachment;

class ReplyAttachmentController
extends Controller
{
    public function view($id)
    {
        $file =
            ReplyAttachment::find($id);

        if (!$file) {

            return response()->json([
                'message' =>
                'File not found'
            ],404);
        }

        $path = storage_path(
            'app/public/' .
            $file->file_path
        );

        if (!file_exists($path)) {

            return response()->json([
                'message' =>
                'Physical file not found'
            ],404);
        }

        return response()->file(
            $path
        );
    }

    public function download($id)
    {
        $file =
            ReplyAttachment::find($id);

        if (!$file) {

            return response()->json([
                'message' =>
                'File not found'
            ],404);
        }

        $path = storage_path(
            'app/public/' .
            $file->file_path
        );

        if (!file_exists($path)) {

            return response()->json([
                'message' =>
                'Physical file not found'
            ],404);
        }

        return response()->download(
            $path,
            $file->file_name
        );
    }
}
