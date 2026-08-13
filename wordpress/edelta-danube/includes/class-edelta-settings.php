<?php
/**
 * edelta-danube — plugin settings (global defaults).
 *
 * @package edelta-danube
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the settings page and option defaults.
 */
class Edelta_Settings {

	/**
	 * Option name.
	 */
	const OPTION = 'edelta_danube_options';

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
	}

	/**
	 * Enqueue the WP color picker on the settings page.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function admin_assets( $hook ) {
		if ( 'settings_page_edelta-danube' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_add_inline_script(
			'wp-color-picker',
			'jQuery(function($){ if ($.fn.wpColorPicker) { $(".edelta-color").wpColorPicker(); } });'
		);
	}

	/**
	 * Default option values.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'port'      => '2',
			'days'      => '30',
			'display'   => 'both',
			'border'    => '#436741',
			'api_base'  => 'https://api.edelta.ro',
			'backlink'  => '1',
			'cache_time'=> '600',
		);
	}

	/**
	 * Merged options.
	 *
	 * @return array
	 */
	public static function options() {
		return wp_parse_args( get_option( self::OPTION, array() ), self::defaults() );
	}

	/**
	 * Add the settings page.
	 */
	public function add_menu() {
		add_options_page(
			__( 'edelta Danube Levels', 'edelta-danube' ),
			__( 'edelta Danube Levels', 'edelta-danube' ),
			'manage_options',
			'edelta-danube',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register the setting and its sanitize callback.
	 */
	public function register_settings() {
		register_setting( self::OPTION, self::OPTION, array( $this, 'sanitize' ) );
	}

	/**
	 * Sanitize submitted options.
	 *
	 * @param array $input Raw input.
	 *
	 * @return array
	 */
	public function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();

		$input['port']       = isset( $input['port'] ) ? absint( $input['port'] ) : 2;
		$input['days']       = isset( $input['days'] ) ? absint( $input['days'] ) : 30;
		$input['display']    = isset( $input['display'] ) && in_array( $input['display'], array( 'chart', 'table', 'both' ), true ) ? $input['display'] : 'both';
		$input['border']     = isset( $input['border'] ) ? sanitize_hex_color( $input['border'] ) : '#436741';
		$input['api_base']   = isset( $input['api_base'] ) ? esc_url_raw( $input['api_base'] ) : 'https://api.edelta.ro';
		$input['backlink']   = ! empty( $input['backlink'] ) ? '1' : '0';
		$input['cache_time'] = isset( $input['cache_time'] ) ? absint( $input['cache_time'] ) : 600;

		return $input;
	}

	/**
	 * Render the settings page.
	 */
	public function render_page() {
		$o = self::options();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'edelta Danube Levels', 'edelta-danube' ); ?></h1>
			<p>
				<?php
				printf(
					/* translators: %s: shortcode example */
					esc_html__( 'Use the shortcode %s anywhere on your site.', 'edelta-danube' ),
					'<code>[edelta_danube]</code>'
				);
				?>
			</p>
			<form method="post" action="options.php">
				<?php settings_fields( self::OPTION ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="edelta_port"><?php esc_html_e( 'Select the port', 'edelta-danube' ); ?></label></th>
						<td>
							<select id="edelta_port" name="<?php echo esc_attr( self::OPTION . '[port]' ); ?>">
								<?php foreach ( range( 1, 23 ) as $p ) : ?>
									<option value="<?php echo esc_attr( $p ); ?>" <?php selected( (int) $o['port'], $p ); ?>><?php echo esc_html( $p ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'The Danube port (1..23) to display data for.', 'edelta-danube' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="edelta_days"><?php esc_html_e( 'Days', 'edelta-danube' ); ?></label></th>
						<td>
							<select id="edelta_days" name="<?php echo esc_attr( self::OPTION . '[days]' ); ?>">
								<?php foreach ( array( 7, 14, 30 ) as $d ) : ?>
									<option value="<?php echo esc_attr( $d ); ?>" <?php selected( (int) $o['days'], $d ); ?>><?php echo esc_html( $d ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Number of recent days to show (the public API serves at most 30).', 'edelta-danube' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="edelta_display"><?php esc_html_e( 'Display', 'edelta-danube' ); ?></label></th>
						<td>
							<select id="edelta_display" name="<?php echo esc_attr( self::OPTION . '[display]' ); ?>">
								<option value="chart" <?php selected( $o['display'], 'chart' ); ?>><?php esc_html_e( 'Chart', 'edelta-danube' ); ?></option>
								<option value="table" <?php selected( $o['display'], 'table' ); ?>><?php esc_html_e( 'Table', 'edelta-danube' ); ?></option>
								<option value="both"  <?php selected( $o['display'], 'both' ); ?>><?php esc_html_e( 'Chart and table', 'edelta-danube' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="edelta_border"><?php esc_html_e( 'Chart line color', 'edelta-danube' ); ?></label></th>
						<td>
							<input type="text" id="edelta_border" class="edelta-color" name="<?php echo esc_attr( self::OPTION . '[border]' ); ?>" value="<?php echo esc_attr( $o['border'] ); ?>" data-default-color="#436741" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="edelta_api_base"><?php esc_html_e( 'API base URL', 'edelta-danube' ); ?></label></th>
						<td>
							<input type="url" id="edelta_api_base" class="regular-text" name="<?php echo esc_attr( self::OPTION . '[api_base]' ); ?>" value="<?php echo esc_attr( $o['api_base'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable backlink to edelta.ro?', 'edelta-danube' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION . '[backlink]' ); ?>" value="1" <?php checked( '1', $o['backlink'] ); ?> />
								<?php esc_html_e( 'Show a small link to edelta.ro at the bottom of the widget.', 'edelta-danube' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="edelta_cache"><?php esc_html_e( 'Cache time (seconds)', 'edelta-danube' ); ?></label></th>
						<td>
							<input type="number" id="edelta_cache" name="<?php echo esc_attr( self::OPTION . '[cache_time]' ); ?>" value="<?php echo esc_attr( $o['cache_time'] ); ?>" min="60" step="60" />
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
