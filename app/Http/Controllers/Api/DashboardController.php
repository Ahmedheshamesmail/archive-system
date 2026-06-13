<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Reply;
use App\Models\Letter;
use App\Http\Controllers\Controller;

class DashboardController
extends Controller
{
    public function index()
    {
        $today =
            Carbon::today();

        $startOfMonth =
            Carbon::now()
            ->startOfMonth();

        $endOfMonth =
            Carbon::now()
            ->endOfMonth();

        $totalLetters =
            Letter::count();

        $totalReplies =
            Reply::count();

        $todayLetters =
            Letter::whereDate(
                'created_at',
                $today
            )->count();

        $todayReplies =
            Reply::whereDate(
                'created_at',
                $today
            )->count();

        $monthlyLetters =
            Letter::whereBetween(
                'created_at',
                [
                    $startOfMonth,
                    $endOfMonth
                ]
            )->count();

        $monthlyReplies =
            Reply::whereBetween(
                'created_at',
                [
                    $startOfMonth,
                    $endOfMonth
                ]
            )->count();

        $lettersWithoutReplies =
            Letter::doesntHave(
                'replies'
            )->count();

        $latestLetters =
            Letter::with([
                'creator',
                'attachments',
                'replies'
            ])
            ->latest()
            ->take(10)
            ->get();

        return response()->json([

            'stats' => [

                'total_letters' =>
                    $totalLetters,

                'total_replies' =>
                    $totalReplies,

                'today_letters' =>
                    $todayLetters,

                'today_replies' =>
                    $todayReplies,

                'monthly_letters' =>
                    $monthlyLetters,

                'monthly_replies' =>
                    $monthlyReplies,

                'letters_without_replies' =>
                    $lettersWithoutReplies
            ],

            'latest_letters' =>
                $latestLetters

        ],200);
    }
}
