@if (!empty($labChartData))
    <script src="{{ url(mix('js/dist/Chart.min.js')) }}"></script>
    <script nonce="{{ csrf_token() }}">
        (function () {
            var chartData = @json($labChartData);
            var directionColors = {
                worsening: '#c62828',
                stable: '#1565c0',
                improving: '#2e7d32'
            };

            var summaryCtx = document.getElementById('ahopLabTrendSummaryChart');
            if (summaryCtx && chartData.directionSummary) {
                new Chart(summaryCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: chartData.directionSummary.labels,
                        datasets: [{
                            data: chartData.directionSummary.data,
                            backgroundColor: chartData.directionSummary.colors,
                        }]
                    },
                    options: {
                        legend: { position: 'bottom' },
                        maintainAspectRatio: false,
                    }
                });
            }

            (chartData.lineCharts || []).forEach(function (chart) {
                var el = document.getElementById(chart.id);
                if (!el) {
                    return;
                }
                var color = directionColors[chart.direction] || '#0d6e7a';
                new Chart(el.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: chart.labels,
                        datasets: [{
                            label: chart.title + (chart.unit ? ' (' + chart.unit + ')' : ''),
                            data: chart.values,
                            borderColor: color,
                            backgroundColor: color + '33',
                            fill: true,
                            lineTension: 0.2,
                            pointRadius: 4,
                            pointBackgroundColor: color,
                        }]
                    },
                    options: {
                        legend: { display: false },
                        maintainAspectRatio: false,
                        scales: {
                            yAxes: [{ ticks: { beginAtZero: false } }],
                            xAxes: [{ ticks: { maxRotation: 45, minRotation: 0 } }]
                        }
                    }
                });
            });
        })();
    </script>
@endif
