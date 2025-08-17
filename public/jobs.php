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

<h1>Jobs (Output #1)</h1>
<form method="get" class="form-row">
  <div>
    <label>Stage</label>
    <select name="stage">
      <option value="">Any</option>
      <?php foreach (['Approved','Scheduled','Completed'] as $s) {
        $sel = ($_GET['stage'] ?? '') === $s ? 'selected' : '';
        echo "<option $sel>$s</option>";
      } ?>
    </select>
  </div>
  <div>
    <label>Date From</label>
    <input type="date" name="from" value="<?=h($_GET['from'] ?? '')?>">
  </div>
  <div>
    <label>Date To</label>
    <input type="date" name="to" value="<?=h($_GET['to'] ?? '')?>">
  </div>
  <div style="align-self:end;"><button class="secondary">Filter</button></div>
  <div style="align-self:end;"><a class="secondary" href="api/export.php?view=output1">Export CSV</a></div>
</form>

<table class="table">
  <tr>
    <th>Job #</th><th>Job ID</th><th>Name</th><th>Stage</th><th>Client</th>
    <th>City</th><th>Start</th><th>End</th><th>Total</th><th></th>
  </tr>
  <?php
    $sql = "SELECT * FROM v_output_1 WHERE 1";
    $args = [];
    if (!empty($_GET['stage'])) { $sql .= " AND stage = ?"; $args[] = $_GET['stage']; }
    if (!empty($_GET['from'])) { $sql .= " AND (start_date >= ?)"; $args[] = $_GET['from'] . ' 00:00:00'; }
    if (!empty($_GET['to']))   { $sql .= " AND (end_date <= ?)"; $args[] = $_GET['to'] . ' 23:59:59'; }
    $sql .= " ORDER BY start_date DESC LIMIT 500";
    $stmt = db()->prepare($sql); $stmt->execute($args);
    foreach ($stmt as $r) {
      echo "<tr>";
      echo "<td>".h($r['account_reference_number'])."</td>";
      echo "<td>".h($r['job_id'])."</td>";
      echo "<td>".h($r['job_name'])."</td>";
      echo "<td>".stage_badge($r['stage'])."</td>";
      echo "<td>".h($r['name'])."</td>";
      echo "<td>".h($r['bill_addr_city'])."</td>";
      echo "<td>".h($r['start_date'])."</td>";
      echo "<td>".h($r['end_date'])."</td>";
      echo "<td>".h(number_format((float)$r['total_price'],2))."</td>";
      echo "<td><a href='job.php?id=".urlencode($r['job_id'])."'>View</a></td>";
      echo "</tr>";
    }
  ?>
</table>
</div><footer>© <?=date('Y')?> SRP</footer></body></html>
