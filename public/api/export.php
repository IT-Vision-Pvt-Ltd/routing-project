<?php
require_once __DIR__ . '/../../app/init.php'; require_login(); if (!can('export')) { http_response_code(403); exit('Forbidden'); }
$view = $_GET['view'] ?? '';
if ($view === 'output1') {
  $q = db()->query("SELECT * FROM v_output_1 ORDER BY start_date DESC");
  $fn = 'output1_jobs_clients.csv';
} elseif ($view === 'output2') {
  $q = db()->query("SELECT * FROM v_output_2 ORDER BY account_reference_number, `order`, line_item_id");
  $fn = 'output2_job_line_items.csv';
} else {
  http_response_code(400); exit('missing view');
}
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'.$fn.'"');
$out = fopen('php://output', 'w');
$headerWrote = false;
foreach ($q as $row) {
  if (!$headerWrote) { fputcsv($out, array_keys($row)); $headerWrote=true; }
  fputcsv($out, $row);
}
fclose($out);
