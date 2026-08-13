<?php
/**
 * Plugin Name:       edelta Danube Levels
 * Plugin URI:        https://github.com/reflexie/edelta-danube
 * Description:       Displays Danube water level and water temperature (chart and/or table) using the public api.edelta.ro API. Free plugin, no API key required.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            edelta-danube contributors
 * Author URI:        https://edelta.ro
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       edelta-danube
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'EDELTA_DANUBE_VERSION', '1.0.0' );
define( 'EDELTA_DANUBE_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDELTA_DANUBE_URL', plugin_dir_url( __FILE__ ) );

require_once EDELTA_DANUBE_DIR . 'includes/class-edelta-api.php';
require_once EDELTA_DANUBE_DIR . 'includes/class-edelta-settings.php';
require_once EDELTA_DANUBE_DIR . 'includes/class-edelta-shortcode.php';

/**
 * Load the plugin text domain.
 */
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'edelta-danube', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

new Edelta_Settings();
new Edelta_Shortcode();
