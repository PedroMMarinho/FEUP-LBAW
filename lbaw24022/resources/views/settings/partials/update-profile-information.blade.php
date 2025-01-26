<section class="w-full">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information.") }}
        </p>
    </header>   

    <form id="send-verification"
        method="post"
        action="{{ route('verification.send') }}">
        @csrf
    </form>
    <form method="post" action="{{ route('profileImage.upload', $user->id)}}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf   
        @method('patch')

        <div>
            <label for="profile_picture" class="block font-medium text-sm text-gray-700">{{ __('Profile Picture') }}</label>
            
            <div style="height:150px" class="py-3 relative overflow-hidden">
                <img src="{{$user->getProfileImage()}}" 
                id="profile_image_preview"
                class="aspect-square object-cover h-32 rounded-full" 
                alt="Profile Image"
                onclick="document.getElementById('profile_picture').click();">
                @if($user->image()->exists())
                    <button type="button" onclick="submitDelete()" class="absolute top-2 left-24 bg-gray-300 text-white rounded-full p-2 hover:bg-gray-500">
                        <svg class="w-5 h-5" viewBox="-0.5 0 19 19" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>icon/18/icon-delete</title> <desc>Created with Sketch.</desc> <defs> </defs> <g id="out" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage"> <path d="M4.91666667,14.8888889 C4.91666667,15.3571429 5.60416667,16 6.0625,16 L12.9375,16 C13.3958333,16 14.0833333,15.3571429 14.0833333,14.8888889 L14.0833333,6 L4.91666667,6 L4.91666667,14.8888889 L4.91666667,14.8888889 L4.91666667,14.8888889 Z M15,3.46500003 L12.5555556,3.46500003 L11.3333333,2 L7.66666667,2 L6.44444444,3.46500003 L4,3.46500003 L4,4.93000007 L15,4.93000007 L15,3.46500003 L15,3.46500003 L15,3.46500003 Z" id="path" fill="#000000" sketch:type="MSShapeGroup"> </path> </g> </g></svg>
                    </button>
                @endif

            </div>
            
            <input type="file" id="profile_picture" name="image" accept="image/*" class="hidden" onchange="previewImage(event)"/>
            <input type="text" name="type" value="profile" hidden>
        </div>
        
        <div class="flex flex-row">
            <label for="profile_picture" 
                class=" mr-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Upload
            </label>
            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save') }}</x-primary-button>
                
                <x-input-error :messages="$errors->get('image')"
                    class="mt-2" />

                @if (session('status') === 'image-updated')
                    <p x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600">{{ __('Saved.') }}</p>
                @endif

                @if (session('status') === 'image-deleted')
                    <p x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">{{ __('Deleted.') }}</p>
                @endif
            </div>
        </div>

    </form>

    <form id="delete-form" method="post" action="{{ route('profileImage.destroy', $user->id) }}" class="hidden">
        @csrf
        @method('delete')
    </form>

    <hr class=" mt-6 border-t border-gray-300">
    
    <form method="post"
        action="{{ route('settings.update', ['section' => 'profile', 'userId' => $user->id]) }}"
        class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="username"
                :value="__('Username')" />
            <x-text-input id="username"
                name="username"
                type="text"
                class="mt-1 block w-full"
                :value="old('username', $user->username)"
                required
                autofocus
                autocomplete="username" />
            <x-input-error class="mt-2"
                :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="description"
                :value="__('Description')" />
            <x-textarea-input id="user_description"
                name="description"
                type="text"
                class="mt-1 block w-full h-32"
                :value="old('description', $user->description)"
                />

            <x-input-error class="mt-2"
                :messages="$errors->get('description')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'settings-updated')
                <p x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
    @push('scripts')
        <script>

            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('profile_picture').addEventListener('change', previewImage);
            });


            function submitDelete() {
                document.getElementById('delete-form').submit();
            }
            
            function previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();

                    // When the file is read successfully, set the image src
                    reader.onload = function(e) {
                        document.getElementById('profile_image_preview').src = e.target.result;
                    };

                    // Read the file as a Data URL (base64 string)
                    reader.readAsDataURL(file);
                }
            }

            

        </script>
    @endpush
    
    
</section>
