<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingsUpdateRequest;
use App\Models\GeneralUser;
use App\Models\User;
use App\Models\Block;
use App\Models\AdminChange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;


class SettingsController extends Controller
{

    public function show(Request $request, string $section, int $userId): View | RedirectResponse
    {
        $user = GeneralUser::findOrFail($userId);
        
        if (Gate::denies('viewSettings', [$user, $section])) {
            return back()->with('error', 'Cannot access this user settings');
        }

        if($section === 'block')
        {
            $currentBlock = $user->user->currentBlock();

            $blocks = $user->user->blocks()
                ->with('blockAdmin.generalUser', 'appealAdmin.generalUser')
                ->when($currentBlock, function ($query) use ($currentBlock) {
                    return $query->where('id', '!=', $currentBlock->id);
                })
                ->latest('end_time')
                ->get();
            

            return view("settings.$section", compact('user', 'currentBlock', 'blocks'));
        }

        if($section === 'reports'){
            $reports = $user->user->userReportsAsReported()->with('reporterUser.generalUser')->paginate(5);

            return view("settings.$section", compact('user', 'reports'));

        }   

        return view("settings.$section", [  
            'user' => $user,
        ]);
    }

    public function update(SettingsUpdateRequest $request, string $section, int $userId)
    {   
        $userToChange = GeneralUser::findOrFail($userId);

        if (($userToChange->id !== $request->user()->id) && ($request->user()->role !== 'Admin')) {
            return redirect('/')->with('error', 'Unauthorized Action');
        }

        if (($section !== 'profile') && ($userToChange->id !== $request->user()->id)) {
            return redirect('/')->with('error', 'Unauthorized Action');;
        }

        $validatedData = $request->validated(); 

        if ($section === 'profile') {

            $userToChange->username = $validatedData['username']; 
            $userToChange->description = $validatedData['description'];
            $userToChange->save();

            if($request->user()->id !== $userToChange->id && $request->user()->role === 'Admin')
            {
                AdminChange::create([
                    'description' => 'Updated profile details of ' . $userToChange->role . ' ' . $userToChange->username,
                    'admin' => $request->user()->id
                ]);
            }
        
        } elseif ($section === 'account') {
            $userToChange->update([
                'email' => $validatedData['email'],
            ]);

        } elseif ($section === 'notifications'){
            $userToChange->user->update($validatedData);
        }

        return back()->with('status', 'settings-updated')->with('success', 'Settings successfully updated');

    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, int $userId): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $generalUser = $request->user();

        if ($generalUser->role === 'Admin' && $generalUser->id !== $userId) {
            $request->validateWithBag('userDeletion', [
                'motive' => ['required', 'string', 'max:100'],
            ]);
        }

        $userToDelete = GeneralUser::findOrFail($userId);


        if (Gate::denies('delete', $userToDelete)) {
            if($generalUser->role === 'Regular User')
            {
                return back()->with('error', 'Cannot delete your account');
            }
            return back()->with('error', 'Cannot delete this account');

        }

        if($generalUser->id === $userToDelete->id)
        {
            Auth::logout();
        }
        else
        {
            AdminChange::create([
                'description' => 'Deleted account of ' . $userToDelete->role . ' ' . $userToDelete->username . "\nMotive: " . $request->input('motive'),
                'admin' => $generalUser->id
            ]);
        }


        ImageController::destroyImage($userToDelete->id);

        $userToDelete->delete();


        if($generalUser->id === $userToDelete->id)
        {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        return Redirect::to('/')->with('success', 'Account Successfully Deleted');
    }

    public function block(Request $request, int $userId): RedirectResponse
    {   

        $blockUser = GeneralUser::findOrFail($userId);

        if($request->user()->role !== 'Admin' || $blockUser->role !== 'Regular User')
        {   
            return Redirect::to('/')->with('error', 'Cannot block this user');  
        }

        
        $validationRules = [
            'motive' => ['required', 'string', 'max:100'],
            'block_type' => ['required']
        ];
    
        if ($request->input('block_type') === 'periodic') 
        {
            $validationRules['end_time'] = ['required', 'date', 'after_or_equal:today'];
        }

        $request->validateWithBag('userBlock', $validationRules);

        $blockType = $request->input('block_type');

        $blockMessage = $request->input('motive');

        if($blockType === 'permanent'){
            Block::create([
                'block_message' => $blockMessage,
                'block_admin' => $request->user()->id,
                'blocked_user' => $blockUser->id,
            ]);
        } 
        elseif ($blockType === 'periodic')
        {
            $blockTime = $request->input('end_time');

            $blockTime = \Carbon\Carbon::parse($blockTime)->endOfDay();

            Block::create([
                'end_time' => $blockTime,
                'block_message' => $blockMessage,
                'block_admin' => $request->user()->id,
                'blocked_user' => $blockUser->id,
            ]);
        }

        AdminChange::create([
            'description' => 'Blocked ' . $blockUser->role . ' ' . $blockUser->username . "\nMotive: " . $blockMessage,
            'admin' => $request->user()->id
        ]);

        return Redirect::to("/settings/block/{$userId}")->with('success', 'User successfully Blocked');
    }


    public function unblock(Request $request, int $userId): RedirectResponse
    {   

        $unblockUser = GeneralUser::findOrFail($userId);

        if($request->user()->role !== 'Admin' || $unblockUser->role !== 'Regular User')
        {   
            return Redirect::to('/')->with('error', 'Cannot unblock this user');  
        }

        $associatedBlock = Block::where('blocked_user', $unblockUser->id)
        ->where('end_time', '>', \Carbon\Carbon::now()) 
        ->latest('end_time')
        ->first();

        if (!$associatedBlock) {
            return Redirect::to('/')
                ->with('error', 'No active block found for this user');
        }
        
        $associatedBlock->appeal_accepted = true;
        $associatedBlock->appeal_admin = $request->user()->id;

        $associatedBlock->save();

        AdminChange::create([
            'description' => 'Unblocked ' . $unblockUser->role . ' ' . $unblockUser->username,
            'admin' => $request->user()->id
        ]);

        usleep(700000);

        return Redirect::to("/settings/block/{$userId}")->with('success', 'User successfully unblocked');;
    }

    public function rejectAppeal(Request $request, int $userId): RedirectResponse
    {   

        $user = GeneralUser::findOrFail($userId);

        if($request->user()->role !== 'Admin' || $user->role !== 'Regular User')
        {   
            return Redirect::to('/')->with('error', 'Cannot handle this user appeals');  
        }

        $associatedBlock = Block::where('blocked_user', $user->id)
        ->where('end_time', '>', \Carbon\Carbon::now()) 
        ->latest('end_time')
        ->first();

        if (!$associatedBlock) {
            return Redirect::to('/')
                ->with('error', 'No active block found for this user');
        }
        
        $associatedBlock->appeal_accepted = false;
        $associatedBlock->appeal_admin = $request->user()->id;

        $associatedBlock->save();

        AdminChange::create([
            'description' => 'Rejected appeal of ' . $user->role . ' ' . $user->username,
            'admin' => $request->user()->id
        ]);

        return Redirect::to("/settings/block/{$userId}")->with('success', 'User appeal successfully rejected');;
    }

    
}

