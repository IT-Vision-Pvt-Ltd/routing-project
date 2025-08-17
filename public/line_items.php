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

<h1>Line Items (Output #2)</h1>
<form method="get" class="form-row">
  <div>
    <label>Job Number (account_reference_number)</label>
    <input type="text" name="jobno" value="<?=h($_GET['jobno'] ?? '')?>">
  </div>
  <div style="align-self:end;"><button class="secondary">Find</button></div>
  <div style="align-self:end;"><a class="secondary" href="/api/export.php?view=output2">Export CSV</a></div>
</form>
<?php
$rows = [];
if (!empty($_GET['jobno'])) {
  $stmt = db()->prepare("SELECT * FROM v_output_2 WHERE account_reference_number = ? ORDER BY `order` ASC, line_item_id ASC");
  $stmt->execute([$_GET['jobno']]);
  $rows = $stmt->fetchAll();
}
?>
<table class="table">
  <tr>
    <th>Job #</th><th>Job ID</th><th>Item</th><th>Qty</th><th>Completed Qty</th><th>Unit Price</th><th>Total</th>
  </tr>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?=h($r['account_reference_number'])?></td>
      <td><?=h($r['job_id'])?></td>
      <td><?=h($r['name'])?></td>
      <td><?=h($r['quantity'])?></td>
      <td><?=h($r['completed_quantity'])?></td>
      <td><?=h($r['unit_price'])?></td>
      <td><?=h($r['total'])?></td>
    </tr>
  <?php endforeach; ?>
</table>
</div><footer>© <?=date('Y')?> SRP</footer></body></html>
