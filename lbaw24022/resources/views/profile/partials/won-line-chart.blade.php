@php
    $currentMonth = now();
    $lastSixMonths = [];
    $winAll = [];

    // Array to store months in reverse order
    for ($i = 5; $i >= 0; $i--) {
        $month = $currentMonth->copy()->subMonths($i);
        $lastSixMonths[] = $month->format('F'); 
        
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $winAll[] = Auth::user()->user->sellTransactions()
                            ->whereBetween('timestamp', [$monthStart, $monthEnd])
                            ->where('amount', '>', 0)->sum('amount');
    }
@endphp
<canvas class="w-full h-full" id="wonChart" auction-money="{{$auctionsSpent}}" sub-money="{{$subscriptionsSpent}}" ad-money="{{$advertiseSpent}}"></canvas>



@push('scripts')
<script>


document.addEventListener('DOMContentLoaded', function() {
    const ctxSales = document.getElementById('wonChart').getContext('2d');

    const winAll = @json($winAll);
    const labels = @json($lastSixMonths); 


    const maxValue = Math.max(...winAll);
    const stepSize = Math.ceil(maxValue / 3); 
    const suggestedMax = maxValue + stepSize; 

    const salesChart = new Chart(ctxSales, {
        type: 'line', 
        data: {
            labels: labels, 

            datasets: [
                {
                label: 'Total won',
                data: winAll, // Y-axis data: sales values
                backgroundColor: '#eb7405', // Bar color
                borderColor: '#eb7405',
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