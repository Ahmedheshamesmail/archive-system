<?php

namespace App\Http\Controllers\Api;

use App\Models\Reply;
use App\Models\Letter;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ReplyAttachment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ReplyController extends Controller
{
    public function store(Request $request,$id)
    {
        $request->validate([

            'reply_number' =>
                'nullable|string|max:100|unique:replies,reply_number',

            'reply_date' =>
                'required|date',

            'notes' =>
                'nullable|string',

            'files' =>
                'required|array|min:1',

            'files.*' =>
                'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ], [

            'reply_number.unique' =>
                'رقم الرد موجود بالفعل'
        ]);

        DB::beginTransaction();

        try {

            $letter =
                Letter::find($id);

            if (!$letter) {

                return response()->json([
                    'message' =>
                    'Letter not found'
                ],404);
            }

            $reply =
                Reply::create([

                    'letter_id' =>
                        $letter->id,

                    'reply_number' =>
                        $request->reply_number,

                    'reply_date' =>
                        $request->reply_date,

                    'notes' =>
                        $request->notes,

                    'created_by' =>
                        auth()->id()
                ]);

            $attachments = [];

            foreach (
                $request->file('files')
                as $file
            ) {

                $extension =
                    $file
                    ->getClientOriginalExtension();

                $mimeType =
                    $file
                    ->getMimeType();

                $originalName =
                    $file
                    ->getClientOriginalName();

                $generatedName =
                    Str::uuid() .
                    '.' .
                    $extension;

                $path =
                    $file->storeAs(
                        'replies',
                        $generatedName,
                        'public'
                    );

                $attachment =
                    ReplyAttachment::create([

                        'reply_id' =>
                            $reply->id,

                        'file_name' =>
                            $originalName,

                        'stored_name' =>
                            $generatedName,

                        'file_path' =>
                            $path,

                        'file_type' =>
                            $mimeType,

                        'file_extension' =>
                            $extension,

                        'file_size' =>
                            $file
                            ->getSize(),

                        'uploaded_by' =>
                            auth()->id()
                    ]);

                $attachments[] = [

                    'id' =>
                        $attachment->id,

                    'file_name' =>
                        $attachment
                        ->file_name,

                    'file_type' =>
                        $attachment
                        ->file_type,

                    'file_size' =>
                        $attachment
                        ->file_size,

                    'view_url' =>
                        url(
                        "/api/reply-attachments/{$attachment->id}/view"
                        ),

                    'download_url' =>
                        url(
                        "/api/reply-attachments/{$attachment->id}/download"
                        )
                ];
            }

            AuditLog::create([

                'user_id' =>
                    auth()->id(),

                'action' =>
                    'CREATE_REPLY',

                'entity_type' =>
                    'replies',

                'entity_id' =>
                    $reply->id,

                'details' =>
                    json_encode([

                        'letter_id' =>
                            $letter->id,

                        'reply_id' =>
                            $reply->id,

                        'files_count' =>
                            count(
                                $attachments
                            )
                    ])
            ]);

            DB::commit();

            return response()->json([

                'message' =>
                    'Reply created successfully',

                'data' => [

                    'reply' =>
                        $reply,

                    'attachments' =>
                        $attachments
                ]

            ],201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'message' =>
                    'Error creating reply',

                'error' =>
                    $e->getMessage()

            ],500);
        }
    }

    public function index($id)
{
    $letter =
        Letter::find($id);

    if (!$letter) {

        return response()->json([
            'message' =>
            'Letter not found'
        ],404);
    }

    $replies = Reply::with([
        'creator',
        'attachments'
    ])
    ->where(
        'letter_id',
        $id
    )
    ->latest()
    ->get();

    return response()->json([

        'message' =>
            'Replies fetched successfully',

        'data' =>
            $replies

    ],200);
    }


