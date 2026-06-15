<script src="{{ url(mix('js/dist/Chart.min.js')) }}"></script>
<script nonce="{{ csrf_token() }}">
    $(function () {
        if (typeof Chart === 'undefined') {
            return;
        }

        var labelColor = '#ffffff';
        var gridColor = 'rgba(255, 255, 255, 0.18)';
        var zeroLineColor = 'rgba(255, 255, 255, 0.35)';
        var primary = '#0d6e7a';
        var accent = '#2eb8a6';

        function barScales(yTicks) {
            return {
                yAxes: [{
                    ticks: Object.assign({}, yTicks, { fontColor: labelColor }),
                    gridLines: { color: gridColor, zeroLineColor: zeroLineColor }
                }],
                xAxes: [{
                    ticks: { maxRotation: 45, minRotation: 0, fontColor: labelColor },
                    gridLines: { color: gridColor }
                }]
            };
        }

        function renderDoughnut(canvasId, dataset) {
            var el = document.getElementById(canvasId);
            if (!el || !dataset) {
                return;
            }

            new Chart(el.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: dataset.labels || [],
                    datasets: [{
                        data: dataset.data || [],
                        backgroundColor: dataset.colors || [primary, accent, '#059669', '#1565c0'],
                    }]
                },
                options: {
                    legend: {
                        position: 'bottom',
                        labels: { fontColor: labelColor, fontSize: 11 }
                    },
                    maintainAspectRatio: false,
                    responsive: true
                }
            });
        }

        function renderBar(canvasId, labels, values, color) {
            var el = document.getElementById(canvasId);
            if (!el || !labels || !labels.length) {
                return;
            }

            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values || [],
                        backgroundColor: (color || primary) + 'bb',
                        borderColor: color || primary,
                        borderWidth: 1
                    }]
                },
                options: {
                    legend: { display: false },
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: barScales({ beginAtZero: true })
                }
            });
        }

        @if (!empty($patientChartData))
        var patientChartData = @json($patientChartData);
        renderDoughnut('ahopRiskSummaryChart', patientChartData.levelSummary);
        renderBar(
            'ahopRiskTopChart',
            patientChartData.topPatients.labels,
            patientChartData.topPatients.values,
            '#c62828'
        );
        @endif

        @if (!empty($equipmentChartData))
        var equipmentChartData = @json($equipmentChartData);
        renderDoughnut('ahopEquipmentSummaryChart', equipmentChartData.urgencySummary);
        renderBar(
            'ahopEquipmentTopChart',
            equipmentChartData.topEquipment.labels,
            equipmentChartData.topEquipment.values,
            primary
        );
        @endif

        @if (!empty($labChartData))
        var labChartData = @json($labChartData);
        var directionColors = {
            worsening: '#c62828',
            stable: '#1565c0',
            improving: '#2e7d32'
        };

        renderDoughnut('ahopLabTrendSummaryChart', {
            labels: labChartData.directionSummary.labels,
            data: labChartData.directionSummary.data,
            colors: labChartData.directionSummary.colors
        });

        (labChartData.lineCharts || []).forEach(function (chart) {
            var el = document.getElementById(chart.id);
            if (!el) {
                return;
            }

            var color = directionColors[chart.direction] || primary;
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
                        pointBackgroundColor: color
                    }]
                },
                options: {
                    legend: { display: false },
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        yAxes: [{ ticks: { beginAtZero: false, fontColor: labelColor }, gridLines: { color: gridColor } }],
                        xAxes: [{ ticks: { maxRotation: 45, minRotation: 0, fontColor: labelColor }, gridLines: { color: gridColor } }]
                    }
                }
            });
        });
        @endif
    });
</script>
