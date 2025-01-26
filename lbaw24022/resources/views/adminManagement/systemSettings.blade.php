@extends('adminManagement.management')

@section('slot')
    <div class=" max-w-2xl w-full">
        <section>
            <header>
                <h2 class="text-lg font-medium text-gray-900">
                    {{ 'System Settings' }}
                </h2>
            </header>
        
            <form id="send-verification"
                method="post"
                action="{{ route('verification.send') }}">
                @csrf
            </form>
        
            <form method="post"
                action="{{ route('management.systemSettings') }}"
                class="mt-6 space-y-6">
                @csrf
    
                <div>
                    <x-input-label for="bidInterval"
                        :value="__('Auction Bid Interval')" />
                    <x-text-input id="bidInterval"
                        name="bidInterval"
                        type="number"
                        class="mt-1 block w-full"
                        :value="old('bidInterval', $config->minimal_bid_interval)"
                        required
                        autofocus />
                    <x-input-error class="mt-2"
                        :messages="$errors->get('bidInterval')" />
                </div>
        
                <div>
                    <x-input-label for="subDay"
                        :value="__('Subscription Price Day')" />
                    <x-text-input id="subDay"
                        name="subDay"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                        :value="old('subDay', $config->subscribe_price_plan_a)"
                        required
                        autofocus />
                    <x-input-error class="mt-2"
                        :messages="$errors->get('subDay')" />
                </div>   
                
                <div>
                    <x-input-label for="subMonth"
                        :value="__('Subscription Price Month (Daily)')" />
                    <x-text-input id="subMonth"
                        name="subMonth"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                        :value="old('subMonth', $config->subscribe_price_plan_b)"
                        required
                        autofocus />
                    <x-input-error class="mt-2"
                        :messages="$errors->get('subMonth')" />
                </div>

                <div>
                    <x-input-label for="sub6Months"
                        :value="__('Subscription Price 6 Months (Daily)')" />
                    <x-text-input id="sub6Months"
                        name="sub6Months"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                        :value="old('sub6Months', $config->subscribe_price_plan_c)"
                        required
                        autofocus />
                    <x-input-error class="mt-2"
                        :messages="$errors->get('sub6Months')" />
                </div>

                <div>
                    <x-input-label for="adPrice"
                        :value="__('Advertisement Price')" />
                    <x-text-input id="adPrice"
                        name="adPrice"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                        :value="old('adPrice', $config->ad_price)"
                        required
                        autofocus />
                    <x-input-error class="mt-2"
                        :messages="$errors->get('adPrice')" />
                </div>

                <div>
                    <x-input-label for="adDiscountPrice"
                        :value="__('Advertisement Discounted Price')" />
                    <x-text-input id="adDiscountPrice"
                        name="adDiscountPrice"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                        :value="old('adDiscountPrice', $config->discounted_ad_price)"
                        required
                        autofocus />
                    <x-input-error class="mt-2"
                        :messages="$errors->get('adDiscountPrice')" />
                </div>
        
                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
        
                    @if (session('status') === 'systemSettings-updated')
                        <p x-data="{ show: true }"
                            x-show="show"
                            x-transition
                            x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm text-gray-600">{{ __('Saved.') }}</p>
                    @endif
                </div>
            </form>
        </section>                
    </div>
@endsection