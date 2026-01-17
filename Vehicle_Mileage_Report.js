// Initialize icons
lucide.createIcons();

/* ======================
   BAR + LINE CHART
====================== */
const mainCtx = document.getElementById('mainChart');

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

/* ======================
   GAUGE CHART
====================== */
const mileage = 18.2;
const gaugeCtx = document.getElementById('gaugeChart');

new Chart(gaugeCtx, {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [mileage, 20 - mileage],
            backgroundColor: ['#f59e0b', '#1e293b'],
            borderWidth: 0,
            circumference: 180,
            rotation: 270,
            cutout: '85%'
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
        }
    },
    plugins: [{
        id: 'text',
        beforeDraw(chart) {
            const { ctx, width, height } = chart;
            ctx.save();
            ctx.font = 'bold 22px Inter';
            ctx.fillStyle = '#f8fafc';
            ctx.textAlign = 'center';
            ctx.fillText(`${mileage} km/L`, width / 2, height / 1.4);
            ctx.restore();
        }
    }]
});

/* ======================
   TABLE DATA
====================== */
const vehicles = [
    { id: 'MH12AB1234', dist: 680, mileage: 16.1 },
    { id: 'MH12AB1234', dist: 530, mileage: 13.9 },
    { id: 'MH12AB1234', dist: 410, mileage: 10.8 }
];

const table = document.getElementById('vehicleTable');

vehicles.forEach(v => {
    let icon = 'check-circle';
    let color = 'text-green-500';

    if (v.mileage < 12) {
        icon = 'alert-triangle';
        color = 'text-red-500';
    } else if (v.mileage < 14) {
        icon = 'alert-circle';
        color = 'text-yellow-500';
    }

    table.innerHTML += `
        <tr class="border-t border-slate-800 hover:bg-slate-800/40">
            <td class="p-4">${v.id}</td>
            <td class="p-4 text-blue-400">${v.dist} km</td>
            <td class="p-4">${v.mileage} km/L</td>
            <td class="p-4">
                <i data-lucide="${icon}" class="${color} w-5 h-5"></i>
            </td>
        </tr>
    `;
});

lucide.createIcons();

