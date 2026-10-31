import { ArcElement, Chart, DoughnutController, Legend, Tooltip } from 'chart.js';

Chart.register(DoughnutController, ArcElement, Tooltip, Legend);

const PALETTE = ['#0d9488', '#0ea5e9', '#f59e0b', '#8b5cf6', '#ec4899', '#22c55e', '#f43f5e', '#64748b'];

const rupiah = new Intl.NumberFormat('id-ID');

document.querySelectorAll('canvas[data-chart="expense-by-category"]').forEach((canvas) => {
    const chart = JSON.parse(canvas.dataset.chartData ?? '{}');
    const values = chart.values ?? [];

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: chart.labels ?? [],
            datasets: [
                {
                    data: values,
                    backgroundColor: values.map((_, index) => PALETTE[index % PALETTE.length]),
                    borderWidth: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, boxHeight: 10, padding: 12, usePointStyle: true },
                },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.label}: Rp ${rupiah.format(context.parsed)}`,
                    },
                },
            },
        },
    });
});
