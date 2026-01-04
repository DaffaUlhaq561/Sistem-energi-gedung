<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

function ok($data) {
    echo json_encode($data);
    exit;
}

function fail($code, $message, $error = null) {
    http_response_code($code);
    echo json_encode(['message' => $message, 'error' => $error]);
    exit;
}

function query_all($conn, $sql, $types = '', $params = []) {
    if ($types && count($params) !== strlen($types)) {
        throw new Exception('Parameter tipe tidak sesuai');
    }
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function query_one($conn, $sql, $types = '', $params = []) {
    $rows = query_all($conn, $sql, $types, $params);
    return $rows[0] ?? null;
}

function simulated_energy_value($timestamp, $phaseOffset = 0.0) {
    // Pola dasar: sinusoidal + noise deterministik (berbasis timestamp) agar stabil antar request
    $base = 45 + 35 * sin(($timestamp / 180) + $phaseOffset);
    $hash = crc32(date('Y-m-d H:i:s', $timestamp));
    $noise = (($hash % 1000) / 1000) * 10 - 5; // -5 .. +5
    $value = $base + $noise;
    return max(5, round($value, 2));
}

try {
    $route = $_GET['route'] ?? '';
    if (!$route && php_sapi_name() === 'cli' && !empty($argv)) {
        foreach ($argv as $arg) {
            if (strpos($arg, '=') !== false) {
                parse_str($arg, $parsed);
                if (isset($parsed['route'])) {
                    $route = $parsed['route'];
                    break;
                }
            }
        }
    }
    $conn = db();

    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    if ($route === 'health' || $requestUri === '/backend/api.php/health') {
        try {
            $conn->query('SELECT 1');
            ok(['status' => 'ok']);
        } catch (Exception $e) {
            fail(500, 'error', $e->getMessage());
        }
    }

    if ($route === 'energy/summary') {
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
        try {
            $sumRow = query_one(
                $conn,
                'SELECT SUM(energy_kwh) AS total_kwh, AVG(energy_kwh) AS avg_kwh FROM energy_readings WHERE reading_time >= DATE_SUB(NOW(), INTERVAL ? DAY)',
                'i',
                [$days]
            );
            $latestRow = query_one(
                $conn,
                'SELECT reading_time, energy_kwh FROM energy_readings ORDER BY reading_time DESC LIMIT 1'
            );
            $lastDayRow = query_one(
                $conn,
                'SELECT SUM(energy_kwh) AS total_last_day FROM energy_readings WHERE reading_time >= DATE_SUB(NOW(), INTERVAL 1 DAY)'
            );
            $WARNING_THRESHOLD_KWH = 1500;
            $status = (float)($lastDayRow['total_last_day'] ?? 0) > $WARNING_THRESHOLD_KWH ? 'peringatan' : 'normal';
            ok([
                'total_kwh' => (float)($sumRow['total_kwh'] ?? 0),
                'avg_kwh' => (float)($sumRow['avg_kwh'] ?? 0),
                'latest' => $latestRow ?: null,
                'status' => $status,
                'warning_threshold_kwh' => $WARNING_THRESHOLD_KWH,
                'window_days' => $days,
            ]);
        } catch (Exception $e) {
            fail(500, 'Gagal mengambil ringkasan', $e->getMessage());
        }
    }

    if ($route === 'energy/aggregate') {
        $mode = $_GET['mode'] ?? 'daily';
        $window = isset($_GET['window']) ? (int)$_GET['window'] : ($mode === 'monthly' ? 6 : 14);
        try {
            if ($mode === 'monthly') {
                $sql = "
                    SELECT DATE_FORMAT(reading_time, '%Y-%m-01') AS label, SUM(energy_kwh) AS total_kwh
                    FROM energy_readings
                    WHERE reading_time >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL ? MONTH)
                    GROUP BY label
                    ORDER BY label ASC
                ";
                $rows = query_all($conn, $sql, 'i', [$window - 1]);
            } elseif ($mode === 'weekly') {
                $sql = "
                    SELECT DATE_SUB(DATE(reading_time), INTERVAL WEEKDAY(reading_time) DAY) AS label, SUM(energy_kwh) AS total_kwh
                    FROM energy_readings
                    WHERE reading_time >= DATE_SUB(NOW(), INTERVAL ? WEEK)
                    GROUP BY DATE_SUB(DATE(reading_time), INTERVAL WEEKDAY(reading_time) DAY)
                    ORDER BY label ASC
                ";
                $rows = query_all($conn, $sql, 'i', [$window]);
            } else {
                $sql = "
                    SELECT DATE(reading_time) AS label, SUM(energy_kwh) AS total_kwh
                    FROM energy_readings
                    WHERE reading_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                    GROUP BY DATE(reading_time)
                    ORDER BY label ASC
                ";
                $rows = query_all($conn, $sql, 'i', [$window]);
            }
            $out = array_map(function ($r) {
                return [
                    'label' => $r['label'],
                    'total_kwh' => (float)($r['total_kwh'] ?? 0),
                ];
            }, $rows);
            ok($out);
        } catch (Exception $e) {
            fail(500, 'Gagal mengambil agregasi energi', $e->getMessage());
        }
    }

    if ($route === 'energy/live') {
        $window = isset($_GET['window']) ? (int)$_GET['window'] : 40; // jumlah sampel
        $window = max(5, min($window, 120));
        $interval = isset($_GET['interval']) ? (int)$_GET['interval'] : 15; // detik antar sampel
        $interval = max(5, min($interval, 120));
        $phase = isset($_GET['phase']) ? (float)$_GET['phase'] : 0.0;

        $now = time();
        $feed = [];
        for ($i = $window - 1; $i >= 0; $i--) {
            $ts = $now - ($i * $interval);
            $feed[] = [
                't' => date('c', $ts),
                'v' => simulated_energy_value($ts, $phase),
            ];
        }
        ok([
            'feed' => $feed,
            'interval_seconds' => $interval,
            'generated_at' => date('c', $now),
            'source' => 'simulated'
        ]);
    }

    if ($route === 'energy/live_stream') {
        // Server-Sent Events (SSE) untuk real-time
        $interval = isset($_GET['interval']) ? (int)$_GET['interval'] : 5; // detik
        $interval = max(1, min($interval, 30));
        $phase = isset($_GET['phase']) ? (float)$_GET['phase'] : 0.0;
        $lastHash = null;
        // Override header default JSON
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        // Flush padding to start stream
        echo ": stream started\n\n";
        @ob_flush();
        @flush();
        while (true) {
            $row = query_one(
                $conn,
                'SELECT id, reading_time, energy_kwh FROM energy_readings ORDER BY reading_time DESC LIMIT 1'
            );
            if ($row) {
                $hash = md5(json_encode($row));
                if ($hash !== $lastHash) {
                    $payload = [
                        'id' => (int)$row['id'],
                        'reading_time' => $row['reading_time'],
                        'energy_kwh' => (float)$row['energy_kwh'],
                        'source' => 'db'
                    ];
                    $lastHash = $hash;
                    echo "event: energy\n";
                    echo 'data: ' . json_encode($payload) . "\n\n";
                } else {
                    echo ": no-change\n\n";
                }
            } else {
                $ts = time();
                $payload = [
                    'id' => 0,
                    'reading_time' => date('c', $ts),
                    'energy_kwh' => simulated_energy_value($ts, $phase),
                    'source' => 'simulated'
                ];
                echo "event: energy\n";
                echo 'data: ' . json_encode($payload) . "\n\n";
            }
            @ob_flush();
            @flush();
            sleep($interval);
        }
    }

    if ($route === 'energy/readings') {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
        $limit = min($limit, 1000);
        $startDate = $_GET['startDate'] ?? null;
        $endDate = $_GET['endDate'] ?? null;
        $filters = [];
        $types = '';
        $params = [];
        if ($startDate) {
            $filters[] = 'reading_time >= ?';
            $types .= 's';
            $params[] = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $filters[] = 'reading_time <= ?';
            $types .= 's';
            $params[] = $endDate . ' 23:59:59';
        }
        $where = count($filters) ? ('WHERE ' . implode(' AND ', $filters)) : '';
        try {
            $sql = "SELECT id, reading_time, energy_kwh FROM energy_readings $where ORDER BY reading_time DESC LIMIT ?";
            $typesLim = $types . 'i';
            $paramsLim = array_merge($params, [$limit]);
            $rows = query_all($conn, $sql, $typesLim, $paramsLim);
            $out = array_map(function ($r) {
                return [
                    'id' => (int)$r['id'],
                    'reading_time' => $r['reading_time'],
                    'energy_kwh' => (float)($r['energy_kwh'] ?? 0),
                ];
            }, $rows);
            ok($out);
        } catch (Exception $e) {
            fail(500, 'Gagal mengambil data energi', $e->getMessage());
        }
    }

    fail(404, 'Route tidak ditemukan');
} catch (Exception $e) {
    fail(500, 'Kesalahan server', $e->getMessage());
}

