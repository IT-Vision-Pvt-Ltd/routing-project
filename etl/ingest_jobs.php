<?php
// Usage (CLI): php etl/ingest_jobs.php /path/to/jobs.csv
require_once __DIR__ . '/../app/init.php';
if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only'); }
$csv = $argv[1] ?? null;
if (!$csv || !file_exists($csv)) { fwrite(STDERR, "Missing jobs.csv\n"); exit(1); }

$fh = fopen($csv, 'r');
$headers = fgetcsv($fh);
$map = array_flip($headers);

$required = ['id','client_id','account_reference_number'];
foreach ($required as $col) {
  if (!isset($map[$col])) { fwrite(STDERR, "Missing column: $col\n"); exit(2); }
}

$total=$ok=$err=0; $errors=[];
db()->beginTransaction();
try {
  $sql = "INSERT INTO jobs (
            id, operation_id, account_reference_number, client_id, job_name, is_recurring, start_date, end_date, location_id,
            total_price, total_payments, total_sales_tax, total_cogs, total_expenses, total_costs,
            proposal_date, converted_to_active, current_job_stage, is_archived, on_hold, proposal_accepted_at,
            submitted_by_user_id, is_eligible_for_renewal, client_notes, internal_notes, crew_notes, progress_billing_type,
            created_at, updated_at
          ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE
            operation_id=VALUES(operation_id), account_reference_number=VALUES(account_reference_number),
            client_id=VALUES(client_id), job_name=VALUES(job_name), is_recurring=VALUES(is_recurring),
            start_date=VALUES(start_date), end_date=VALUES(end_date), location_id=VALUES(location_id),
            total_price=VALUES(total_price), total_payments=VALUES(total_payments), total_sales_tax=VALUES(total_sales_tax),
            total_cogs=VALUES(total_cogs), total_expenses=VALUES(total_expenses), total_costs=VALUES(total_costs),
            proposal_date=VALUES(proposal_date), converted_to_active=VALUES(converted_to_active),
            current_job_stage=VALUES(current_job_stage), is_archived=VALUES(is_archived), on_hold=VALUES(on_hold),
            proposal_accepted_at=VALUES(proposal_accepted_at), submitted_by_user_id=VALUES(submitted_by_user_id),
            is_eligible_for_renewal=VALUES(is_eligible_for_renewal), client_notes=VALUES(client_notes),
            internal_notes=VALUES(internal_notes), crew_notes=VALUES(crew_notes),
            progress_billing_type=VALUES(progress_billing_type), created_at=VALUES(created_at), updated_at=VALUES(updated_at)";
  $stmt = db()->prepare($sql);

  while (($row = fgetcsv($fh)) !== false) {
    $total++;
    try {
      $params = [
        $row[$map['id']],
        $row[$map['operation_id']] ?? null,
        $row[$map['account_reference_number']] ?? null,
        $row[$map['client_id']],
        $row[$map['job_name']] ?? null,
        isset($map['is_recurring']) ? parse_bool($row[$map['is_recurring']]) : null,
        isset($map['start_date']) ? parse_datetime($row[$map['start_date']]) : null,
        isset($map['end_date']) ? parse_datetime($row[$map['end_date']]) : null,
        $row[$map['location_id']] ?? null,
        parse_decimal($row[$map['total_price']] ?? None),
        parse_decimal($row[$map['total_payments']] ?? None),
        parse_decimal($row[$map['total_sales_tax']] ?? None),
        parse_decimal($row[$map['total_cogs']] ?? None),
        parse_decimal($row[$map['total_expenses']] ?? None),
        parse_decimal($row[$map['total_costs']] ?? None),
        isset($map['proposal_date']) ? parse_datetime($row[$map['proposal_date']]) : null,
        isset($map['converted_to_active']) ? parse_bool($row[$map['converted_to_active']]) : null,
        $row[$map['current_job_stage']] ?? null,
        isset($map['is_archived']) ? parse_bool($row[$map['is_archived']]) : null,
        isset($map['on_hold']) ? parse_bool($row[$map['on_hold']]) : null,
        isset($map['proposal_accepted_at']) ? parse_datetime($row[$map['proposal_accepted_at']]) : null,
        $row[$map['submitted_by_user_id']] ?? null,
        isset($map['is_eligible_for_renewal']) ? parse_bool($row[$map['is_eligible_for_renewal']]) : null,
        $row[$map['client_notes']] ?? null,
        $row[$map['internal_notes']] ?? null,
        $row[$map['crew_notes']] ?? null,
        $row[$map['progress_billing_type']] ?? null,
        isset($map['created_at']) ? parse_datetime($row[$map['created_at']]) : null,
        isset($map['updated_at']) ? parse_datetime($row[$map['updated_at']]) : null,
      ];
      $stmt->execute($params);
      $ok++;
    } catch (Throwable $e) {
      $err++; $errors[] = ['row'=>$total, 'error'=>$e->getMessage()];
    }
  }
  db()->commit();
} catch (Throwable $e) {
  db()->rollBack();
  throw $e;
} finally {
  fclose($fh);
  record_import_log(basename($csv), 'jobs', $total, $ok, $err, $errors);
  echo "Jobs import: total=$total ok=$ok err=$err\n";
}
?>
