// ═══════════════════ CHARTS ═══════════════════

let revChart, pieChart;

const revData = {
    daily: {labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], data: [5200, 4100, 6800, 5500, 7900, 9400, 8100]},
    weekly: {labels: ['Wk 1', 'Wk 2', 'Wk 3', 'Wk 4'], data: [28000, 34000, 26000, 41000]},
    monthly: {labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], data: [18000, 16000, 24000, 28000, 22000, 31000, 29000, 36000, 32000, 40000, 38000, 42000]},
};

export function initCharts() {
    if (revChart) revChart.destroy();
    if (pieChart) pieChart.destroy();
    const rCtx = document.getElementById('revenueChart').getContext('2d');
    const grad = rCtx.createLinearGradient(0, 0, 0, 200);
    grad.addColorStop(0, 'rgba(184,137,90,.25)');
    grad.addColorStop(1, 'rgba(184,137,90,.03)');
    revChart = new Chart(rCtx, {
        type: 'bar',
        data: {
            labels: revData.daily.labels,
            datasets: [{
                label: 'Revenue (₱)',
                data: revData.daily.data,
                backgroundColor: grad,
                borderColor: '#b8895a',
                borderWidth: 2,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            plugins: {legend: {display: false}},
            scales: {
                y: {beginAtZero: true, grid: {color: '#f0e8de'}, ticks: {color: '#9a7d68', font: {family: 'Jost'}}},
                x: {grid: {display: false}, ticks: {color: '#9a7d68', font: {family: 'Jost'}}}
            }
        }
    });
    const pCtx = document.getElementById('orderPie').getContext('2d');
    pieChart = new Chart(pCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Pending', 'Shipped', 'Cancelled'],
            datasets: [{
                data: [580, 220, 200, 42],
                backgroundColor: ['#8fad88', '#d9a84e', '#c9a87c', '#c97070'],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {position: 'bottom', labels: {font: {family: 'Jost', size: 11}, padding: 12, color: '#9a7d68'}}
            },
            cutout: '68%'
        }
    });
}

export function switchTab(el, key) {
    document.querySelectorAll('.tab-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    revChart.data.labels = revData[key].labels;
    revChart.data.datasets[0].data = revData[key].data;
    revChart.update();
}