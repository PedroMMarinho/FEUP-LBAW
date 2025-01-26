<?php

namespace App\Policies;

use App\Models\GeneralUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GeneralUserPolicy
{

    public function transferMoney(GeneralUser $authUser) : bool
    {
        return $authUser->role === 'Regular User' && !$authUser->user->blocked;
    }


    public function report(GeneralUser $authUser, GeneralUser $profileUser): bool
    {
        $prohibitedUsers = [1,2,3];

        if(in_array($profileUser->id, $prohibitedUsers)) return false;
        
        if($authUser->role === 'Regular User' && $profileUser->role === 'Regular User' && $authUser->id !== $profileUser->id && !$authUser->user->blocked)
        {
            return true;
        }

        return false;
    }

    public function follow(GeneralUser $authUser, GeneralUser $profileUser): bool
    {
        $prohibitedUsers = [1,2,3];

        if(in_array($profileUser->id, $prohibitedUsers)) return false;
        
        if($authUser->role === 'Regular User' && $profileUser->role === 'Regular User' && $authUser->id !== $profileUser->id)
        {
            return true;
        }

        return false;
    }

    public function rate(GeneralUser $authUser, GeneralUser $profileUser): bool
    {
        $prohibitedUsers = [1,2,3];

        if(in_array($profileUser->id, $prohibitedUsers)) return false;
        
        if($authUser->role === 'Regular User' && $profileUser->role === 'Regular User' && $authUser->id !== $profileUser->id && !$authUser->user->blocked)
        {
            return true;
        }

        return false;
    }

    public function viewSettings(GeneralUser $authUser, GeneralUser $settingsUser, string $section): bool
    {
        $prohibitedUsers = [1,2,3];
        $allowedSection = ['profile'];


        if(in_array($settingsUser->id, $prohibitedUsers)) return false;

        if($authUser->id === $settingsUser->id)
        {
            $allowedSection[] = 'account';

            if($authUser->role === 'Regular User')
            {           
                $allowedSection[] = 'notifications';
            }

            return in_array($section, $allowedSection);
        }
        elseif ($authUser->role === 'Admin')
        {
            if($settingsUser->role === 'Regular User')
            {
                $allowedSection[] = 'block';
                $allowedSection[] = 'reports';
            }

            return in_array($section, $allowedSection);

        }
        return false;
    }

    public function delete(GeneralUser $authUser, GeneralUser $userToDelete): bool
    {
        $prohibitedUsers = [1,2,3];

        if(in_array($userToDelete->id, $prohibitedUsers)) return false;

        if($authUser->id === $userToDelete->id)
        {


            if($userToDelete->role === 'Regular User')
            {

                if($userToDelete->user->wallet != 0) return false;

                if($userToDelete->user->hasActiveAuctions())return false;

                if($userToDelete->user->hasFinishedAuctions())return false;

            }

            return true;
        }
        elseif ($authUser->role === 'Admin')
        {
            return true;
        }

        return false;
    }


}
