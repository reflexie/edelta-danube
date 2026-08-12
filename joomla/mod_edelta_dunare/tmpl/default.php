<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var $module \stdClass */
/** @var $params \Joomla\Registry\Registry */
/** @var $app \Joomla\CMS\Application\SiteApplication */

$doc = $app->getDocument();

$port    = (int) $module_port;
$days    = (int) $module_days;
$display = (string) $module_display;
$border  = (string) $module_border;
$result  = $module_result;
$moreUrl = (string) $module_more_url;

// The public API serves at most 30 days; show the actual days returned.
$daysShown     = (int) ($result['days'] ?? $days);
$showBottomLink = $moreUrl !== '';

$showChart = ($display === 'chart' || $display === 'both');
$showTable = ($display === 'table' || $display === 'both');

$uid = (int) $module->id;

// Assets (local, self-contained)
$doc->addStyleSheet(Uri::base() . 'modules/mod_edelta_dunare/assets/css/mod_edelta_dunare.css');

if ($showChart) {
    $doc->addScript(Uri::base() . 'modules/mod_edelta_dunare/assets/js/Chart.min.js');
}

$rows   = $result['rows'] ?? [];
$ok     = !empty($result['success']);
$error  = $result['error'] ?? '';
$portNm = trim((string) ($result['port'] ?? ''));

// Chart data embedded as JSON (XSS-safe)
$jsLabels = json_encode(array_map(static function ($r) {
    return $r['date_rom'];
}, $rows));
$jsCota   = json_encode(array_map(static function ($r) {
    return $r['cota'] !== null ? (float) $r['cota'] : null;
}, $rows));
$jsTemp   = json_encode(array_map(static function ($r) {
    $t = $r['temperatura'] ?? '';

    return $t !== '' ? (float) str_replace(',', '.', $t) : null;
}, $rows));
$jsPort   = json_encode($portNm);
$jsBorder = json_encode($border);
$jsTitle  = json_encode(Text::sprintf('MOD_EDELTA_DUNARE_LAST_DAYS', $daysShown));
?>
<div class="mod-edelta-dunare" id="mod-edelta-dunare-<?php echo $uid; ?>">
	<?php if (!$ok): ?>
		<div class="mod-edelta-dunare-error">
			<?php echo Text::_('MOD_EDELTA_DUNARE_ERROR'); ?>
			<?php if ($error !== ''): ?><small class="text-muted"> (<?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>)</small><?php endif; ?>
		</div>
	<?php else: ?>
		<div class="mod-edelta-dunare-info">
			<?php if ($portNm !== ''): ?><strong><?php echo htmlspecialchars($portNm, ENT_QUOTES, 'UTF-8'); ?></strong><?php endif; ?>
			<span><?php echo Text::sprintf('MOD_EDELTA_DUNARE_LAST_DAYS', $daysShown); ?></span>
		</div>

		<?php if ($showChart): ?>
		<div class="mod-edelta-dunare-chart-wrap">
			<canvas id="edelta-chart-<?php echo $uid; ?>"></canvas>
		</div>
		<?php endif; ?>

		<?php if ($showTable): ?>
		<div class="mod-edelta-dunare-table-wrap">
			<table class="mod-edelta-dunare-table">
				<thead>
					<tr>
						<th><?php echo Text::_('DATA'); ?></th>
						<th><?php echo Text::_('COTE'); ?> [cm]</th>
						<th><?php echo Text::_('TEMP'); ?> [C]</th>
					</tr>
				</thead>
				<tbody>
				<?php if (empty($rows)): ?>
					<tr>
						<td colspan="3" class="text-center"><?php echo Text::_('MOD_EDELTA_DUNARE_NO_DATA'); ?></td>
					</tr>
				<?php else: ?>
					<?php foreach ($rows as $r): ?>
						<tr>
							<td><?php echo htmlspecialchars($r['date_rom'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td><?php echo htmlspecialchars((string) $r['cota'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td><?php echo htmlspecialchars((string) $r['temperatura'], ENT_QUOTES, 'UTF-8'); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>

		<?php if ($showChart): ?>
		<script>
		(function() {
			var canvas = document.getElementById('edelta-chart-<?php echo $uid; ?>');
			if (!canvas || typeof Chart === 'undefined') {
				return;
			}

			var labels = <?php echo $jsLabels; ?>;
			var cota   = <?php echo $jsCota; ?>;
			var temp   = <?php echo $jsTemp; ?>;
			var port   = <?php echo $jsPort; ?>;
			var title  = <?php echo $jsTitle; ?>;

			new Chart(canvas.getContext('2d'), {
				type: 'line',
				data: {
					labels: labels,
					datasets: [
						{
							label: <?php echo json_encode(Text::_('COTE') . ' [cm]'); ?>,
							data: cota,
							fill: true,
							borderColor: <?php echo $jsBorder; ?>,
							backgroundColor: 'rgba(67,103,65,0.15)',
							pointRadius: 1,
							pointHitRadius: 3,
							spanGaps: true,
							yAxisID: 'y-axis-1'
						},
						{
							label: <?php echo json_encode(Text::_('TEMP') . ' [C]'); ?>,
							data: temp,
							fill: false,
							borderDash: [5, 1],
							borderColor: '#f29732',
							pointRadius: 1,
							pointHitRadius: 3,
							yAxisID: 'y-axis-2'
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					title: {
						display: true,
						text: (port ? port + ' — ' : '') + title,
						fontSize: 14
					},
					tooltips: { mode: 'label' },
					hover: { mode: 'label' },
					scales: {
						xAxes: [{
							display: true,
							ticks: {
								userCallback: function(value, index) {
									return index % 5 === 0 ? value : '';
								}
							}
						}],
						yAxes: [
							{
								type: 'linear',
								display: true,
								position: 'left',
								id: 'y-axis-1',
								scaleLabel: { display: true, labelString: 'cm' }
							},
							{
								type: 'linear',
								display: true,
								position: 'right',
								id: 'y-axis-2',
								gridLines: { drawOnChartArea: false },
								scaleLabel: { display: true, labelString: 'C' }
							}
						]
					}
				}
			});
		})();
		</script>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ($showBottomLink): ?>
	<div class="mod-edelta-dunare-more">
		<a href="<?php echo htmlspecialchars($moreUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
			<?php echo Text::_('MOD_EDELTA_DUNARE_MORE'); ?>
		</a>
	</div>
	<?php endif; ?>
</div>
