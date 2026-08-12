<?php

namespace EdeltaDunare\Module\EdeltaDunare\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;

/**
 * Helper for mod_edelta_dunare.
 *
 * Fetches (and caches) water level data from the PUBLIC api.edelta.ro
 * endpoints — no API key is required and none is ever shipped.
 *
 * @since 1.0.0
 */
class EdeltaDunareHelper
{
    /**
     * HTTP timeout in seconds for the outbound API request.
     *
     * @var integer
     * @since 1.0.0
     */
    private const HTTP_TIMEOUT = 10;

    /**
     * Public API serves at most this many recent days.
     *
     * @var integer
     * @since 1.1.0
     */
    private const PUBLIC_MAX_DAYS = 30;

    /**
     * Fetch recent measurements for a port and return a normalized payload.
     *
     * @param   integer  $port       Port id (1..23)
     * @param   integer  $days       Number of recent days requested by the admin
     * @param   string   $apiBase    API base URL (no trailing slash)
     * @param   integer  $cacheTime  Cache lifetime in seconds
     *
     * @return  array  ['success'=>bool, 'port'=>string, 'days'=>int, 'rows'=>array, 'error'=>string]
     *
     * @since   1.0.0
     */
    public function getData(int $port, int $days, string $apiBase, int $cacheTime = 600): array
    {
        $daysRequested = max(1, min(365, $days));
        $daysFetched   = min($daysRequested, self::PUBLIC_MAX_DAYS);

        $id = 'recent_' . $port . '_' . $daysFetched;

        try {
            $cache = Factory::getCache('mod_edelta_dunare', 'callback');
            $cache->options['lifetime'] = max(60, $cacheTime);

            $payload = $cache->get(
                [static::class, 'fetchRecent'],
                [$apiBase, $port, $daysFetched],
                $id
            );
        } catch (\Throwable $e) {
            return ['success' => false, 'port' => '', 'days' => 0, 'rows' => [], 'error' => $e->getMessage()];
        }

        if (!is_array($payload) || empty($payload['success'])) {
            return [
                'success' => false,
                'port'    => $payload['port'] ?? '',
                'days'    => $payload['days'] ?? 0,
                'rows'    => [],
                'error'   => $payload['error'] ?? 'Invalid API payload',
            ];
        }

        return $payload;
    }

    /**
     * Callback used by the cache controller: performs the actual API request.
     *
     * @param   string   $apiBase  API base URL (no trailing slash)
     * @param   integer  $port     Port id (1..23)
     * @param   integer  $days     Days to fetch (already clamped server-side)
     *
     * @return  array  Normalized payload
     *
     * @since   1.1.0
     */
    public static function fetchRecent(string $apiBase, int $port, int $days): array
    {
        $url = $apiBase . '/api/measurements/recent?port_id=' . $port . '&days=' . $days;

        try {
            $http = HttpFactory::getHttp(['timeout' => self::HTTP_TIMEOUT]);
            $response = $http->get($url, ['Accept' => 'application/json'], self::HTTP_TIMEOUT);

            if ($response->getStatusCode() !== 200) {
                return ['success' => false, 'port' => '', 'days' => $days, 'rows' => [], 'error' => 'API request failed (HTTP ' . $response->getStatusCode() . ')'];
            }

            $json = json_decode((string) $response->getBody(), true);

            if (!is_array($json) || empty($json['success'])) {
                return ['success' => false, 'port' => '', 'days' => $days, 'rows' => [], 'error' => 'Invalid API response'];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'port' => '', 'days' => $days, 'rows' => [], 'error' => $e->getMessage()];
        }

        $rows = [];

        foreach (($json['data']['measurements'] ?? []) as $m) {
            $rows[] = [
                'date'        => $m['date'],
                'cota'        => $m['cota'],
                'temperatura' => $m['temperatura'],
                'date_rom'    => self::dataRom($m['date']),
            ];
        }

        return [
            'success' => true,
            'port'    => trim((string) ($json['data']['meta']['port_name'] ?? '')),
            'days'    => (int) ($json['data']['meta']['days'] ?? $days),
            'rows'    => $rows,
            'error'   => '',
        ];
    }

    /**
     * Format a Y-m-d date as "d-Mon" using Romanian month abbreviations.
     *
     * @param   string  $date  The date (Y-m-d)
     *
     * @return  string  e.g. "12-Aug"
     *
     * @since   1.0.0
     */
    public static function dataRom(string $date): string
    {
        $months = [
            1  => 'Ian', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mai', 6 => 'Iun',
            7  => 'Iul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Noi', 12 => 'Dec',
        ];

        $parts = explode('-', $date);

        if (count($parts) < 3) {
            return $date;
        }

        return $parts[2] . '-' . ($months[(int) $parts[1]] ?? $parts[1]);
    }
}
