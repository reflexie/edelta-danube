<?php
/**
 * edelta-danube — public API client.
 *
 * Fetches (and caches) water level data from the PUBLIC api.edelta.ro endpoints.
 * No API key is required and none is ever shipped or exposed.
 *
 * @package edelta-danube
 */

defined( 'ABSPATH' ) || exit;

/**
 * Public API client with transient caching.
 */
class Edelta_API {

	/**
	 * Maximum days the public API serves.
	 */
	const PUBLIC_MAX_DAYS = 30;

	/**
	 * Fetch recent measurements for a port and return a normalized payload.
	 *
	 * @param int    $port       Port id (1..23).
	 * @param int    $days       Number of recent days (clamped to 30).
	 * @param string $api_base   API base URL (no trailing slash).
	 * @param int    $cache_time Cache lifetime in seconds.
	 *
	 * @return array{success:bool,port:string,days:int,rows:array,error:string}
	 */
	public static function get_recent( $port, $days, $api_base, $cache_time ) {
		$port     = max( 1, min( 23, (int) $port ) );
		$days     = max( 1, min( self::PUBLIC_MAX_DAYS, (int) $days ) );
		$api_base = rtrim( esc_url_raw( $api_base ), '/' );
		$api_base = $api_base ?: 'https://api.edelta.ro';

		$key = 'edelta_danube_' . $port . '_' . $days;
		$cached = get_transient( $key );

		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$url = $api_base . '/api/measurements/recent?port_id=' . $port . '&days=' . $days;

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return array(
				'success' => false,
				'port'    => '',
				'days'    => $days,
				'rows'    => array(),
				'error'   => is_wp_error( $response ) ? $response->get_error_message() : 'API request failed (HTTP ' . wp_remote_retrieve_response_code( $response ) . ')',
			);
		}

		$json = json_decode( (string) wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $json ) || empty( $json['success'] ) ) {
			return array(
				'success' => false,
				'port'    => '',
				'days'    => $days,
				'rows'    => array(),
				'error'   => 'Invalid API response',
			);
		}

		$rows = array();

		foreach ( (array) ( $json['data']['measurements'] ?? array() ) as $m ) {
			$rows[] = array(
				'date'        => sanitize_text_field( $m['date'] ?? '' ),
				'cota'        => isset( $m['cota'] ) ? (float) $m['cota'] : null,
				'temperatura' => isset( $m['temperatura'] ) ? (string) $m['temperatura'] : '',
				'date_rom'    => self::data_rom( $m['date'] ?? '' ),
			);
		}

		$data = array(
			'success' => true,
			'port'    => sanitize_text_field( $json['data']['meta']['port_name'] ?? '' ),
			'days'    => (int) ( $json['data']['meta']['days'] ?? $days ),
			'rows'    => $rows,
			'error'   => '',
		);

		set_transient( $key, $data, max( 60, (int) $cache_time ) );

		return $data;
	}

	/**
	 * Format a Y-m-d date as "d-Mon" using Romanian month abbreviations.
	 *
	 * @param string $date The date (Y-m-d).
	 *
	 * @return string e.g. "12-Aug".
	 */
	public static function data_rom( $date ) {
		$months = array(
			1  => 'Ian', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mai', 6 => 'Iun',
			7  => 'Iul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Noi', 12 => 'Dec',
		);

		$parts = explode( '-', (string) $date );

		if ( count( $parts ) < 3 ) {
			return (string) $date;
		}

		return $parts[2] . '-' . ( $months[ (int) $parts[1] ] ?? $parts[1] );
	}
}
