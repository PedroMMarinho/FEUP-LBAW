<div attribute-name="{{ $attributeName }}" class="attribute-container flex flex-col-reverse md:flex-row w-full border-b-2 py-4 gap-4">
    <div class="flex flex-col w-full">
        <div class="flex flex-row flex-wrap gap-4">
            <div class="flex flex-col justify-start gap-2 w-full md:w-fit">
                <label>Attribute name:</label>
                <p class="py-2 px-4 min-w-40 max-w-80 overflow-x-auto bg-white border-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $attributeName }}</p>
                <input type="hidden" name="attributes[{{ $attributeName }}][name]" value="{{ $attributeName }}">
            </div>
            <div class="flex flex-col justify-start gap-2 w-full md:w-fit">
                <label>Attribute type:</label>
                <x-select
                    :options="[
                        'string' => 'String',
                        'enum' => 'Enum',
                        'float' => 'Float',
                        'int' => 'Int',
                    ]"                                          
                    default-value="string"
                    container-class="w-40"
                    :id="'attribute-type-' . $attributeName" 
                    name="attributes[{{ $attributeName }}][type]"
                    placeholder="Attribute Type" />
            </div>
        </div>
        <div class="flex flex-col justify-start w-full mt-8 gap-2 hidden">
            <p>Attribute options:</p>
            <div class="flex flex-row flex-wrap w-full gap-2">
                <div attribute-name="{{ $attributeName }}" class="attribute-options-container flex flex-row flex-wrap gap-2">
                </div>
                <div class="flex flex-row items-center gap-3">
                    <x-text-input
                        class="w-full new_option"
                        name="new_option"
                        attribute-name="{{ $attributeName }}"
                        placeholder="New option" />
                    <button onclick="addNewOption(event, '{{ $attributeName }}')" type="button" class="w-5 h-6 fill-current text-black hover:text-blue-700 hover:scale-110 active:scale-100">
                        <svg viewBox="0 0 448 512">
                            <path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div>
        <button onclick="openDeleteAttributeModal('{{ $attributeName }}')" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md text-white hover:bg-red-700 focus:bg-red-800 active:bg-red-900 focus:outline-none hover:scale-105 active:scale-100 transition ease-in-out duration-150 whitespace-nowrap">
            Delete Attribute
        </button>
    </div>
</div>