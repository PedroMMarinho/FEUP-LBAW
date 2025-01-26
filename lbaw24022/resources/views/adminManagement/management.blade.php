


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-5 gap-4">
            <div class="p-4 sm:p-8 bg-white shadow rounded-lg col-span-full lg:col-start-1 lg:col-end-2">
                <x-sidebar 
                    :route="'management.show'"
                    :options="[
                        'Create Accounts' => ['section' => 'createAccounts'], 
                        'User Reports' => ['section' => 'userReports'], 
                        'User Appeals' => ['section' => 'userAppeals'],
                        'Auction Reports' => ['section' => 'auctionReports'],
                        'Categories' => ['section' => 'categories'],
                        'Admin Changes' => ['section' => 'adminChanges'],
                        'System Settings' => ['section' => 'systemSettings']
                    ]"
                />
                
            </div>
            <div class="p-4 sm:p-8 bg-white shadow rounded-lg col-span-full lg:col-start-2 lg:col-end-6 min-h-[28rem]">
                @yield('slot')
            </div>
        </div>
    </div>
</x-app-layout>

