<section class="w-full">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Notifications') }}
        </h2>
    </header>   

    <form id="send-verification"
        method="post"
        action="{{ route('verification.send') }}">
        @csrf
    </form>
    <form method="post"
        action="{{ route('settings.update', ['section' => 'notifications', 'userId' => $user->id]) }}"
        class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex justify-end mb-4">
            <button type="button" id="enable-all-btn"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:green-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Enable All
            </button>
            <x-danger-button type="button" id="disable-all-btn"
                class="ml-2">
                Disable All
            </x-danger-button>
        </div>

        <div>
            <div class="flex flex-row justify-between" >
                <x-input-label for="new_bid_followed_auctions"
                    :value="__('New Bid in Followed Auctions')" class=" !text-base"/>
                <input type="hidden" name="following_auction_new_bid_notifications" value="0">
                <input type="checkbox" id="new_bid_followed_auctions" name="following_auction_new_bid_notifications" value="1"
                class="notification-checkbox appearance-none h-6 w-11 rounded-full bg-gray-200 checked:bg-blue-500  relative transition cursor-pointer"
                {{ $user->user->following_auction_new_bid_notifications ? 'checked' : '' }}>
            </div>

            <hr class="border-t border-gray-300 mt-2">

            <div class="flex flex-row justify-between mt-2">
                <x-input-label for="new_bid_selling_auctions"
                    :value="__('New Bid in Selling Auctions')" class=" !text-base"/>
                <input type="hidden" name="selling_auction_new_bid_notifications" value="0">
                <input type="checkbox" id="new_bid_selling_auctions" name="selling_auction_new_bid_notifications" value="1"
                class="notification-checkbox appearance-none h-6 w-11 rounded-full bg-gray-200 checked:bg-blue-500  relative transition cursor-pointer"
                {{ $user->user->selling_auction_new_bid_notifications ? 'checked' : '' }}>
            </div>

            <hr class="border-t border-gray-300 mt-2">
            
            <div class="flex flex-row justify-between mt-2" >
                <x-input-label for="new_message_notification"
                    :value="__('New Message')" class=" !text-base"/>
                <input type="hidden" name="new_message_notifications" value="0">
                <input type="checkbox" id="new_message_notification" name="new_message_notifications" value="1"
                class="notification-checkbox appearance-none h-6 w-11 rounded-full bg-gray-200 checked:bg-blue-500  relative transition cursor-pointer"
                {{ $user->user->new_message_notifications ? 'checked' : '' }}>
            </div>

            <hr class="border-t border-gray-300 mt-2">

            <div class="flex flex-row justify-between mt-2">
                <x-input-label for="new_auction_notification"
                    :value="__('New Auction')" class=" !text-base"/>
                <input type="hidden" name="new_auction_notifications" value="0">
                <input type="checkbox" id="new_auction_notification" name="new_auction_notifications" value="1"
                class="notification-checkbox appearance-none h-6 w-11 rounded-full bg-gray-200 checked:bg-blue-500  relative transition cursor-pointer"
                {{ $user->user->new_auction_notifications ? 'checked' : '' }}>
            </div>

            <hr class="border-t border-gray-300 mt-2">

            <div class="flex flex-row justify-between mt-2" >
                <x-input-label for="closed_following_auction"
                    :value="__('Following Auction Closed')" class=" !text-base"/>
                <input type="hidden" name="following_auction_closed_notifications" value="0">
                <input type="checkbox" id="closed_following_auction" name="following_auction_closed_notifications" value="1"
                class="notification-checkbox appearance-none h-6 w-11 rounded-full bg-gray-200 checked:bg-blue-500  relative transition cursor-pointer"
                {{ $user->user->following_auction_closed_notifications ? 'checked' : '' }}>
            </div>

            <hr class="border-t border-gray-300 mt-2">
            
            <div class="flex flex-row justify-between mt-2" >
                <x-input-label for="canceled_following_auction"
                    :value="__('Following Auction Canceled')" class=" !text-base"/>
                <input type="hidden" name="following_auction_canceled_notifications" value="0">
                <input type="checkbox" id="canceled_following_auction" name="following_auction_canceled_notifications" value="1"
                class="notification-checkbox appearance-none h-6 w-11 rounded-full bg-gray-200 checked:bg-blue-500  relative transition cursor-pointer"
                {{ $user->user->following_auction_canceled_notifications ? 'checked' : '' }}>
            </div>

            <hr class="border-t border-gray-300 mt-2">
            
            <div class="flex flex-row justify-between mt-2" >
                <x-input-label for="ending_following_auction"
                    :value="__('Following Auction Ending')" class=" !text-base"/>
                <input type="hidden" name="following_auction_ending_notifications" value="0">
                <input type="checkbox" id="ending_following_auction" name="following_auction_ending_notifications" value="1"
                class="notification-checkbox appearance-none h-6 w-11 rounded-full bg-gray-200 checked:bg-blue-500  relative transition cursor-pointer"
                {{ $user->user->following_auction_ending_notifications ? 'checked' : '' }}>
            </div>

            <hr class="border-t border-gray-300 mt-2">
            
            <div class="flex flex-row justify-between mt-2" >
                <x-input-label for="ending_selling_auction"
                    :value="__('Selling Auction Ending')" class=" !text-base"/>
                <input type="hidden" name="seller_auction_ending_notifications" value="0">
                <input type="checkbox" id="ending_selling_auction" name="seller_auction_ending_notifications" value="1"
                class="notification-checkbox appearance-none h-6 w-11 rounded-full bg-gray-200 checked:bg-blue-500  relative transition cursor-pointer"
                {{ $user->user->seller_auction_ending_notifications ? 'checked' : '' }}>
            </div>

            <hr class="border-t border-gray-300 mt-2">
            
            <div class="flex flex-row justify-between mt-2" >
                <x-input-label for="ending_bidder_auction"
                    :value="__('Bidder Auction Ending')" class=" !text-base"/>
                <input type="hidden" name="bidder_auction_ending_notifications" value="0">
                <input type="checkbox" id="ending_bidder_auction" name="bidder_auction_ending_notifications" value="1"
                class="notification-checkbox appearance-none h-6 w-11 rounded-full bg-gray-200 checked:bg-blue-500  relative transition cursor-pointer"
                {{ $user->user->bidder_auction_ending_notifications ? 'checked' : '' }}>
            </div>

            <hr class="border-t border-gray-300 mt-2">

            <div class="flex flex-row justify-between mt-2" >
                <x-input-label for="rating"
                    :value="__('Rating')" class=" !text-base"/>
                <input type="hidden" name="rating_notifications" value="0">
                <input type="checkbox" id="rating" name="rating_notifications" value="1"
                class="notification-checkbox appearance-none h-6 w-11 rounded-full bg-gray-200 checked:bg-blue-500  relative transition cursor-pointer"
                {{ $user->user->rating_notifications ? 'checked' : '' }}>
            </div>

            <div class="flex items-center gap-4 mt-6">
                <x-primary-button>{{ __('Save') }}</x-primary-button>

                @if (session('status') === 'settings-updated')
                    <p x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600">{{ __('Saved.') }}</p>
                @endif
            </div>
        </div>
    </form>
    
    
</section>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const enableAllButton = document.getElementById("enable-all-btn");
            const disableAllButton = document.getElementById("disable-all-btn");
            const checkboxes = document.querySelectorAll(".notification-checkbox");

            // Function to enable all checkboxes
            function enableAll() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
            }

            // Function to disable all checkboxes
            function disableAll() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
            }

            // Add event listeners to the buttons
            enableAllButton.addEventListener("click", enableAll);
            disableAllButton.addEventListener("click", disableAll);
        });
    </script>
@endpush
