<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Letter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\LetterAttachment;
use Illuminate\Support\Str;
use App\Traits\ApiResponse;

class LetterController extends Controller
{
    use ApiResponse;

//     public function index(Request $request)
// {
//     $query = Letter::query()
//         ->with([
//             'creator',
//             'attachments',
//             'replies'
//         ]);

//     /**
//      * search by letter number
//      */
//     if (
//         $request->filled(
//             'letter_number'
//         )
//     ) {

//         $query->where(
//             'letter_number',
//             'ILIKE',
//             '%' .
//             $request->letter_number .
//             '%'
//         );
//     }

//     /**
//      * search by subject
//      */
//     if (
//         $request->filled(
//             'subject'
//         )
//     ) {

//         $query->where(
//             'subject',
//             'ILIKE',
//             '%' .
//             $request->subject .
//             '%'
//         );
//     }

//     /**
//      * sender entity
//      */
//     if (
//         $request->filled(
//             'sender_entity'
//         )
//     ) {

//         $query->where(
//             'sender_entity',
//             'ILIKE',
//             '%' .
//             $request->sender_entity .
//             '%'
//         );
//     }

//     /**
//      * category
//      */
//     if (
//         $request->filled(
//             'category'
//         )
//     ) {

//         $query->where(
//             'category',
//             $request->category
//         );
//     }

//     /**
//      * date range
//      */
//     if (
//         $request->filled(
//             'from_date'
//         )
//     ) {

//         $query->whereDate(
//             'letter_date',
//             '>=',
//             $request->from_date
//         );
//     }

//     if (
//         $request->filled(
//             'to_date'
//         )
//     ) {

//         $query->whereDate(
//             'letter_date',
//             '<=',
//             $request->to_date
//         );
//     }

//     /**
//      * has replies
//      */
//     if (
//         $request->has(
//             'has_replies'
//         )
//     ) {

//         if (
//             $request
//             ->has_replies
//             == 'true'
//         ) {

//             $query->has(
//                 'replies'
//             );

//         } else {

//             $query->doesntHave(
//                 'replies'
//             );
//         }
//     }

//     /**
//      * sorting
//      */
//     $sortBy =
//         $request->get(
//             'sort_by',
//             'created_at'
//         );

//     $sortOrder =
//         $request->get(
//             'sort_order',
//             'desc'
//         );

//     $allowedSorts = [

//         'created_at',
//         'letter_date',
//         'letter_number'
//     ];

//     if (
//         !in_array(
//             $sortBy,
//             $allowedSorts
//         )
//     ) {

//         $sortBy =
//             'created_at';
//     }

//     $query->orderBy(
//         $sortBy,
//         $sortOrder
//     );

//     /**
//      * pagination
//      */
//     $perPage =
//         $request->get(
//             'per_page',
//             10
//         );

//     $letters =
//         $query->paginate(
//             $perPage
//         );

//         return $this->successResponse(
//             'Letters fetched successfully',
//             $letters
//         );
// }


public function index(Request $request)
{
    $query = Letter::query()
        ->with([
            'creator',
            'attachments',
            'replies'
        ]);

    /**
     * Global Search
     */
    $query->when(
        $request->filled('search'),
        function ($q) use ($request) {

            $search = trim($request->search);

            $q->where(function ($subQuery) use ($search) {

                $subQuery
                    ->where(
                        'letter_number',
                        'ILIKE',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'subject',
                        'ILIKE',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'sender_entity',
                        'ILIKE',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'category',
                        'ILIKE',
                        "%{$search}%"
                    );
            });
        }
    );

    /**
     * Category Filter
     */
    $query->when(
        $request->filled('category'),
        fn ($q) => $q->where(
            'category',
            $request->category
        )
    );

    /**
     * Date Range
     */
    $query->when(
        $request->filled('from_date'),
        fn ($q) => $q->whereDate(
            'letter_date',
            '>=',
            $request->from_date
        )
    );

    $query->when(
        $request->filled('to_date'),
        fn ($q) => $q->whereDate(
            'letter_date',
            '<=',
            $request->to_date
        )
    );

    /**
     * Replies Filter
     */
    if ($request->filled('has_replies')) {

        if ($request->has_replies === 'true') {

            $query->has('replies');

        } elseif ($request->has_replies === 'false') {

            $query->doesntHave('replies');
        }
    }

    /**
     * Sorting
     */
    $allowedSorts = [
        'created_at',
        'letter_date',
        'letter_number',
        'subject',
        'category'
    ];

    $sortBy = $request->get(
        'sort_by',
        'created_at'
    );

    if (!in_array($sortBy, $allowedSorts)) {
        $sortBy = 'created_at';
    }

    $sortOrder = strtolower(
        $request->get(
            'sort_order',
            'desc'
        )
    );

    if (!in_array($sortOrder, ['asc', 'desc'])) {
        $sortOrder = 'desc';
    }

    $query->orderBy(
        $sortBy,
        $sortOrder
    );

    /**
     * Pagination
     */
    $perPage = (int) $request->get(
        'per_page',
        10
    );

    $letters = $query->paginate(
        $perPage
    );

    return $this->successResponse(
        'Letters fetched successfully',
        $letters
    );
}
    public function store(Request $request)
    {
        $request->validate([

            'letter_number' =>
                'required|unique:letters,letter_number',

            'subject' =>
                'required|string|max:255',

            'category' =>
                'nullable|string|max:255',

            'sender_entity' =>
                'nullable|string|max:255',

            'letter_date' =>
                'required|date',

            'files' =>
                'required|array|min:1',

            'files.*' =>
            'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        DB::beginTransaction();

        try {

            $letter = Letter::create([

                'letter_number' =>
                    $request->letter_number,

                'subject' =>
                    $request->subject,

                'category' =>
                    $request->category,

                'sender_entity' =>
                    $request->sender_entity,

                'letter_date' =>
                    $request->letter_date,

                'created_by' =>
                    auth()->id(),
            ]);

            $attachments = [];

            if ($request->hasFile('files')) {

                foreach ($request->file('files') as $file) {

                    $extension =
                        $file->getClientOriginalExtension();

                    $mimeType =
                        $file->getMimeType();

                    $originalName =
                        $file->getClientOriginalName();

                $generatedName =
                    Str::uuid() . '.' .
                    $extension;

                    $path = $file->storeAs(
                        'letters',
                        $generatedName,
                        'public'
                    );

                    $attachment =
                        \App\Models\LetterAttachment::create([

                            'letter_id' =>
                                $letter->id,

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
                                $file->getSize(),

                            'uploaded_by' =>
                                auth()->id(),
                        ]);

                        $attachments[] = [

                            'id' =>
                                $attachment->id,

                            'file_name' =>
                                $attachment->file_name,

                            'file_type' =>
                                $attachment->file_type,

                            'file_size' =>
                                $attachment->file_size,

                            'view_url' =>
                                url(
                                    "/api/attachments/{$attachment->id}/view"
                                ),

                            'download_url' =>
                                url(
                                    "/api/attachments/{$attachment->id}/download"
                                ),
                        ];
                }
            }

            AuditLog::create([

                'user_id' =>
                    auth()->id(),

                'action' =>
                    'CREATE',

                'entity_type' =>
                    'letters',

                'entity_id' =>
                    $letter->id,

                'details' =>
                    json_encode([

                        'letter_number' =>
                            $letter->letter_number,

                        'files_count' =>
                            count($attachments),
                    ]),
            ]);

            DB::commit();

            return $this->successResponse(
                'Letter created successfully',
                [
                    'letter' => $letter,
                    'attachments' => $attachments
                ],
                201
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->errorResponse(
                'Error creating letter',
                500,
                $e->getMessage()
            );
        }
    }





        public function show($id)
        {
            $letter = Letter::with([
                'creator',
                'attachments',
                'replies.attachments',
                'replies.creator'
            ])->find($id);

            if (!$letter) {
                return $this->errorResponse(
                    'Letter not found',
                    404
                );
            }

            $attachments = $letter->attachments->map(function ($attachment) {

                return [

                    'id' =>
                        $attachment->id,

                    'file_name' =>
                        $attachment->file_name,

                    'file_type' =>
                        $attachment->file_type,

                    'file_size' =>
                        $attachment->file_size,

                    'view_url' =>
                        url("/api/attachments/{$attachment->id}/view"),

                    'download_url' =>
                        url("/api/attachments/{$attachment->id}/download"),
                ];
            });

        return $this->successResponse(
            'Letter fetched successfully',
            [
                'letter' => [
                    'id' => $letter->id,
                    'letter_number' => $letter->letter_number,
                    'subject' => $letter->subject,
                    'category' => $letter->category,
                    'sender_entity' => $letter->sender_entity,
                    'letter_date' => $letter->letter_date,
                    'creator' => $letter->creator,
                    'replies' => $letter->replies,
                ],
                'attachments' => $attachments
            ]
        );
        }




public function viewPdf($id)
{
    $letter = Letter::find($id);

    if (!$letter) {

        return $this->errorResponse(
            'Letter not found',
            404
        );
    }

    if (!Storage::exists($letter->pdf_path)) {

        return $this->errorResponse(
            'PDF not found',
            404
        );
    }

    return response()->file(
        storage_path(
            'app/public/' .
            $letter->pdf_path
        )
    );
}


public function downloadPdf($id)
{
    $letter = Letter::find($id);

    if (!$letter) {

        return $this->errorResponse(
            'Letter not found',
            404
        );
    }

    $path = storage_path(
        'app/public/' .
        $letter->pdf_path
    );

    if (!file_exists($path)) {

        return $this->errorResponse(
            'File not found',
            404
        );
    }

    return response()->download(
        $path,
        'letter_' .
        $letter->letter_number .
        '.pdf'
    );
}

    public function update( Request $request,  $id)
    {
        $request->validate([

            'letter_number' =>
                'nullable|unique:letters,letter_number,' . $id,

            'subject' =>
                'nullable|string|max:255',

            'category' =>
                'nullable|string|max:255',

            'sender_entity' =>
                'nullable|string|max:255',

            'letter_date' =>
                'nullable|date',

            'files' =>
                'nullable|array',

            'files.*' =>
                'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',

                    'deleted_attachments' =>
                        'nullable|array'
                ]);

        DB::beginTransaction();

        try {

            $letter =
                Letter::with(
                    'attachments'
                )->find($id);

                if (!$letter) {
                    return $this->errorResponse(
                        'Letter not found',
                        404
                    );
                }

            /**
             * update data
             */
            $updateData = [];

            if (
                $request->filled(
                    'letter_number'
                )
            ) {
                $updateData[
                    'letter_number'
                ] =
                    $request
                    ->letter_number;
            }

            if (
                $request->filled(
                    'subject'
                )
            ) {
                $updateData[
                    'subject'
                ] =
                    $request
                    ->subject;
            }

            if (
                $request->filled(
                    'category'
                )
            ) {
                $updateData[
                    'category'
                ] =
                    $request
                    ->category;
            }

            if (
                $request->filled(
                    'sender_entity'
                )
            ) {
                $updateData[
                    'sender_entity'
                ] =
                    $request
                    ->sender_entity;
            }

            if (
                $request->filled(
                    'letter_date'
                )
            ) {
                $updateData[
                    'letter_date'
                ] =
                    $request
                    ->letter_date;
            }

            if (
                !empty(
                    $updateData
                )
            ) {
                $letter->update(
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
                    LetterAttachment::whereIn(
                        'id',
                        $request
                        ->deleted_attachments
                    )->where(
                        'letter_id',
                        $letter->id
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
             * upload new files
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
                            'letters',
                            $generatedName,
                            'public'
                        );

                    LetterAttachment::create([

                        'letter_id' =>
                            $letter->id,

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
                    'UPDATE',

                'entity_type' =>
                    'letters',

                'entity_id' =>
                    $letter->id,

                'details' =>
                    json_encode([

                        'letter_number' =>
                            $letter
                            ->letter_number,

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

        return $this->successResponse(
            'Letter updated successfully',
            $letter->fresh()->load([
                'attachments',
                'creator'
            ])
        );

        } catch (
            \Exception $e
        ) {

            DB::rollBack();

            return $this->errorResponse(
                'Update failed',
                500,
                $e->getMessage()
            );
        }
    }

    public function destroy($id)
{
    DB::beginTransaction();

    try {

        $letter =
            Letter::with([
                'attachments',
                'replies.attachments'
            ])->find($id);

        if (!$letter) {

            return $this->errorResponse(
                'Letter not found',
                404
            );
        }

        /**
         * delete letter files
         */
        // foreach (
        //     $letter->attachments
        //     as $attachment
        // ) {

        //     Storage::disk(
        //         'public'
        //     )->delete(
        //         $attachment
        //         ->file_path
        //     );

        //     $attachment
        //         ->delete();
        // }

        /**
         * delete replies files
         */
        // foreach (
        //     $letter->replies
        //     as $reply
        // ) {

        //     foreach (
        //         $reply->attachments
        //         as $file
        //     ) {

        //         Storage::disk(
        //             'public'
        //         )->delete(
        //             $file
        //             ->file_path
        //         );

        //         $file
        //             ->delete();
        //     }
        // }

        /**
         * soft delete letter
         */
        $letter->delete();

        AuditLog::create([

            'user_id' =>
                auth()->id(),

            'action' =>
                'DELETE',

            'entity_type' =>
                'letters',

            'entity_id' =>
                $letter->id,

            'details' =>
                json_encode([

                    'letter_number' =>
                        $letter
                        ->letter_number
                ])
        ]);

        DB::commit();

        return $this->successResponse(
            'Letter deleted successfully'
        );

    } catch (\Exception $e) {

        DB::rollBack();

        return $this->errorResponse(
            'Delete failed',
            500,
            $e->getMessage()
        );
    }
    }

public function restore($id)
{
    DB::beginTransaction();

    try {

        $letter = Letter::withTrashed()->find($id);

        if (!$letter) {

            return $this->errorResponse(
                'Letter not found',
                404
            );
        }

        if (!$letter->trashed()) {

            return $this->errorResponse(
                'Letter is not deleted',
                400
            );
        }

        $letter->restore();

        AuditLog::create([

            'user_id' => auth()->id(),

            'action' => 'RESTORE_LETTER',

            'entity_type' => 'letters',

            'entity_id' => $letter->id,

            'details' => json_encode([

                'letter_number' => $letter->letter_number
            ])
        ]);

        DB::commit();

        return $this->successResponse(
            'Letter restored successfully'
        );

    } catch (\Exception $e) {

        DB::rollBack();

        return $this->errorResponse(
            'Restore failed',
            500,
            $e->getMessage()
        );
    }
}
}