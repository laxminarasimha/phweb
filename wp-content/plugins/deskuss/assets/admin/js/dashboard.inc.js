/**
 * Deskuss Dashboard - Chart.js based plotting
 *
 * Exposes:
 *   $.dskDrawActivityChart(canvasId, plots, stateLabels, eventColors)
 *   $.dskDrawDeptChart(canvasId, deptData)
 */
(function ($) {

    var activityChartInstance = null;
    var deptChartInstance = null;

    function colorWithAlpha(hex, alpha) {
        if (!hex) return 'rgba(120,120,120,' + alpha + ')';
        hex = hex.replace('#', '');
        if (hex.length === 3) {
            hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
        }
        var r = parseInt(hex.substring(0,2), 16);
        var g = parseInt(hex.substring(2,4), 16);
        var b = parseInt(hex.substring(4,6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    function formatDateLabel(ts) {
        var d = new Date(ts * 1000);
        var m = d.getMonth() + 1;
        var day = d.getDate();
        return (m < 10 ? '0' : '') + m + '/' + (day < 10 ? '0' : '') + day;
    }

    /**
     * Render the Ticket Activity line chart.
     * plots = { times: [epoch_seconds,...], plots: { created: [...], closed: [...], ... }, events: [...] }
     * stateLabels = { created: 'Created', ... }
     * eventColors = { created: '#5cb85c', ... }
     */
    $.dskDrawActivityChart = function(canvasId, plots, stateLabels, eventColors) {
        var canvas = document.getElementById(canvasId);
        if (!canvas || typeof Chart === 'undefined') return;

        if (activityChartInstance) {
            activityChartInstance.destroy();
            activityChartInstance = null;
        }

        var times = plots.times || [];
        var dataPlots = plots.plots || {};
        var events = plots.events || [];

        var labels = times.map(formatDateLabel);

        var datasets = [];
        for (var i = 0; i < events.length; i++) {
            var state = events[i];
            var label = (stateLabels && stateLabels[state]) ? stateLabels[state] : state;
            var color = (eventColors && eventColors[state]) ? eventColors[state] : '#999';
            datasets.push({
                label: label,
                data: dataPlots[state] || [],
                borderColor: color,
                backgroundColor: colorWithAlpha(color, 0.12),
                borderWidth: 2,
                fill: true,
                tension: 0.25,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: color,
            });
        }

        activityChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 10, font: { size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 12 },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 4
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { font: { size: 11 }, color: '#888', maxRotation: 0, autoSkip: true, maxTicksLimit: 12 }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { font: { size: 11 }, color: '#888', precision: 0 }
                    }
                }
            }
        });
    };

    /**
     * Render the Department distribution doughnut chart.
     * deptData = [{id, name, count}, ...]
     */
    $.dskDrawDeptChart = function(canvasId, deptData) {
        var canvas = document.getElementById(canvasId);
        if (!canvas || typeof Chart === 'undefined') return;

        if (deptChartInstance) {
            deptChartInstance.destroy();
            deptChartInstance = null;
        }

        var labels = [];
        var data = [];
        var palette = [
            '#337ab7', '#5cb85c', '#f0ad4e', '#d9534f', '#5bc0de',
            '#9b59b6', '#34495e', '#16a085', '#e67e22', '#2ecc71',
            '#8e44ad', '#1abc9c'
        ];
        var colors = [];

        for (var i = 0; i < deptData.length; i++) {
            if (deptData[i].count > 0) {
                labels.push(deptData[i].name);
                data.push(deptData[i].count);
                colors.push(palette[i % palette.length]);
            }
        }

        if (data.length === 0) {
            // No data - render an empty placeholder
            var ctx = canvas.getContext('2d');
            ctx.font = '12px sans-serif';
            ctx.fillStyle = '#888';
            ctx.textAlign = 'center';
            ctx.fillText('No open tickets', canvas.width / 2, canvas.height / 2);
            return;
        }

        deptChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 8, font: { size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 12 },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 4,
                        callbacks: {
                            label: function(ctx) {
                                var total = ctx.dataset.data.reduce(function(a, b){ return a + b; }, 0);
                                var pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    };

})(window.jQuery);
