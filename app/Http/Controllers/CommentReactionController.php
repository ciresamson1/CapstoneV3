<?php

namespace App\Http\Controllers;

use App\Models\TaskComment;
use App\Models\CommentReaction;
use Illuminate\Http\Request;

class CommentReactionController extends Controller
{
    /**
     * Toggle a thumbs-up or thumbs-down reaction.
     * - Clicking the same type again removes the reaction.
     * - Clicking the opposite type switches it.
     */
    public function toggle(Request $request, TaskComment $comment)
    {
        $request->validate(['type' => 'required|in:up,down']);

        $userId = auth()->id();
        $type   = $request->type;

        $existing = CommentReaction::where('comment_id', $comment->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($existing->type === $type) {
                // Same button → remove reaction
                $existing->delete();
            } else {
                // Opposite button → switch
                $existing->update(['type' => $type]);
            }
        } else {
            CommentReaction::create([
                'comment_id' => $comment->id,
                'user_id'    => $userId,
                'type'       => $type,
            ]);
        }

        $ups   = CommentReaction::where('comment_id', $comment->id)->where('type', 'up')->count();
        $downs = CommentReaction::where('comment_id', $comment->id)->where('type', 'down')->count();
        $mine  = CommentReaction::where('comment_id', $comment->id)->where('user_id', $userId)->value('type');

        return response()->json([
            'ups'   => $ups,
            'downs' => $downs,
            'mine'  => $mine,
        ]);
    }
}
