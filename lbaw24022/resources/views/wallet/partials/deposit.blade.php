

<section>
    <x-section-header 
        title="Transfer"
        class="!xl:ml-5 !ml-0">
    </x-section-header>

    <div class="bg-white rounded-lg relative" style="height: 32rem">
        <div class="flex justify-between h-12">

            @php
                $selected = "flex flex-grow items-center justify-center border-b-2 border-blue-600  cursor-pointer hover:border-blue-600";
                $notSelected = $selected  . " border-transparent bg-blue-50";
            @endphp

            <div id="depositButton"
                class="{{session('transferType') == 'Withdraw' ? $notSelected : $selected}}">
                Deposit
            </div>

            <div id="withdrawButton" 
                class="{{session('transferType') != 'Withdraw' ? $notSelected : $selected}}">
                withdraw
            </div>


        </div>


            <div class="py-4 px-6">
                    <div id="withdraw" 
                    @if (session('transferType') != 'Withdraw')
                        class="hidden"
                    @endif
                    >

                    <form id="send-verification"
                        method="post"
                        action="{{ route('verification.send') }}">
                        @csrf
                    </form>

                    <form id="send-transfer"
                        action="{{ session('transferType') == 'Deposit' ? route('wallet.withdraw') : route('wallet.withdraw') }}"
                        method="post">
                        @csrf

                    <label class="text-md" for="withdrawAmount">Amount</label>
                    <x-input
                        :type="'price'"
                        :placeholder="'How much to withdraw'"
                        :defaultValue="old('withdrawAmount')"
                        :id="'withdrawAmount'"
                        class="w-full"
                    />
                    <x-input-error class=""
                        :messages="$errors->get('withdrawAmount')" />

                    <div class="">
                        <label class="text-md" for="iban">IBAN</label>
                        <x-input
                            :type="'text'"
                            :placeholder="'ex: PT12 1234 1234 1234 1234 1234 1'"
                            :id="'iban'"
                            :defaultValue="old('iban')"
                            class="w-full"
                        />
                        <x-input-error class="mt-2"
                            :messages="$errors->get('iban')" />
                    </div>
                    

                    <div class="text-center text-2xl text-gray-400 font-bold mt-16 mb-20">
                        More transfer options soon...
                    </div>

                    <div class="flex justify-center w-full">
                            <x-primary-button class="!py-3 text-lg"> Confirm withdraw</x-primary-button>
                    </div>

                    </form>

                </div> 

                <div id="deposit" 
                @if (session('transferType') == 'Withdraw')
                    class="hidden"
                @endif
                >
                @can('transferMoney', Auth::user())
                    <label class="text-md" for="depositAmount">Amount</label>
                    <x-input
                        :type="'price'"
                        :placeholder="'How much to deposit'"
                        :defaultValue="old('depositAmount')"
                        :id="'depositAmount'"
                        class="w-full"
                    />
                    <x-input-error class=""
                        :messages="$errors->get('depositAmount')" 
                        id="depositAmount-error"
                        class="ml-2 mb-1"/>
                    @include('wallet.partials.paypal-payment')   
                @else
                    <div class="py-4 px-6 flex justify-center items-center h-full mt-40 text-center">
                        <p class="text-3xl text-gray-400 font-bold">Service not currently available.<br> Wait for block to end</p>
                    </div>
                @endcan
                </div>
        </div>
    </div>
</section>



 @push('scripts')
        <script>
            let transferType = "withdraw";

            document.addEventListener('DOMContentLoaded', function () {
                const depositButton = document.getElementById('depositButton');
                const withdrawButton = document.getElementById('withdrawButton');
                const confirmButton = document.getElementById('confirmTransfer');

                const depositSection = document.getElementById('deposit');
                const withdrawSection = document.getElementById('withdraw');

                
                depositButton.addEventListener('click', function (event){

                    withdrawSection.classList.add('hidden');
                    depositSection.classList.remove('hidden');

                    depositButton.classList.remove('border-transparent', 'bg-blue-50');
                    withdrawButton.classList.add('border-transparent', 'bg-blue-50');

                    transferType = "deposit";
                })

                withdrawButton.addEventListener('click', function (event){

                    depositSection.classList.add('hidden');
                    withdrawSection.classList.remove('hidden');

                    withdrawButton.classList.remove('border-transparent', 'bg-blue-50');
                    depositButton.classList.add('border-transparent', 'bg-blue-50');

                    transferType = "withdraw";
                })
            })
        </script>
    @endpush