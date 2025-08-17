<?php require_once __DIR__ . '/../app/init.php'; require_login(); ?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="assets/css/style.css">
<title>Service Routing Portal</title>
</head><body>
<div class="header">
  <div>Service Routing Portal</div>
  <div class="nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="jobs.php">Jobs</a>
    <a href="line_items.php">Line Items</a>
    <a href="route_planner.php">Route Planner</a>
    <?php if (can('import')): ?><a href="import.php">Import</a><?php endif; ?>
    <a href="logout.php">Logout</a>
  </div>
</div>
<div class="container">

<h1>Route Planner</h1>
<form method="get" class="form-row">
  <div>
    <label>Date</label>
    <input type="date" name="date" value="<?=h($_GET['date'] ?? date('Y-m-d'))?>">
  </div>
  <div>
    <label>Sales Rep (optional)</label>
    <input type="text" name="rep" placeholder="e.g., Ali">
  </div>
  <div>
    <label>Max stops per route</label>
    <input type="number" name="max" value="<?=h($_GET['max'] ?? 30)?>" min="1">
  </div>
  <div style="align-self:end;"><button>Build Routes</button></div>
  <div style="align-self:end;"><a class="secondary" href="/print_route.php?date=<?=urlencode($_GET['date'] ?? date('Y-m-d'))?>" target="_blank">Print Route Sheet</a></div>
</form>

<?php
function distance($a, $b) {
  // crude haversine-like using Euclidean fallback if missing coords
  if ($a['latitude'] && $a['longitude'] && $b['latitude'] && $b['longitude']) {
    $dx = $a['latitude'] - $b['latitude'];
    $dy = $a['longitude'] - $b['longitude'];
    return sqrt($dx*$dx + $dy*$dy);
  }
  return strcasecmp($a['bill_addr_city'] ?? '', $b['bill_addr_city'] ?? '');
}

$date = $_GET['date'] ?? date('Y-m-d');
$jobs = db()->prepare("SELECT j.*, c.display_name, c.bill_addr_city, c.latitude, c.longitude FROM jobs j JOIN clients c ON c.id=j.client_id WHERE DATE(j.start_date)=? ORDER BY j.id ASC");
$jobs->execute([$date]);
$jobs = $jobs->fetchAll();

if (!$jobs) { echo "<div class='card'>No jobs for date.</div></div><footer>© ".date('Y')." SRP</footer></body></html>"; exit; }

// Single route (extend to group by rep when reps exist in data)
$origin = ['latitude'=>$jobs[0]['latitude'],'longitude'=>$jobs[0]['longitude'],'bill_addr_city'=>$jobs[0]['bill_addr_city']];
$order = [$jobs[0]];
$remaining = array_slice($jobs,1);
while ($remaining) {
  $last = $order[-1] ?? $order[count($order)-1];
  $nearest_i = 0; $nearest_d = 1e9;
  foreach ($remaining as $i=>$j) {
    $d = distance($last, $j);
    if ($d < $nearest_d) { $nearest_d = $d; $nearest_i = $i; }
  }
  $order[] = $remaining[$nearest_i];
  array_splice($remaining,$nearest_i,1);
}

echo "<h3>Route for ".h($date)." — ".count($order)." stops</h3>";
echo "<table class='table'><tr><th>#</th><th>Job #</th><th>Client</th><th>City</th><th>Start</th></tr>";
$seq=1;
foreach ($order as $j) {
  echo "<tr><td>$seq</td><td>".h($j['account_reference_number'])."</td><td>".h($j['display_name'])."</td><td>".h($j['bill_addr_city'])."</td><td>".h($j['start_date'])."</td></tr>";
  $seq++;
}
echo "</table>";
?>
</div><footer>© <?=date('Y')?> SRP</footer></body></html>
