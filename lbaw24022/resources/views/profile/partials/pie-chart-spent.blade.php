
<canvas class="w-full h-full" id="pieChart" auction-money="{{$auctionsSpent}}" sub-money="{{$subscriptionsSpent}}" ad-money="{{$advertiseSpent}}"></canvas>

@push('scripts')
 <script>
document.addEventListener('DOMContentLoaded', function() {
    const pieCanvas = document.getElementById('pieChart');

    // Get data from custom attributes
    const auctionMoney = parseFloat(pieCanvas.getAttribute('auction-money'));
    const subMoney = parseFloat(pieCanvas.getAttribute('sub-money'));
    const adMoney = parseFloat(pieCanvas.getAttribute('ad-money'));

    // Get the context of the canvas
    let ctxPie = pieCanvas.getContext('2d');

    // Create the donut chart
    var myDonutChart = new Chart(ctxPie, {
        type: 'pie',  // Donut chart uses pie chart type
        data: {
            labels: ['Auctions', 'Advertisements', 'Subscriptions'],
            datasets: [{
                label: 'Money Spent',
                data: [auctionMoney, adMoney, subMoney],  // Data from attributes
                backgroundColor: ['#FF5733', '#33A7FF', '#FFEB33'],  // Segment colors
                borderWidth: 5
            }]
        },
        options: {
            cutout: '70%',  // Creates the donut shape
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return '  Total : ' + tooltipItem.raw + ' €';
                        }
                    }
                },
            layout: {
                padding: {
                    bottom: 40
                }
            },
            legend: {
                position: 'bottom',  // Place the legend below the chart
                align: 'center',     // Center-align the legend
                labels: {
                    font: {
                        size: 12, 
                        weight: 'bold'  // Make the font bold
                    },
                    padding: 12,
                    usePointStyle: true,
                }
            }
            },
        }
    });
});
 </script>
@endpush