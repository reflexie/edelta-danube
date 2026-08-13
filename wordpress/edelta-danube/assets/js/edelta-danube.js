/**
 * edelta-danube — Chart.js v4 widget.
 */
(function () {
	'use strict';

	function init() {
		var wraps = document.querySelectorAll('.edelta-danube-chart-wrap');

		if (!wraps.length || typeof Chart === 'undefined') {
			return;
		}

		wraps.forEach(function (wrap) {
			var canvas = wrap.querySelector('canvas');
			if (!canvas) {
				return;
			}

			var dataEl = document.getElementById(canvas.id.replace('edelta-chart-', 'edelta-data-'));
			if (!dataEl) {
				return;
			}

			var d;
			try {
				d = JSON.parse(dataEl.textContent);
			} catch (e) {
				return;
			}

			new Chart(canvas.getContext('2d'), {
				type: 'line',
				data: {
					labels: d.labels || [],
					datasets: [
						{
							label: d.cote_label || 'Level [cm]',
							data: d.cota || [],
							borderColor: d.border || '#436741',
							backgroundColor: 'rgba(67,103,65,0.15)',
							fill: true,
							pointRadius: 1,
							pointHitRadius: 3,
							spanGaps: true,
							yAxisID: 'y'
						},
						{
							label: d.temp_label || 'Temp. [C]',
							data: d.temp || [],
							borderColor: '#f29732',
							borderDash: [5, 1],
							fill: false,
							pointRadius: 1,
							pointHitRadius: 3,
							yAxisID: 'y1'
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					interaction: { mode: 'index', intersect: false },
					plugins: {
						title: { display: true, text: d.title || '' },
						tooltip: { mode: 'index', intersect: false },
						legend: { display: true }
					},
					scales: {
						x: { ticks: { maxTicksLimit: 8 } },
						y: { type: 'linear', position: 'left', title: { display: true, text: 'cm' } },
						y1: {
							type: 'linear',
							position: 'right',
							grid: { drawOnChartArea: false },
							title: { display: true, text: 'C' }
						}
					}
				}
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
