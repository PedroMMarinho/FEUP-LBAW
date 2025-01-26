<?php

namespace App\Http\Controllers;

use App\Models\GeneralUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term from the request
        $search = $request->input('search');

        // Use the scopeSearch method from the GeneralUser model
        $users = GeneralUser::search($search)
            ->with(['image', 'user', 'user.followers'])
            ->paginate(12);

        $users->getCollection()->map(function ($user) {
            if (Auth::check()) {
                // If the user is authenticated, check if the user has a 'user' relation and determine 'following'
                $user->following = $user->user ? $user->user->followers->contains('id', Auth::id()) : false;
            } else {
                // If the user is not authenticated, set 'following' to false
                $user->following = false;
            }

            return $user;
        });
        // Get the total number of users
        $totalUsers = $users->total();

        // Return the view with the data
        return view('users', compact('users', 'totalUsers', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
