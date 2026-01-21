new Chart(mainCtx, {
    data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
        datasets: [
            {
                type: 'bar',
                label: 'Distance (km)',
                data: [560, 640, 530, 580, 470],
                backgroundColor: 'rgba(59,130,246,0.6)',
                borderRadius: 6,
                yAxisID: 'y'
            },
            {
                type: 'line',
                label: 'Mileage (km/L)',
                data: [12.5, 13.8, 14.3, 14.8, 14.2],
                borderColor: '#10b981',
                borderWidth: 3,
                tension: 0.4,
                pointRadius: 5,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#cbd5f5' } }
        },
        scales: {
            y: {
                ticks: { color: '#94a3b8' },
                grid: { color: '#1e293b' }
            },
            y1: {
                position: 'right',
                ticks: { color: '#10b981' },
                grid: { drawOnChartArea: false }
            },
            x: {
                ticks: { color: '#94a3b8' }
            }
        }
    }
});

