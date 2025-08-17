<?php
require_once __DIR__ . '/../app/init.php'; require_login();
header('Content-Type: text/html; charset=utf-8');
$date = $_GET['date'] ?? date('Y-m-d');
$jobs = db()->prepare("SELECT j.*, c.display_name, c.bill_addr_1, c.bill_addr_city, c.bill_addr_state, c.bill_addr_postal_code, c.phone FROM jobs j JOIN clients c ON c.id=j.client_id WHERE DATE(j.start_date)=? ORDER BY j.id ASC");
$jobs->execute([$date]);
$jobs = $jobs->fetchAll();
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Route Sheet — <?=$date?></title>
<style>
body { font-family: Arial, sans-serif; margin: 24px; }
h1 { margin: 0 0 12px; }
table { width:100%; border-collapse: collapse; }
th,td { border:1px solid #ddd; padding:8px; font-size:12px; }
@media print { .noprint { display:none; } }
</style>
</head><body>
<div class="noprint"><button onclick="window.print()">Print</button></div>
<h1>Route Sheet — <?=$date?></h1>
<table>
<tr><th>#</th><th>Job #</th><th>Client</th><th>Address</th><th>Phone</th><th>Task Notes</th></tr>
<?php $i=1; foreach ($jobs as $j): ?>
<tr>
  <td><?=$i++?></td>
  <td><?=htmlspecialchars($j['account_reference_number'])?></td>
  <td><?=htmlspecialchars($j['display_name'])?></td>
  <td><?=htmlspecialchars($j['bill_addr_1'].' '.$j['bill_addr_city'].' '.$j['bill_addr_state'].' '.$j['bill_addr_postal_code'])?></td>
  <td><?=htmlspecialchars($j['phone'])?></td>
  <td><?=htmlspecialchars($j['crew_notes'] ?? '')?></td>
</tr>
<?php endforeach; ?>
</table>
</body></html>
