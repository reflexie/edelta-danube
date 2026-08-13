<?php
/**
 * edelta-danube — shortcode renderer.
 *
 * @package edelta-danube
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the [edelta_danube ...] shortcode.
 */
class Edelta_Shortcode {

	/**
	 * Whether the frontend assets were enqueued already.
	 *
	 * @var bool
	 */
	private static $assets_loaded = false;

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_shortcode( 'edelta_danube', array( $this, 'render' ) );
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array|string $atts Shortcode attributes.
	 *
	 * @return string Widget HTML.
	 */
	public function render( $atts ) {
		$o = Edelta_Settings::options();

		$atts = shortcode_atts(
			array(
				'port'     => $o['port'],
				'days'     => $o['days'],
				'display'  => $o['display'],
				'border'   => $o['border'],
				'api_base' => $o['api_base'],
				'backlink' => $o['backlink'],
				'cache'    => $o['cache_time'],
			),
			$atts,
			'edelta_danube'
		);

		$port     = max( 1, min( 23, (int) $atts['port'] ) );
		$days     = max( 1, min( Edelta_API::PUBLIC_MAX_DAYS, (int) $atts['days'] ) );
		$display  = in_array( $atts['display'], array( 'chart', 'table', 'both' ), true ) ? $atts['display'] : 'both';
		$border   = sanitize_hex_color( $atts['border'] ) ?: '#436741';
		$api_base = rtrim( esc_url_raw( $atts['api_base'] ), '/' ) ?: 'https://api.edelta.ro';
		$backlink = ! empty( $atts['backlink'] );
		$cache    = max( 60, (int) $atts['cache'] );

		$data  = Edelta_API::get_recent( $port, $days, $api_base, $cache );
		$ok    = ! empty( $data['success'] );
		$error = isset( $data['error'] ) ? (string) $data['error'] : '';

		self::load_assets();

		static $uid_counter = 0;
		$uid = ++$uid_counter;

		$show_chart = ( 'chart' === $display || 'both' === $display );
		$show_table = ( 'table' === $display || 'both' === $display );

		$port_name = trim( (string) ( $data['port'] ?? '' ) );
		$days_shown = (int) ( $data['days'] ?? $days );
		$rows       = (array) ( $data['rows'] ?? array() );

		$labels = array();
		$cota   = array();
		$temp   = array();

		foreach ( $rows as $r ) {
			$labels[] = $r['date_rom'];
			$cota[]   = ( null !== $r['cota'] ) ? (float) $r['cota'] : null;

			$t = $r['temperatura'] ? str_replace( ',', '.', $r['temperatura'] ) : '';
			$temp[] = ( '' !== $t ) ? (float) $t : null;
		}

		$last_days = sprintf( __( 'Last %s days', 'edelta-danube' ), $days_shown );
		$title     = ( $port_name ? $port_name . ' — ' : '' ) . $last_days;

		$chart_json = wp_json_encode(
			array(
				'labels'     => $labels,
				'cota'       => $cota,
				'temp'       => $temp,
				'port'       => $port_name,
				'title'      => $title,
				'border'     => $border,
				'cote_label' => __( 'Level', 'edelta-danube' ) . ' [cm]',
				'temp_label' => __( 'Temp.', 'edelta-danube' ) . ' [C]',
			),
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
		);

		ob_start();
		?>
		<div class="edelta-danube" id="edelta-danube-<?php echo esc_attr( $uid ); ?>">
			<?php if ( ! $ok ) : ?>
				<div class="edelta-danube-error">
					<?php esc_html_e( 'Unable to load data.', 'edelta-danube' ); ?>
					<?php if ( $error ) : ?><small class="edelta-danube-error-detail"> (<?php echo esc_html( $error ); ?>)</small><?php endif; ?>
				</div>
			<?php else : ?>
				<div class="edelta-danube-info">
					<?php if ( $port_name ) : ?><strong><?php echo esc_html( $port_name ); ?></strong><?php endif; ?>
					<span><?php echo esc_html( $last_days ); ?></span>
				</div>

				<?php if ( $show_chart ) : ?>
					<div class="edelta-danube-chart-wrap"><canvas id="edelta-chart-<?php echo esc_attr( $uid ); ?>"></canvas></div>
				<?php endif; ?>

				<?php if ( $show_table ) : ?>
					<div class="edelta-danube-table-wrap">
						<table class="edelta-danube-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Date', 'edelta-danube' ); ?></th>
									<th><?php esc_html_e( 'Level', 'edelta-danube' ); ?> [cm]</th>
									<th><?php esc_html_e( 'Temp.', 'edelta-danube' ); ?> [C]</th>
								</tr>
							</thead>
							<tbody>
							<?php if ( empty( $rows ) ) : ?>
								<tr><td colspan="3" class="edelta-danube-empty"><?php esc_html_e( 'No data available.', 'edelta-danube' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $rows as $r ) : ?>
									<tr>
										<td><?php echo esc_html( $r['date_rom'] ); ?></td>
										<td><?php echo esc_html( null !== $r['cota'] ? $r['cota'] : '' ); ?></td>
										<td><?php echo esc_html( $r['temperatura'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $backlink ) : ?>
				<div class="edelta-danube-more">
					<a href="https://edelta.ro" target="_blank" rel="noopener"><?php esc_html_e( 'More data on edelta.ro', 'edelta-danube' ); ?></a>
				</div>
			<?php endif; ?>

			<?php if ( $show_chart && $ok ) : ?>
				<script type="application/json" id="edelta-data-<?php echo esc_attr( $uid ); ?>"><?php echo $chart_json; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON encoded above. ?></script>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Enqueue frontend assets once per page.
	 */
	private static function load_assets() {
		if ( self::$assets_loaded ) {
			return;
		}

		self::$assets_loaded = true;

		wp_enqueue_style(
			'edelta-danube',
			EDELTA_DANUBE_URL . 'assets/css/edelta-danube.css',
			array(),
			EDELTA_DANUBE_VERSION
		);

		wp_enqueue_script(
			'edelta-chartjs',
			EDELTA_DANUBE_URL . 'assets/js/chart.umd.min.js',
			array(),
			'4.4.9',
			true
		);

		wp_enqueue_script(
			'edelta-danube',
			EDELTA_DANUBE_URL . 'assets/js/edelta-danube.js',
			array( 'edelta-chartjs' ),
			EDELTA_DANUBE_VERSION,
			true
		);
	}
}
