<script src="{{ url(mix('js/dist/Chart.min.js')) }}"></script>
<script nonce="{{ csrf_token() }}">
    $(function () {
        if (typeof Chart === 'undefined') {
            return;
        }

        var chartData = @json($chartData ?? []);
        var primary = '#0d6e7a';
        var accent = '#2eb8a6';
        var labelColor = '#ffffff';
        var gridColor = 'rgba(255, 255, 255, 0.18)';
        var zeroLineColor = 'rgba(255, 255, 255, 0.35)';

        function buildScales(yTicks) {
            return {
                yAxes: [{
                    ticks: Object.assign({}, yTicks, { fontColor: labelColor }),
                    gridLines: {
                        color: gridColor,
                        zeroLineColor: zeroLineColor,
                    }
                }],
                xAxes: [{
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0,
                        fontColor: labelColor,
                    },
                    gridLines: { color: gridColor }
                }]
            };
        }

        function renderBarChart(canvasId, dataset, options) {
            options = options || {};
            var el = document.getElementById(canvasId);
            if (!el || !dataset) {
                return;
            }

            var yTicks = { beginAtZero: true };
            if (typeof options.yCallback === 'function') {
                yTicks.callback = options.yCallback;
            }

            var chartOptions = {
                legend: {
                    display: !!options.showLegend,
                    labels: { fontColor: labelColor }
                },
                maintainAspectRatio: false,
                responsive: true,
                scales: buildScales(yTicks)
            };

            if (typeof options.tooltipCallback === 'function') {
                chartOptions.tooltips = {
                    callbacks: {
                        label: options.tooltipCallback
                    }
                };
            }

            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: dataset.labels || [],
                    datasets: [{
                        label: options.label || '',
                        data: dataset.values || [],
                        backgroundColor: options.backgroundColor || (accent + 'cc'),
                        borderColor: options.borderColor || primary,
                        borderWidth: 1,
                    }]
                },
                options: chartOptions
            });
        }

        function renderDoughnutChart(canvasId, dataset) {
            var el = document.getElementById(canvasId);
            if (!el || !dataset) {
                return;
            }

            new Chart(el.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: dataset.labels || [],
                    datasets: [{
                        data: dataset.values || [],
                        backgroundColor: dataset.colors || [primary, accent, '#059669', '#1565c0'],
                    }]
                },
                options: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            fontColor: labelColor,
                            fontSize: 11,
                        }
                    },
                    maintainAspectRatio: false,
                    responsive: true,
                    tooltips: {
                        callbacks: {
                            label: function (tooltipItem, data) {
                                var label = data.labels[tooltipItem.index] || '';
                                var value = data.datasets[0].data[tooltipItem.index] || 0;
                                return label + ': ₱' + Number(value).toLocaleString(undefined, { maximumFractionDigits: 0 });
                            }
                        }
                    }
                }
            });
        }

        var peso = function (value) {
            return '₱' + Number(value).toLocaleString(undefined, { maximumFractionDigits: 0 });
        };

        renderBarChart('ahopReportOpdChart', chartData.opd_visits, {
            label: @json(trans('admin/clinical_reports/general.chart_opd_visits')),
            backgroundColor: primary + 'bb',
            borderColor: primary,
        });

        renderBarChart('ahopReportCollectionsChart', chartData.collections, {
            label: @json(trans('admin/clinical_reports/general.chart_collections')),
            backgroundColor: accent + 'bb',
            borderColor: accent,
            yCallback: function (value) { return peso(value); },
            tooltipCallback: function (tooltipItem) {
                return peso(tooltipItem.yLabel);
            }
        });

        renderDoughnutChart('ahopReportRevenueChart', chartData.revenue_by_service);

        var aging = chartData.invoice_aging || {};
        renderBarChart('ahopReportAgingChart', aging, {
            label: @json(trans('admin/clinical_reports/general.chart_invoice_aging')),
            backgroundColor: aging.colors || (accent + 'bb'),
            borderColor: primary,
            yCallback: function (value) { return peso(value); },
            tooltipCallback: function (tooltipItem) {
                return peso(tooltipItem.yLabel);
            }
        });
    });
</script>
