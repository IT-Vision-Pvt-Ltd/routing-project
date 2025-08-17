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

<h1>Dashboard</h1>
<div class="grid">
  <div class="card">
    <div>Today's Jobs (by stage)</div>
    <div>
      <?php
        $today = date('Y-m-d');
        $stmt = db()->prepare("SELECT current_job_stage AS stage, COUNT(*) c FROM jobs WHERE DATE(start_date)=? GROUP BY current_job_stage");
        $stmt->execute([$today]);
        foreach ($stmt->fetchAll() as $r) {
          echo '<div>'.h($r['stage']).': <strong>'.h($r['c']).'</strong></div>';
        }
      ?>
    </div>
  </div>
  <div class="card">
    <div>Last Imports</div>
    <table class="table">
      <tr><th>When</th><th>Table</th><th>File</th><th>OK</th><th>Err</th></tr>
      <?php
        $q = db()->query("SELECT * FROM import_logs ORDER BY id DESC LIMIT 5");
        foreach ($q as $log) {
          echo '<tr><td>'.h($log['created_at']).'</td><td>'.h($log['table_name']).'</td><td>'.h($log['file_name']).'</td><td>'.h($log['success_rows']).'</td><td>'.h($log['error_rows']).'</td></tr>';
        }
      ?>
    </table>
  </div>
</div>
</div><footer>© <?=date('Y')?> SRP</footer></body></html>
