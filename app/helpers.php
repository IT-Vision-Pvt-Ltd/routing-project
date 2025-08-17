<?php
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header("Location: $url"); exit; }
function is_post() { return $_SERVER['REQUEST_METHOD'] === 'POST'; }

function can($cap) {
    $u = $_SESSION['user'] ?? null;
    if (!$u) return false;
    $role = $u['role'];
    $matrix = [
        'admin' => ['import','view','export','route'],
        'manager' => ['view','export','route'],
        'sales' => ['view','route'],
        'readonly' => ['view']
    ];
    return in_array($cap, $matrix[$role] ?? [], true);
}

function stage_badge($stage) {
    $c = 'gray';
    if ($stage === 'Approved') $c = 'blue';
    elseif ($stage === 'Scheduled') $c = 'orange';
    elseif ($stage === 'Completed') $c = 'green';
    return '<span class="badge ' . $c . '">' . h($stage) . '</span>';
}

function parse_bool($v) {
    if ($v === null) return null;
    $v = strtolower(trim((string)$v));
    return in_array($v, ['1','true','yes','y'], true) ? 1 :
           (in_array($v, ['0','false','no','n'], true) ? 0 : null);
}

function parse_decimal($v) {
    if ($v === '' || $v === null) return null;
    return (float)str_replace([','], [''], $v);
}

function parse_datetime($v) {
    if (!$v) return null;
    $ts = strtotime($v);
    return $ts ? date('Y-m-d H:i:s', $ts) : null;
}

function record_import_log($file, $table, $total, $ok, $err, $errors = []) {
    $stmt = db()->prepare("INSERT INTO import_logs (file_name, table_name, total_rows, success_rows, error_rows, errors) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$file, $table, $total, $ok, $err, $errors ? json_encode($errors) : null]);
}

function geocode_address($addr) {
    $key = GOOGLE_MAPS_API_KEY;
    if (!$key) return [None, None];
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($addr) . "&key=" . urlencode($key);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>10]);
    $res = curl_exec($ch);
    curl_close($ch);
    if (!$res) return [None, None];
    $obj = json_decode($res, true);
    if (($obj['status'] ?? '') === 'OK') {
        $loc = $obj['results'][0]['geometry']['location'];
        return [$loc['lat'], $loc['lng']];
    }
    return [None, None];
}
?>