public function show($id)
{
    $reply = Reply::with([
        'attachments',
        'letter',
        'creator'
    ])->find($id);

    if (!$reply) {

        return response()->json([
            'message' =>
                'Reply not found'
        ], 404);
    }

    return response()->json([

        'message' =>
            'Reply retrieved successfully',

        'data' => [

            'reply' => [

                'id' =>
                    $reply->id,

                'reply_number' =>
                    $reply->reply_number,

                'reply_date' =>
                    $reply->reply_date,

                'notes' =>
                    $reply->notes,

                'letter_id' =>
                    $reply->letter_id,

                'created_by' =>
                    $reply->created_by,

                'creator' =>
                    $reply->creator,

                'letter' =>
                    $reply->letter,

                'attachments' =>
                    $reply
                    ->attachments
                    ->map(function ($file) {

                    return [

                        'id' =>
                            $file->id,

                        'file_name' =>
                            $file->file_name,

                        'file_type' =>
                            $file->file_type,

                        'file_size' =>
                            $file->file_size,

                        'view_url' =>
                            url(
                                "/api/reply-attachments/{$file->id}/view"
                            ),

                        'download_url' =>
                            url(
                                "/api/reply-attachments/{$file->id}/download"
                            )
                    ];
                })
            ]
        ]
    ]);
}


    public function update(Request $request,$id)
{
    $request->validate([

        'reply_number' =>
            'nullable|string|max:100',

        'reply_date' =>
            'nullable|date',

        'notes' =>
            'nullable|string',

        'files' =>
            'nullable|array',

        'files.*' =>
            'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',

        'deleted_attachments' =>
            'nullable|array'
    ]);

    DB::beginTransaction();

    try {

        $reply =
            Reply::with(
                'attachments'
            )->find($id);

        if (!$reply) {

            return response()->json([
                'message' =>
                    'Reply not found'
            ],404);
        }

        /**
         * update data
         */
        $updateData = [];

        if (
            $request->filled(
                'reply_number'
            )
        ) {
            $updateData[
                'reply_number'
            ] =
                $request
                ->reply_number;
        }

        if (
            $request->filled(
                'reply_date'
            )
        ) {
            $updateData[
                'reply_date'
            ] =
                $request
                ->reply_date;
        }

        if (
            $request->filled(
                'notes'
            )
        ) {
            $updateData[
                'notes'
            ] =
                $request
                ->notes;
        }

        if (
            !empty(
                $updateData
            )
        ) {
            $reply->update(
                $updateData
            );
        }

        /**
         * delete attachments
         */
        if (
            $request->filled(
                'deleted_attachments'
            )
        ) {

            $attachments =
                ReplyAttachment::whereIn(
                    'id',
                    $request
                    ->deleted_attachments
                )->where(
                    'reply_id',
                    $reply->id
                )->get();

            foreach (
                $attachments
                as $attachment
            ) {

                Storage::disk(
                    'public'
                )->delete(
                    $attachment
                    ->file_path
                );

                $attachment
                    ->delete();
            }
        }

        /**
         * upload files
         */
        if (
            $request->hasFile(
                'files'
            )
        ) {

            $files =
                $request->file(
                    'files'
                );

            if (
                !is_array(
                    $files
                )
            ) {
                $files = [
                    $files
                ];
            }

            foreach (
                $files
                as $file
            ) {

                $extension =
                    $file
                    ->getClientOriginalExtension();

                $mimeType =
                    $file
                    ->getMimeType();

                $originalName =
                    $file
                    ->getClientOriginalName();

                $generatedName =
                    \Illuminate\Support\Str::uuid()
                    . '.' .
                    $extension;

                $path =
                    $file->storeAs(
                        'replies',
                        $generatedName,
                        'public'
                    );

                ReplyAttachment::create([

                    'reply_id' =>
                        $reply->id,

                    'file_name' =>
                        $originalName,

                    'stored_name' =>
                        $generatedName,

                    'file_path' =>
                        $path,

                    'file_type' =>
                        $mimeType,

                    'file_extension' =>
                        $extension,

                    'file_size' =>
                        $file
                        ->getSize(),

                    'uploaded_by' =>
                        auth()->id()
                ]);
            }
        }

        /**
         * audit log
         */
        AuditLog::create([

            'user_id' =>
                auth()->id(),

            'action' =>
                'UPDATE_REPLY',

            'entity_type' =>
                'replies',

            'entity_id' =>
                $reply->id,

            'details' =>
                json_encode([

                    'reply_id' =>
                        $reply->id,

                    'updated_fields' =>
                        array_keys(
                            $updateData
                        ),

                    'deleted_attachments' =>
                        $request
                        ->deleted_attachments
                        ?? []
                ])
        ]);

        DB::commit();

        return response()->json([

            'message' =>
                'Reply updated successfully',

            'data' =>
                $reply->fresh()
                ->load([
                    'attachments',
                    'creator'
                ])

        ],200);

    } catch (
        \Exception $e
    ) {

        DB::rollBack();

        return response()->json([

            'message' =>
                'Update failed',

            'error' =>
                $e->getMessage()

        ],500);
    }
    }


    public function destroy($id)
{
    DB::beginTransaction();

    try {

        $reply =
            Reply::find($id);

        if (!$reply) {

            return response()->json([
                'message' =>
                    'Reply not found'
            ],404);
        }

        $reply->delete();

        AuditLog::create([

            'user_id' =>
                auth()->id(),

            'action' =>
                'SOFT_DELETE_REPLY',

            'entity_type' =>
                'replies',

            'entity_id' =>
                $reply->id,

            'details' =>
                json_encode([

                    'reply_id' =>
                        $reply->id,

                    'reply_number' =>
                        $reply
                        ->reply_number
                ])
        ]);

        DB::commit();

        return response()->json([

            'message' =>
                'Reply deleted successfully'

        ],200);

    } catch (
        \Exception $e
    ) {

        DB::rollBack();

        return response()->json([

            'message' =>
                'Delete failed',

            'error' =>
                $e->getMessage()

        ],500);
    }
    }

    public function restore($id)
{
    DB::beginTransaction();

    try {

        $reply =
            Reply::withTrashed()
            ->find($id);

        if (!$reply) {

            return response()->json([
                'message' =>
                    'Reply not found'
            ],404);
        }

        if (
            !$reply->trashed()
        ) {

            return response()->json([
                'message' =>
                    'Reply is not deleted'
            ],400);
        }

        $reply->restore();

        AuditLog::create([

            'user_id' =>
                auth()->id(),

            'action' =>
                'RESTORE_REPLY',

            'entity_type' =>
                'replies',

            'entity_id' =>
                $reply->id,

            'details' =>
                json_encode([

                    'reply_id' =>
                        $reply->id
                ])
        ]);

        DB::commit();

        return response()->json([

            'message' =>
                'Reply restored successfully'

        ],200);

    } catch (
        \Exception $e
    ) {

        DB::rollBack();

        return response()->json([

            'message' =>
                'Restore failed',

            'error' =>
                $e->getMessage()

        ],500);
    }
}
}
