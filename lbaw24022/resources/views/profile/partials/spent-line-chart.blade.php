
@php
    $currentMonth = now();
    $lastSixMonths = [];
    $spendAll = [];
    $spendSubs = [];
    $spendAd = [];
    $spendAuctions = [];

    // Array to store months in reverse order
    for ($i = 5; $i >= 0; $i--) {
        $month = $currentMonth->copy()->subMonths($i);
        $lastSixMonths[] = $month->format('F'); 
        
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $allTransactions = Auth::user()->user->variousTransactions()
                            ->whereBetween('timestamp', [$monthStart, $monthEnd])
                            ->where('transaction_type', '!=','Wallet')
                            ->where('amount', '>', 0);

        $spendAll[] = $allTransactions->sum('amount');
        $spendSubs[] = $allTransactions->clone()->where('transaction_type','Subscription')->sum('amount');
        $spendAd[] = $allTransactions->clone()->where('transaction_type', 'Advertisement')->sum('amount');


        $spendAuctions[] = $allTransactions->where('transaction_type', 'Auction')->sum('amount');
    }
@endphp
<canvas class="w-full h-full" id="spendChart" auction-money="{{$auctionsSpent}}" sub-money="{{$subscriptionsSpent}}" ad-money="{{$advertiseSpent}}"></canvas>



@push('scripts')
<script>


document.addEventListener('DOMContentLoaded', function() {
    const ctxSales = document.getElementById('spendChart').getContext('2d');

    const spendAll = @json($spendAll);
    const spendSubs = @json($spendSubs);
    const spendAd = @json($spendAd);
    const spendAuction = @json($spendAuctions);


    const maxValue = Math.max(...spendAll);
    const stepSize = Math.ceil(maxValue / 3); 
    const suggestedMax = maxValue + stepSize; 

    console.log(spendAd);

    const labels = @json($lastSixMonths); // Passing the labels (month names) from Blade to JavaScript

    const salesChart = new Chart(ctxSales, {
        type: 'line',  // Bar chart type
        data: {
            labels: labels, // X-axis labels: months

            datasets: [
                {
                label: 'Total spends',
                data: spendAll, // Y-axis data: sales values
                backgroundColor: '#eb7405', // Bar color
                borderColor: '#eb7405',
                borderWidth: 1
                },
                {
                label: 'Subscriptions',
                data: spendSubs, // Y-axis data: sales values
                backgroundColor: '#FFEB33', // Bar color
                borderColor: '#FFEB33',
                borderWidth: 1
                },
                {
                label: 'Auctions',
                data: spendAuction, // Y-axis data: sales values
                backgroundColor: '#FF5733', // Bar color
                borderColor: '#FF5733',
                borderWidth: 1
                },
                {
                label: 'Advertisements',
                data: spendAd, // Y-axis data: sales values
                backgroundColor: '#33A7FF', // Bar color
                borderColor: '#33A7FF',
                borderWidth: 1
                },

            ] 
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true, 
                    ticks: {
                        stepSize: stepSize,
                        suggestedMax: suggestedMax,
                    },
                    title: {
                        display: true,
                        text: 'Money (€)' 
                    }
                },
                x: {
                    title: {
                        display: false,
                        text: 'Month'  
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom', // Position of the legend
                    align: 'center', // Center-align the legend
                    labels: {
                        font: {
                            size: 12,
                            weight: 'bold', // Make the labels bold
                        },
                        usePointStyle: true, 
                        pointStyle: 'circle',
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return '  ' + tooltipItem.raw.toLocaleString() + ' €'; // Add the currency symbol to the tooltip
                        }
                    }
                }
            }
        }
    });




});

    </script>

    
@endpush