<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-5 gap-4">
            <div class="p-4 sm:p-8 bg-white shadow rounded-lg col-span-full lg:col-start-1 lg:col-end-2">
                
                @php
                    $op = [
                        'Profile' => ['section' => 'profile', 'userId' => $user->id],
                    ];

                    if(Auth::user()->id === $user->id){
                        $op['Account'] = ['section' => 'account', 'userId' => $user->id]; 
                    }
                    
                    if (Auth::user()->role === 'Regular User') 
                    {
                        $op['Notifications'] = ['section' => 'notifications', 'userId' => $user->id];
                    } 
                    else if ((Auth::user()->role === 'Admin') && (Auth::user()->id === $user->id))
                    {
                        /*
                            Caso de admin a ver o seu perfil 
                            $op['System'] = ['section' => 'system', 'userId' => $user->id];
                            ...
                        */
                    }
                    else if ((Auth::user()->role === 'Admin') && (Auth::user()->id !== $user->id) && ($user->role === 'Regular User'))
                    {       
                            $op['Blocks'] = ['section' => 'block', 'userId' => $user->id];
                            $op['Reports'] = ['section' => 'reports', 'userId' => $user->id];
                    }
                @endphp

                <x-sidebar 
                    :route="'settings.show'"
                    :options="$op"
                />
            </div>
            <div class="p-4 sm:p-8 bg-white shadow rounded-lg col-span-full lg:col-start-2 lg:col-end-6 min-h-[28rem]">
                @yield('slot')
            </div>
        </div>
    </div>
</x-app-layout>

