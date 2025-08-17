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

<?php
$job_id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM jobs WHERE id=?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();
if (!$job) { echo "<h1>Job not found</h1></div></body></html>"; exit; }
$c = db()->prepare("SELECT * FROM clients WHERE id=?"); $c->execute([$job['client_id']]); $client=$c->fetch();
?>
<h1>Job #<?=h($job['account_reference_number'])?> (ID: <?=h($job['id'])?>)</h1>
<div class="grid">
  <div class="card">
    <strong>Stage: </strong><?=stage_badge($job['current_job_stage'])?><br>
    <strong>Name:</strong> <?=h($job['job_name'])?><br>
    <strong>Start:</strong> <?=h($job['start_date'])?> — <strong>End:</strong> <?=h($job['end_date'])?><br>
    <strong>Total:</strong> <?=h(number_format((float)$job['total_price'],2))?><br>
  </div>
  <div class="card">
    <strong>Client:</strong> <?=h($client['display_name'] ?? $client['company_name'] ?? ($client['first_name'].' '.$client['last_name']))?><br>
    <div><?=h($client['bill_addr_1'])?> <?=h($client['bill_addr_city'])?> <?=h($client['bill_addr_state'])?> <?=h($client['bill_addr_postal_code'])?></div>
    <div><?=h($client['email'])?> — <?=h($client['phone'])?></div>
  </div>
</div>
<h3>Line Items</h3>
<table class="table">
  <tr>
    <th>Item</th><th>Qty</th><th>Unit</th><th>Completed Qty</th><th>Total</th>
  </tr>
  <?php
    $li = db()->prepare("SELECT * FROM line_items WHERE job_id=? ORDER BY `order` ASC, id ASC");
    $li->execute([$job_id]);
    foreach ($li as $r) {
      echo "<tr>";
      echo "<td>".h($r['name'])."</td>";
      echo "<td>".h($r['quantity'])."</td>";
      echo "<td>".h($r['unit_price'])."</td>";
      echo "<td>".h($r['completed_quantity'])."</td>";
      echo "<td>".h($r['total'])."</td>";
      echo "</tr>";
    }
  ?>
</table>
</div><footer>© <?=date('Y')?> SRP</footer></body></html>
