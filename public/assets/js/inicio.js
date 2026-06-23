(function () {
    function obtenerColor(variable, respaldo) {
        var valor = getComputedStyle(document.documentElement).getPropertyValue(variable);

        return valor ? valor.trim() : respaldo;
    }

    function inicializarGraficaCitasProveedor() {
        var chartElement = document.querySelector('[data-inicio-citas-chart]');

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
                labels: {
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

    function inicializarGraficasProveedoresCliente() {
        var chartElements = document.querySelectorAll('[data-inicio-proveedor-rating-chart]');

        if (!chartElements.length || typeof ApexCharts === 'undefined') {
            return;
        }

        chartElements.forEach(function (chartElement) {
            var data = JSON.parse(chartElement.dataset.chart || '[]');
            var valores = data.some(function (value) {
                return Number(value) > 0;
            }) ? data : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

            var chart = new ApexCharts(chartElement, {
                series: [
                    {
                        name: 'Promedio',
                        data: valores,
                    },
                ],
                chart: {
                    type: 'line',
                    width: 120,
                    height: 48,
                    sparkline: {
                        enabled: true,
                    },
                    toolbar: {
                        show: false,
                    },
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                },
                colors: [obtenerColor('--vz-info', '#299cdb')],
                tooltip: {
                    fixed: {
                        enabled: false,
                    },
                    x: {
                        show: false,
                    },
                    y: {
                        formatter: function (value) {
                            return Number(value).toFixed(1) + ' estrellas';
                        },
                    },
                },
            });

            chart.render();
        });
    }

    function inicializarGraficaAdminModulos() {
        var chartElement = document.querySelector('[data-inicio-admin-modulos-chart]');

        if (!chartElement || typeof ApexCharts === 'undefined') {
            return;
        }

        var chartData = JSON.parse(chartElement.dataset.chart || '{}');
        var labels = chartData.labels || [];
        var valores = chartData.series || [];
        var data = labels.map(function (label, index) {
            return {
                x: label,
                y: Number(valores[index] || 0),
            };
        });

        var chart = new ApexCharts(chartElement, {
            series: [
                {
                    name: 'Registros',
                    data: data,
                },
            ],
            chart: {
                height: Math.max(380, labels.length * 34),
                type: 'bar',
                toolbar: {
                    show: false,
                },
            },
            colors: [obtenerColor('--vz-primary', '#6f42c1')],
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    barHeight: '62%',
                    horizontal: true,
                    distributed: true,
                },
            },
            dataLabels: {
                enabled: true,
                formatter: function (value) {
                    return Number(value).toFixed(0);
                },
                style: {
                    fontWeight: 700,
                },
            },
            legend: {
                show: false,
            },
            xaxis: {
                labels: {
                    style: {
                        colors: obtenerColor('--vz-secondary-color', '#878a99'),
                    },
                },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: obtenerColor('--vz-secondary-color', '#878a99'),
                    },
                },
            },
            grid: {
                borderColor: 'rgba(135, 138, 153, 0.18)',
                strokeDashArray: 4,
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return Number(value).toFixed(0) + ' registros';
                    },
                },
            },
        });

        chart.render();
    }

    function inicializarGraficaAdminCitasRadial() {
        var chartElement = document.querySelector('[data-inicio-admin-citas-radial]');

        if (!chartElement || typeof ApexCharts === 'undefined') {
            return;
        }

        var chartData = JSON.parse(chartElement.dataset.chart || '{}');

        var chart = new ApexCharts(chartElement, {
            series: chartData.series || [],
            labels: chartData.labels || [],
            chart: {
                height: 320,
                type: 'radialBar',
            },
            colors: [
                obtenerColor('--vz-info', '#299cdb'),
                obtenerColor('--vz-primary', '#6f42c1'),
                obtenerColor('--vz-success', '#0ab39c'),
                obtenerColor('--vz-warning', '#f7b84b'),
            ],
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: '36%',
                    },
                    track: {
                        background: 'rgba(135, 138, 153, 0.14)',
                    },
                    dataLabels: {
                        name: {
                            fontSize: '13px',
                        },
                        value: {
                            fontSize: '20px',
                            formatter: function (value) {
                                return Number(value).toFixed(0) + '%';
                            },
                        },
                        total: {
                            show: true,
                            label: 'Estados',
                            formatter: function () {
                                return 'Citas';
                            },
                        },
                    },
                },
            },
            legend: {
                show: true,
                position: 'bottom',
                labels: {
                    colors: obtenerColor('--vz-secondary-color', '#878a99'),
                },
            },
        });

        chart.render();
    }

    function inicializarGraficaDonut(selector, colores) {
        var chartElement = document.querySelector(selector);

        if (!chartElement || typeof ApexCharts === 'undefined') {
            return;
        }

        var chartData = JSON.parse(chartElement.dataset.chart || '{}');
        var series = chartData.series || [];
        var total = series.reduce(function (acumulado, valor) {
            return acumulado + Number(valor || 0);
        }, 0);

        var chart = new ApexCharts(chartElement, {
            series: series,
            labels: chartData.labels || [],
            chart: {
                height: 315,
                type: 'donut',
            },
            colors: colores,
            stroke: {
                width: 0,
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function () {
                                    return total;
                                },
                            },
                        },
                    },
                },
            },
            dataLabels: {
                enabled: false,
            },
            legend: {
                position: 'bottom',
                labels: {
                    colors: obtenerColor('--vz-secondary-color', '#878a99'),
                },
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return Number(value).toFixed(0) + ' registros';
                    },
                },
            },
        });

        chart.render();
    }

    function inicializarGraficasAdmin() {
        inicializarGraficaAdminModulos();
        inicializarGraficaAdminCitasRadial();

        inicializarGraficaDonut('[data-inicio-admin-solicitudes-chart]', [
            obtenerColor('--vz-warning', '#f7b84b'),
            obtenerColor('--vz-info', '#299cdb'),
            obtenerColor('--vz-danger', '#f06548'),
            obtenerColor('--vz-secondary', '#878a99'),
            obtenerColor('--vz-success', '#0ab39c'),
        ]);

        inicializarGraficaDonut('[data-inicio-admin-proveedores-chart]', [
            obtenerColor('--vz-warning', '#f7b84b'),
            obtenerColor('--vz-success', '#0ab39c'),
            obtenerColor('--vz-danger', '#f06548'),
        ]);
    }

    function inicializarTooltipsInicio() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
            return;
        }

        document.querySelectorAll('.inicio-admin [data-bs-toggle="tooltip"]').forEach(function (element) {
            new bootstrap.Tooltip(element);
        });
    }

    function inicializarMapaLaPazInicio() {
        if (typeof window.iniciarMapasLaPaz === 'function') {
            window.iniciarMapasLaPaz();
        }

        posicionarMarcadoresMapaAdmin();
        setTimeout(posicionarMarcadoresMapaAdmin, 250);
    }

    function posicionarMarcadoresMapaAdmin() {
        var contenedor = document.querySelector('[data-inicio-admin-mapa-wrap]');
        var mapaElemento = contenedor ? contenedor.querySelector('[data-mapa-lapaz]') : null;
        var marcadores = document.querySelectorAll('[data-inicio-admin-map-marker]');

        if (!contenedor || !mapaElemento || !marcadores.length) {
            return;
        }

        var anchoContenedor = mapaElemento.clientWidth;
        var altoContenedor = mapaElemento.clientHeight;

        if (!anchoContenedor || !altoContenedor) {
            return;
        }

        var mapa = {
            ancho: 800,
            alto: 969,
            minLongitud: -68.17165967746597,
            maxLongitud: -68.02106713527337,
            minLatitud: -16.61701039154325,
            maxLatitud: -16.43446565567419,
        };

        var proporcionMapa = mapa.ancho / mapa.alto;
        var proporcionContenedor = anchoContenedor / altoContenedor;
        var anchoMapa = anchoContenedor;
        var altoMapa = altoContenedor;
        var offsetX = 0;
        var offsetY = 0;

        if (proporcionContenedor > proporcionMapa) {
            anchoMapa = altoContenedor * proporcionMapa;
            offsetX = (anchoContenedor - anchoMapa) / 2;
        } else {
            altoMapa = anchoContenedor / proporcionMapa;
            offsetY = (altoContenedor - altoMapa) / 2;
        }

        marcadores.forEach(function (marcador) {
            var latitud = Number(marcador.dataset.latitud);
            var longitud = Number(marcador.dataset.longitud);

            if (!Number.isFinite(latitud) || !Number.isFinite(longitud)) {
                marcador.hidden = true;
                return;
            }

            var x = (longitud - mapa.minLongitud) / (mapa.maxLongitud - mapa.minLongitud);
            var y = (mapa.maxLatitud - latitud) / (mapa.maxLatitud - mapa.minLatitud);

            if (x < 0 || x > 1 || y < 0 || y > 1) {
                marcador.hidden = true;
                return;
            }

            marcador.hidden = false;
            marcador.style.left = (mapaElemento.offsetLeft + offsetX + (x * anchoMapa)) + 'px';
            marcador.style.top = (mapaElemento.offsetTop + offsetY + (y * altoMapa)) + 'px';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        inicializarGraficaCitasProveedor();
        inicializarGraficasProveedoresCliente();
        inicializarGraficasAdmin();
        inicializarTooltipsInicio();
        inicializarMapaLaPazInicio();
    });

    window.addEventListener('resize', posicionarMarcadoresMapaAdmin);
    window.addEventListener('mapa-lapaz:inicializado', posicionarMarcadoresMapaAdmin);
})();
