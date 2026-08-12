<?php
/**
 * edelta-danube — public API reference implementation (read-only).
 *
 * Public, rate-limited endpoints for the Danube "Cote" dataset:
 *   GET /api/ports
 *   GET /api/measurements/latest?port_id=X
 *   GET /api/measurements/range?port_id=X&from=Y&to=Z[&limit=N]
 *
 * Requires PDO + MySQL. DB credentials come from the environment.
 * Response envelopes match https://api.edelta.ro so existing clients work.
 *
 * @license GPL-2.0-or-later
 * @see     https://github.com/reflexie/edelta-danube
 */

declare(strict_types=1);

const MIN_DATE     = '2011-01-01';
const MAX_WINDOW   = 366; // days
const MAX_LIMIT    = 365; // rows
const RATE_LIMIT   = 30;  // requests per IP per minute
const RATE_WINDOW  = 60;  // seconds

/** Send a JSON response and stop. */
function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function error(string $message, int $code, string $details = ''): void
{
    respond(['success' => false, 'error' => $message, 'code' => $code, 'details' => $details], $code);
}

/** Parse the request path as /api/<route>. */
function route(string $uri): string
{
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';
    $path = rtrim($path, '/');

    if (str_starts_with($path, '/api/')) {
        return substr($path, 5);
    }

    return '';
}

/** Connect to the data DB using environment credentials. */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = getenv('COTE_DB_HOST') ?: 'localhost';
        $name = getenv('COTE_DB_NAME') ?: 'cote';
        $user = getenv('COTE_DB_USER') ?: 'cote';
        $pass = getenv('COTE_DB_PASSWORD') ?: '';

        try {
            $pdo = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error('Internal server error', 500);
        }
    }

    return $pdo;
}

/** Simple per-IP rate limit backed by the api_requests table. */
function rateLimited(PDO $pdo): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $route = route($_SERVER['REQUEST_URI'] ?? '/');

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS api_requests (
            ip   VARCHAR(64)  NOT NULL,
            ts   INT UNSIGNED NOT NULL,
            route VARCHAR(32) NOT NULL,
            KEY k_ip_ts (ip, ts)
        ) ENGINE=InnoDB'
    );

    $now = time();

    $stmt = $pdo->prepare('DELETE FROM api_requests WHERE ts < :cutoff');
    $stmt->execute([':cutoff' => $now - RATE_WINDOW]);

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM api_requests WHERE ip = :ip AND ts >= :start');
    $stmt->execute([':ip' => $ip, ':start' => $now - RATE_WINDOW]);

    if ((int) $stmt->fetchColumn() >= RATE_LIMIT) {
        header('Retry-After: 60');
        error('Too Many Requests', 429, 'Rate limit exceeded');
    }

    $stmt = $pdo->prepare('INSERT INTO api_requests (ip, ts, route) VALUES (:ip, :now, :route)');
    $stmt->execute([':ip' => $ip, ':now' => $now, ':route' => $route]);
}

/** All valid port ids (1..23). */
function portId(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id_locdunare AS id, nume_locdunare AS name FROM cote_loc WHERE id_locdunare = :id'
    );
    $stmt->execute([':id' => $id]);

    $row = $stmt->fetch();

    return $row ?: null;
}

/** The most recent measurement for a port, or null. */
function latestMeasurement(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT date, cota, temperatura FROM cote_data
         WHERE id_locdunare = :id
         ORDER BY date DESC LIMIT 1'
    );
    $stmt->execute([':id' => $id]);

    $row = $stmt->fetch();

    return $row ?: null;
}

/** Measurements for a port within a window, ascending. */
function rangeMeasurements(PDO $pdo, int $id, string $from, string $to, int $limit): array
{
    $stmt = $pdo->prepare(
        'SELECT date, cota, temperatura FROM cote_data
         WHERE id_locdunare = :id AND date >= :from AND date <= :to
         ORDER BY date ASC
         LIMIT :limit'
    );
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':from', $from, PDO::PARAM_STR);
    $stmt->bindValue(':to', $to, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/** Validate a Y-m-d date string. */
function validDate(string $d): bool
{
    $t = DateTime::createFromFormat('Y-m-d', $d);

    return $t !== false && $t->format('Y-m-d') === $d;
}

// ---------------------------------------------------------------------------

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'GET') {
    error('Method Not Allowed', 405);
}

$route = route($_SERVER['REQUEST_URI'] ?? '/');

if (!in_array($route, ['ports', 'measurements/latest', 'measurements/range'], true)) {
    error('Not Found', 404);
}

$pdo = db();
rateLimited($pdo);

switch ($route) {
    case 'ports':
        $rows = $pdo->query('SELECT id_locdunare AS id, nume_locdunare AS name FROM cote_loc ORDER BY id_locdunare')->fetchAll();
        respond(['success' => true, 'data' => ['ports' => $rows]]);
        break;

    case 'measurements/latest':
        $portId = (int) ($_GET['port_id'] ?? 0);
        $port   = portId($pdo, $portId);

        if ($port === null) {
            error('Bad Request', 400, 'Invalid or missing port_id');
        }

        $measurement = latestMeasurement($pdo, $portId);

        if ($measurement === null) {
            error('Not Found', 404, 'No measurements for this port');
        }

        respond([
            'success' => true,
            'data'    => [
                'measurement' => $measurement,
                'meta'        => ['port_id' => $port['id'], 'port_name' => $port['name']],
            ],
        ]);
        break;

    case 'measurements/range':
        $portId = (int) ($_GET['port_id'] ?? 0);
        $port   = portId($pdo, $portId);

        if ($port === null) {
            error('Bad Request', 400, 'Invalid or missing port_id');
        }

        $from = $_GET['from'] ?? '';
        $to   = $_GET['to']   ?? '';

        if (!validDate($from) || !validDate($to)) {
            error('Bad Request', 400, 'Both "from" and "to" parameters are required (YYYY-MM-DD)');
        }

        if ($from < MIN_DATE) {
            $from = MIN_DATE;
        }

        if (($to <= $from) || ((strtotime($to) - strtotime($from)) / 86400 > MAX_WINDOW)) {
            error('Bad Request', 400, 'Date window must be between 1 and ' . MAX_WINDOW . ' days');
        }

        $limit = max(1, min((int) ($_GET['limit'] ?? MAX_LIMIT), MAX_LIMIT));

        $rows = rangeMeasurements($pdo, $portId, $from, $to, $limit);

        respond([
            'success' => true,
            'data'    => [
                'measurements' => $rows,
                'meta'         => [
                    'port_id'   => $port['id'],
                    'port_name' => $port['name'],
                    'from'      => $from,
                    'to'        => $to,
                    'count'     => count($rows),
                ],
            ],
        ]);
        break;
}
