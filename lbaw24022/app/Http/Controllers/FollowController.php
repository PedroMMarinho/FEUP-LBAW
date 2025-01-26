<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FollowController extends Controller
{
    public function followUser(int $id)
    {
        $authUser = Auth::user()->user; // The authenticated user

        if ($authUser->id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow yourself',
            ], 400);
        }
        
        // Check if the user is already following
        $isFollowing = $authUser->following($id);

        if ($isFollowing) {
            // Unfollow the user
            $authUser->followingUsers()->detach($id);
        } else {
            // Follow the user
            $authUser->followingUsers()->attach($id);
        }

        return response()->json([
            'success' => true,
            'isFollowing' => !$isFollowing, // Return the updated status
        ]);
    }

    public function followAuction(int $id)
    {
        $auction = Auction::findOrFail($id);
        $user = Auth::user();

        if ($user->id === $auction->seller->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow your own auction.',
            ], 400);
        }
        
        // Check if the user is already following
        $isFollowing = $auction->isFollowedBy($user->id);

        if ($isFollowing) {
            // Unfollow the user
            $auction->followers()->detach($user->id);
        } else {
            // Follow the user
            $auction->followers()->attach($user->id);
        }

        return response()->json([
            'success' => true,
            'isFollowing' => !$isFollowing, // Return the updated status
        ]);
    }

}

