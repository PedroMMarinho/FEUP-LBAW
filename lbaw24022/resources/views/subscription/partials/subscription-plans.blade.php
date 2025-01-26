@props([
    'prices' => [3, 2, 1],
    'subscribed' => false,
])




<section class="xl:w-1/2 mb-10 xl:mb-0">


    <x-section-header
        :title="$subscribed ? 'Extend your subscription' : 'Choose your plan'"
        :class="'text-center !ml-0 pb-4'">
    </x-section-header>

    @php
        $planStyle = "planButton min-w-[550px] cursor-pointer h-28 bg-white border-blue-600 border-transparent hover:border-blue-600 border-2 hover:shadow-2xl flex justify-between items-start pt-8 px-8";
    @endphp

    <div class="flex flex-col justify-center gap-14 h-auto">
        <div class="{{$planStyle}}">
            <div>
                <p class="text-3xl font-semibold ">X DAYS</p>
                <p id="dailyPrice" class="text-right text-xs mr-1 -mt-1">{{$prices[0]*100}} cents/day</p>
            </div>

            <div class="mt-1 ml-5 flex items-center gap-2">
                <label class="text-xs " for="numberDaysInput">Number of days:</label>
                <x-input 
                    type="int"
                    :limitInt="3"
                    id="numberDaysInput"
                    placeholder="Ex. 365"
                    class="!w-16 !text-xs !bg-gray-50 !rounded-md !border-0" 
                />
                <input class="hidden planTypeInput" type="number" name="plan" value=1 disabled>

            </div>

            <p class="text-3xl text-right w-32 font-semibold overflow-hidden" id="totalXDaysPrice">0 €</p>

        </div>

        <div class="{{$planStyle}}">

            <div>
                <p class="text-3xl font-semibold ">1 MONTH</p>
                <p class="text-right text-xs mr-1 -mt-1">{{$prices[1] * 100}} cents/day</p>
            </div>
            <input class="hidden planTypeInput" type="number" name="plan" value=2 disabled>

            <p class="text-3xl font-semibold"> {{number_format($prices[1] * 31, 2)}} €</p>
        </div>

        <div class="{{$planStyle}}">

            <div>
                <p class="text-3xl font-semibold ">6 MONTHS</p>
                <p class="text-right text-xs mr-1 -mt-1">{{$prices[2]*100}} cents/day</p>
            </div>
            <input class="hidden planTypeInput" type="number" name="plan" value=3 disabled>

            <p class="text-3xl font-semibold"> {{number_format($prices[2] * 6 * 31, 2)}} €</p>
        </div>


    </div>


</section>

@push('scripts')
    <script>

            document.addEventListener('DOMContentLoaded', function () {

                const numberOfDaysInput  = document.getElementById("numberDaysInput");

                numberOfDaysInput.addEventListener('input', function (){

                    const totalPrice = document.getElementById("totalXDaysPrice");
                    const numDays = numberOfDaysInput.value;

                    const dailyPrice = parseFloat(document.getElementById("dailyPrice").textContent.split(" ")[0]);

                    totalPrice.textContent = numDays * dailyPrice / 100 + " €";
                })


                const plansButtons = Array.from(document.getElementsByClassName("planButton"));


                plansButtons.forEach((button, i) => {
                    button.addEventListener('click', function () {

                        plansButtons.forEach(btn => {

                            // Change style
                            btn.classList.remove("shadow-2xl");
                            btn.classList.add("border-transparent");

                            // Deactivate input
                            const inputs = btn.querySelectorAll('.planTypeInput');
                            inputs.forEach(input => {
                                input.disabled = true;
                            });

                        });

                        // Highlight the selected button
                        button.classList.add("shadow-2xl");
                        button.classList.remove("border-transparent");

                        // Enable inputs for the selected button
                        const activateInputs = button.querySelectorAll('input');
                        activateInputs.forEach(input => {
                            input.disabled = false;
                        });
                    });
                });
            });

    </script>
@endpush

