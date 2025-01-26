<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-slot name="header">
                <h2 id="auctions-header" class="font-semibold text-xl text-gray-800 leading-tight">
                    @if ($section == 'to-ship')
                        {{ __('My Auctions Waiting To Be Shipped ') }}
                    @elseif ($section == 'shipped')
                        {{ __('My Shipped Auctions') }}
                    @elseif ($section == 'canceled')
                        {{ __('My Canceled Auctions') }}
                    @elseif ($section == 'finished-without-bids')
                        {{ __('My Auctions That Finished Without Bids') }}
                    @else
                        {{ __('My Active Auctions') }}
                    @endif
                </h2>
            </x-slot>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <div class="flex flex-col items-center justify-center p-3 h-full aspect-square rounded-xl gap-4 bg-white shadow-sm">
                    <h2 class="text-5xl" id='active-count'>{{ $auctionCounts['Active'] }}</h2>
                    <h2 class="text-lg text-center">Active</h2>
                </div>
                <div class="flex flex-col items-center justify-center p-4 h-full aspect-square rounded-xl gap-4 bg-white shadow-sm">
                    <h2 class="text-5xl" id='to-ship-count'>{{ $auctionCounts['Finished'] }}</h2>
                    <h2 class="text-lg text-center">To Ship</h2>
                </div>
                <div class="flex flex-col items-center justify-center p-4 h-full aspect-square rounded-xl gap-4 bg-white shadow-sm">
                    <h2 class="text-5xl" id='shipped-count'>{{ $auctionCounts['Shipped'] }}</h2>
                    <h2 class="text-kg text-center">Shipped</h2>
                </div>
                <div class="flex flex-col items-center justify-center p-4 h-full aspect-square rounded-xl gap-4 bg-white shadow-sm">
                    <h2 class="text-5xl" id='canceled-count'>{{ $auctionCounts['Canceled'] }}</h2>
                    <h2 class="text-lg text-center">Canceled</h2>
                </div>
                <div class="flex flex-col items-center justify-center p-2 h-full aspect-square rounded-xl gap-4 bg-white shadow-sm">
                    <h2 class="text-5xl" id='finished-without-bids-count'>{{ $auctionCounts['Finished without bids'] }}</h2>
                    <h2 class="text-lg text-center">Finished w/o Bids</h2>
                </div>
            </div>
            <div class="flex flex-wrap justify-start gap-2 my-6">
                <a href="/seller-dashboard" class="px-4 py-2 {{ $section == null || $section == 'active' ? 'bg-indigo-600' : 'bg-slate-400' }} text-md text-white rounded-full hover:bg-indigo-700 hover:scale-105 active:scale-100">
                    Active Auctions
                </a>
                <a href="/seller-dashboard?section=to-ship" class="px-4 py-2 {{ $section == 'to-ship' ? 'bg-indigo-600' : 'bg-slate-400' }} text-md text-white rounded-full hover:bg-indigo-700 hover:scale-105 active:scale-100">
                    To Ship
                </a>
                <a href="/seller-dashboard?section=shipped" class="px-4 py-2 {{ $section == 'shipped' ? 'bg-indigo-600' : 'bg-slate-400' }} text-md text-white rounded-full hover:bg-indigo-700 hover:scale-105 active:scale-100">
                    Shipped
                </a>
                <a href="/seller-dashboard?section=canceled" class="px-4 py-2 {{ $section == 'canceled' ? 'bg-indigo-600' : 'bg-slate-400' }} text-md text-white rounded-full hover:bg-indigo-700 hover:scale-105 active:scale-100">
                    Canceled
                </a>
                <a href="/seller-dashboard?section=finished-without-bids" class="px-4 py-2 {{ $section == 'finished-without-bids' ? 'bg-indigo-600' : 'bg-slate-400' }} text-md text-white rounded-full hover:bg-indigo-700 hover:scale-105 active:scale-100">
                    Finished Without Bids
                </a>
            </div>
        </div>
        <div id="auctions-section">
            <x-auctions-search-and-filters 
                :search="$search"
                :location="$location"
                :categories="$categories"
                :selected-category="$selectedCategory"
                :from-bid="$fromBid"
                :to-bid="$toBid"
                :total-auctions="$totalAuctions"
                :auctions="$auctions"
                :auctions-only="true"
                :section-page="true"
                :default-search-option="'auction'"
            />
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // -------------------------------------------------------------
                // Ship Buttons
                // -------------------------------------------------------------

                const shipBtns = document.querySelectorAll('.ship-button');
                const toShipCountH2 = document.getElementById('to-ship-count');
                const toShipCount = parseInt(toShipCountH2.textContent, 10);
                const shippedCountH2 = document.getElementById('shipped-count');
                const shippedCount = parseInt(shippedCountH2.textContent, 10);

                function getShippingForms(auctionId) {
                    fetch(`/partials/shipping-forms/${auctionId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        const html2pdfOptions = {
                            margin:       [15, 15, 15, 15],
                            filename:     `okshon_shipping_form-auction_${auctionId}.pdf`,
                            html2canvas:  { scale: 2 },
                            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                        };

                        html2pdf()
                            .from(html)
                            .set(html2pdfOptions)
                            .save();
                    });
                }

                shipBtns.forEach(function(button) {
                    button.addEventListener('click', async function(event) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        try {
                            const auctionId = button.getAttribute('auction-id');
                            const response = await fetch(`/shipAuction/${auctionId}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json',
                                },
                            });

                            const data = await response.json();

                            if (data.success) {
                                toShipCountH2.textContent = toShipCount - 1;
                                shippedCountH2.textContent = shippedCount + 1;

                                const auctionLink = button.closest('a');
                                if (auctionLink) {
                                    auctionLink.remove();
                                }
                                showPopUp(data.reply, 'success');
                                getShippingForms(auctionId);
                            } else {
                                console.error(data.message);
                                showPopUp(data.reply, 'error');
                            }
                        } catch (error) {
                            console.error('Error shipping item:', error);
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
