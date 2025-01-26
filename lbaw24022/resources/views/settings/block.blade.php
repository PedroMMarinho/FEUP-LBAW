@extends('settings.settings')

@section('slot')
    <div class="w-full">
        <section>
            <form id="send-verification"
                method="post"
                action="{{ route('verification.send') }}">
                @csrf
            </form>
            <h2 class="text-lg font-medium text-gray-900 mb-6">
                {{ 'Block Status' }}
            </h2>
            <div class="grid grid-cols-3">
                @if ($user->user->blocked)
                    <x-danger-button 
                    class="justify-center h-10 self-center justify-self-center w-full col-span-full sm:col-start-1 sm:col-end-2"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-unblock')">
                        {{__('Unblock')}}
                    </x-danger-button>
                    <div class="text-center self-center col-span-full mt-5 sm:mt-0 sm:col-start-2 sm:col-end-4">
                        Block End: {{$currentBlock->end_time == '2999-12-31 23:59:59' ? 'Permanently' : $currentBlock->end_time->format('d/m/Y')}}
                    </div>
                    <h2 class="font-medium text-gray-900 self-center col-span-full mt-6">
                        {{ 'Current Block' }}
                    </h2>
                    <p class="text-gray-600 col-span-full mt-4 pl-4">
                        {{ __('Motive: ' . $currentBlock->block_message) }}
                    </p>
                    <p class="text-gray-600 col-span-full mt-4 pl-4">
                        {{ __('Admin: ' . $currentBlock->blockAdmin->generalUser->username) }}
                    </p>
                @else
                    <x-danger-button 
                    class="justify-center h-10 self-center justify-self-center w-full col-span-full sm:col-start-1 sm:col-end-2"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-block')">
                        {{__('Block')}}
                    </x-danger-button>
                @endif
                <div class="col-span-full" x-data="{ dropdownOpen: false }">
                    <h2 class="font-medium text-gray-900 self-center col-span-full mt-6 select-none">
                        {{ 'Past Blocks (' . $blocks->count() . ')' }}
    
                        @if ($blocks->count() > 0)
                            <span class="ml-2 cursor-pointer" @click="dropdownOpen = !dropdownOpen">                            
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block transform transition-transform" :class="{'rotate-180': dropdownOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </span>
                        @endif
                    </h2> 
                    <div x-show="dropdownOpen" class="overflow-y-auto max-h-[24rem]" >
                        @foreach ($blocks as $block)
                            <div class="block mb-4">
                                <h2 class="font-medium text-gray-900 self-center col-span-full mt-6">
                                    {{ 'Block (' . \Carbon\Carbon::parse($block->start_time)->format('d/m/Y') . ')' }}
                                </h2>
                                <p class="text-gray-600 col-span-full mt-4 pl-4">
                                    {{ __('Motive: ' . $block->block_message) }}
                                </p>
                                <p class="text-gray-600 col-span-full mt-4 pl-4">
                                    {{ __('Block Admin: ' . $block->blockAdmin->generalUser->username) }}
                                </p>
                                @if ($block->appeal_message && $block->appeal_accepted == true)
                                    <p class="text-gray-600 col-span-full mt-4 pl-4">
                                        {{ __('Appeal: ' . $block->appeal_message) }}
                                    </p>
                                @endif
                                <p class="text-gray-600 col-span-full mt-4 pl-4">
                                    {{ __('Appeal Admin: ' . $block->appealAdmin->generalUser->username) }}
                                </p>
                                <p class="text-gray-600 col-span-full mt-4 pl-4">
                                    @php
                                        $start = \Carbon\Carbon::parse($block->start_time);
                                        $end = \Carbon\Carbon::parse($block->end_time);
                                        $diff = $start->diff($end);
                                    @endphp

                                    {{ __('Block Duration: ') }}
                                    @if($diff->y > 0) {{ $diff->y }} {{ trans_choice('year|years', $diff->y)}}@endif
                                    @if($diff->m > 0) {{ $diff->m }} {{ trans_choice('month|months', $diff->m)}}@endif
                                    @if($diff->d > 0) {{ $diff->d }} {{ trans_choice('day|days', $diff->d)}}@endif
                                    @if($diff->h > 0) {{ $diff->h }} {{ trans_choice('hour|hours', $diff->h)}}@endif
                                    @if($diff->m > 0) {{ $diff->m }} {{ trans_choice('minute|minutes', $diff->m)}}@endif
                                    @if($diff->s > 0) {{ $diff->s }} {{ trans_choice('second|seconds', $diff->s)}}@endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                
                <x-modal name="confirm-user-unblock" focusable>
                    <form method="post" action="{{ route('user.unblock', $user->id) }}" class="p-6">
                        @csrf
                        @method('post')
            
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Are you sure you want to unblock this account?') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('This action will unblock the user and clear all their reports up to this moment.') }}
                        </p>
            
                        <div class="mt-6 flex justify-end">
                            <x-secondary-button x-on:click="$dispatch('close')">
                                {{ __('Cancel') }}
                            </x-secondary-button>
            
                            <x-danger-button class="ms-3">
                                {{ __('Unblock') }}
                            </x-danger-button>
                        </div>
                    </form>
                </x-modal>
        
                <x-modal name="confirm-user-block" :show="$errors->userBlock->isNotEmpty()" focusable>
                    <form id="block-form" method="post" action="{{ route('user.block', $user->id) }}" class="p-6">
                        @csrf
                        @method('post')
            
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Are you sure you want to block this account?') }}
                        </h2>
        
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Please provide the reason for blocking this account and the respective end time.') }}
                        </p>
            
                        <div class="mt-6">
                            <x-input-label for="motive" value="{{ __('Motive') }}" class="sr-only" />
            
                            <x-text-input
                                id="motive"
                                name="motive"
                                type="text"
                                class="mt-1 block w-3/4"
                                placeholder="{{ __('Motive') }}"
                                value="{{ old('motive') }}"
                            />
            
                            <x-input-error :messages="$errors->userBlock->get('motive')" class="mt-2" />
                        </div>
        
                        <div>
                            <x-input-label for="block_type" value="{{ __('Block Type') }}" class="sr-only" />
        
                            <div class="flex items-center">
                                <input type="radio" id="permanent" name="block_type" value="permanent" class="mr-2" 
                                {{ old('block_type') === 'permanent' ? 'checked' : '' }}/>
                                <label for="permanent">{{ __('Permanent Block') }}</label>
                            </div>
        
                            <div class="flex items-center mt-2">
                                <input type="radio" id="periodic" name="block_type" value="periodic" class="mr-2" 
                                {{ old('block_type') === 'periodic' ? 'checked' : '' }}/>
                                <label for="periodic">{{ __('Periodic Block') }}</label>
                            </div>
        
                        </div>
        
                        <div id="end_time_section" class="mt-6 hidden">
                            <x-input-label for="end_time" value="{{ __('End Time') }}" class="sr-only" />
        
                            <x-text-input
                                id="end_time"
                                name="end_time"
                                type="date"
                                class="mt-1 block w-3/4"
                                min="{{ \Carbon\Carbon::now()->toDateString() }}"
                                placeholder="{{ __('End Time') }}"
                                value="{{ old('end_time') }}"
                            />
        
                            <x-input-error :messages="$errors->userBlock->get('end_time')" class="mt-2" />
        
                        </div>
                        <x-input-error :messages="$errors->userBlock->get('block_type')" class="mt-2" />
            
                        <div class="mt-6 flex justify-end">
                            <x-secondary-button x-on:click="$dispatch('close'); resetForm()">
                                {{ __('Cancel') }}
                            </x-secondary-button>
            
                            <x-danger-button class="ms-3">
                                {{ __('Block') }}
                            </x-danger-button>
                        </div>
                    </form>
                </x-modal>
            </div>
            @if ($user->user->blocked)
                <hr class="mt-6 border-t border-gray-300">
                <h2 class="text-lg font-medium text-gray-900 mt-6 mb-2">
                    {{ 'Appeal Status' }}
                </h2>
                <div class="grid grid-cols-4 grid-row-2 sm:grid-row-1 gap-y-4 sm:h-16">
                    @if($currentBlock->appeal_message && is_null($currentBlock->appeal_accepted))
                        <div class="col-span-full sm:col-start-1 sm:col-end-3 row-start-1 row-end-2 flex items-center justify-center p-2">{{$currentBlock->appeal_message}}</div>
                        <x-secondary-button class="col-start-1 col-end-3 sm:col-start-3 sm:col-end-4 row-start-2 row-end-3 sm:row-start-1 justify-center h-10 self-center justify-self-center mr-2 w-3/4 "
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-accept-appeal')">
                                {{ __('Accept Appeal') }}
                        </x-secondary-button>
                        <x-primary-button class="col-start-3 col-end-5 sm:col-start-4 sm:col-end-5 row-start-2 row-end-3 sm:row-start-1 justify-center h-10 self-center justify-self-center w-3/4"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-reject-appeal')">
                                {{ __('Reject Appeal') }}
                        </x-primary-button>
                    @elseif ($currentBlock->appeal_message && ($currentBlock->appeal_accepted == false))
                        <div class="col-start-1 col-end-2 flex items-center justify-center">{{$currentBlock->appeal_message}}</div>
                        <div class="col-start-2 col-end-4 text-gray-600 flex justify-center items-center">Appeal Rejected by {{$currentBlock->appealAdmin->generalUser->username}}</div>
                    @else
                        <div class="col-span-full text-gray-600 flex justify-center items-center">No appeal</div>

                    @endif
                    <x-modal name="confirm-accept-appeal" focusable>
                        <form method="post" action="{{ route('user.unblock', $user->id) }}" class="p-6">
                            @csrf
                            @method('post')
                
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Are you sure you want to accept the user appeal to unblock?') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('This action will unblock the user and clear all their reports up to this moment.') }}
                            </p>

                            <div class="mt-6 flex justify-end">
                                <x-secondary-button x-on:click="$dispatch('close')">
                                    {{ __('Cancel') }}
                                </x-secondary-button>
                
                                <x-primary-button class="ms-3">
                                    {{ __('Accept') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </x-modal>
                    <x-modal name="confirm-reject-appeal" focusable>
                        <form method="post" action="{{ route('user.rejectAppeal', $user->id) }}" class="p-6">
                            @csrf
                            @method('post')
                
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Are you sure you want to reject the user appeal to unblock?') }}
                            </h2>
                
                            <div class="mt-6 flex justify-end">
                                <x-secondary-button x-on:click="$dispatch('close')">
                                    {{ __('Cancel') }}
                                </x-secondary-button>
                
                                <x-primary-button class="ms-3">
                                    {{ __('Reject') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </x-modal>
                </div>
            @endif
        </section>   
    </div>

@endsection

            
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        function resetForm() {
            const form = document.getElementById('block-form');
            if (form) {
                form.reset();

                const motive = form.querySelectorAll('#motive');
                motive.value = '';

                const errorMessages = form.querySelectorAll('.error');
                errorMessages.forEach(error => {
                    error.textContent = ''; 
                });

                const radios = form.querySelectorAll('input[type="radio"]');
                radios.forEach(radio => {
                    radio.checked = false;
                });

                const endTimeSection = document.getElementById('end_time_section');
                if (endTimeSection) {
                    endTimeSection.style.display = 'none';
                }
            }
        }

        window.resetForm = resetForm; 

        function toggleEndTimeSection() {
            var endTimeSection = document.getElementById('end_time_section');
            var periodicBlock = document.getElementById('periodic');
            if (periodicBlock && periodicBlock.checked) {
                endTimeSection.style.display = 'block'; 
            } else {
                endTimeSection.style.display = 'none';
            }
        }

        document.querySelectorAll('input[name="block_type"]').forEach(function (input) {
            input.addEventListener('change', toggleEndTimeSection);
        });

        toggleEndTimeSection();

    });
</script>
@endpush



