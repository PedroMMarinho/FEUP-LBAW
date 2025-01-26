<?php

namespace App\Http\Requests;

use App\Models\GeneralUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $section = $this->route('section'); 

        switch ($section) {
            case 'profile':
                return [
                    'username' => [
                        'required',
                        'string',
                        'max:255',
                        'unique:general_user,username,' . $this->route('userId'),
                    ],
                    'description' => [
                        'nullable',
                        'string',
                        'max:700',
                    ],
                ];

            case 'account':
                return [
                    'email' => [
                        'required',
                        'string',
                        'lowercase',
                        'email',
                        'max:255',
                        Rule::unique(GeneralUser::class)->ignore($this->user()->id),
                    ],
                ];

            case 'notifications':
                return[
                    'following_auction_new_bid_notifications' => ['boolean'],
                    'selling_auction_new_bid_notifications' => ['boolean'],
                    'new_message_notifications' => ['boolean'],
                    'new_auction_notifications' => ['boolean'],
                    'following_auction_closed_notifications' => ['boolean'],
                    'following_auction_canceled_notifications' => ['boolean'],
                    'following_auction_ending_notifications' => ['boolean'],
                    'seller_auction_ending_notifications' => ['boolean'],
                    'bidder_auction_ending_notifications' => ['boolean'],
                    'rating_notifications' => ['boolean'],
                ];

            default:
                return [];
        }
    }
}
