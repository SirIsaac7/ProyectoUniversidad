(function () {
    function obtenerColor(variable, respaldo) {
        var valor = getComputedStyle(document.documentElement).getPropertyValue(variable);

        return valor ? valor.trim() : respaldo;
    }

    function inicializarGraficaCitasProveedor() {
        var chartElement = document.getElementById('proveedorCitasOverviewChart');

        if (!chartElement || typeof ApexCharts === 'undefined') {
            return;
        }

        var chartData = JSON.parse(chartElement.dataset.chart || '{}');
        var series = chartData.series || {};

        var chart = new ApexCharts(chartElement, {
            series: [
                {
                    name: 'Citas totales',
                    type: 'area',
                    data: series.totales || [],
                },
                {
                    name: 'Completadas',
                    type: 'column',
                    data: series.completadas || [],
                },
                {
                    name: 'Canceladas o vencidas',
                    type: 'column',
                    data: series.incidencias || [],
                },
            ],
            chart: {
                height: 360,
                type: 'line',
                toolbar: {
                    show: false,
                },
                zoom: {
                    enabled: false,
                },
            },
            colors: [
                obtenerColor('--vz-primary', '#6f42c1'),
                obtenerColor('--vz-success', '#0ab39c'),
                obtenerColor('--vz-warning', '#f7b84b'),
            ],
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth',
                width: [3, 0, 0],
            },
            fill: {
                opacity: [0.16, 0.88, 0.75],
                type: ['gradient', 'solid', 'solid'],
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.04,
                    stops: [0, 90, 100],
                },
            },
            markers: {
                size: [4, 0, 0],
                strokeWidth: 3,
                hover: {
                    size: 6,
                },
            },
            plotOptions: {
                bar: {
                    columnWidth: '28%',
                    borderRadius: 4,
                },
            },
            xaxis: {
                categories: chartData.labels || [],
                axisTicks: {
                    show: false,
                },
                axisBorder: {
                    show: false,
                },
                labels: {
                    style: {
                        colors: obtenerColor('--vz-secondary-color', '#878a99'),
                    },
                },
            },
            yaxis: {
                min: 0,
                forceNiceScale: true,
                labels: {
                    formatter: function (value) {
                        return Number(value).toFixed(0);
                    },
                    style: {
                        colors: obtenerColor('--vz-secondary-color', '#878a99'),
                    },
                },
            },
            grid: {
                borderColor: 'rgba(135, 138, 153, 0.18)',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: true,
                    },
                },
                yaxis: {
                    lines: {
                        show: true,
                    },
                },
                padding: {
                    top: 0,
                    right: 18,
                    bottom: 8,
                    left: 12,
                },
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                markers: {
                    width: 10,
                    height: 10,
                    radius: 5,
                },
                labels: {
                    colors: obtenerColor('--vz-secondary-color', '#878a99'),
                },
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (value) {
                        return Number(value).toFixed(0) + ' citas';
                    },
                },
            },
        });

        chart.render();
    }

    document.addEventListener('DOMContentLoaded', function () {
        inicializarGraficaCitasProveedor();
    });
})();
