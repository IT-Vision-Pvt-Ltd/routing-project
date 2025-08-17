<?php
// Usage (CLI): php etl/ingest_line_items.php /path/to/line_items.csv
require_once __DIR__ . '/../app/init.php';
if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only'); }
$csv = $argv[1] ?? null;
if (!$csv || !file_exists($csv)) { fwrite(STDERR, "Missing line_items.csv\n"); exit(1); }

$fh = fopen($csv, 'r');
$headers = fgetcsv($fh);
$map = array_flip($headers);

$required = ['id','job_id'];
foreach ($required as $col) {
  if (!isset($map[$col])) { fwrite(STDERR, "Missing column: $col\n"); exit(2); }
}

$total=$ok=$err=0; $errors=[];
db()->beginTransaction();
try {
  $sql = "INSERT INTO line_items (
            id, job_id, item_id, name, identifier, option_type_cd, option_accepted, quantity, completed_quantity,
            unit_price, completed_unit_cost, unit_markup, total, is_actual, is_approved, is_archived, is_billable,
            is_estimate, is_locked, is_percentage_discount, is_reimbursable, is_sales_tax, is_subcontracted,
            is_tax_discount, is_taxable, is_taxed, `order`, visit_id, vendor_id, created_at, updated_at
          ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE
            job_id=VALUES(job_id), item_id=VALUES(item_id), name=VALUES(name), identifier=VALUES(identifier),
            option_type_cd=VALUES(option_type_cd), option_accepted=VALUES(option_accepted), quantity=VALUES(quantity),
            completed_quantity=VALUES(completed_quantity), unit_price=VALUES(unit_price),
            completed_unit_cost=VALUES(completed_unit_cost), unit_markup=VALUES(unit_markup), total=VALUES(total),
            is_actual=VALUES(is_actual), is_approved=VALUES(is_approved), is_archived=VALUES(is_archived),
            is_billable=VALUES(is_billable), is_estimate=VALUES(is_estimate), is_locked=VALUES(is_locked),
            is_percentage_discount=VALUES(is_percentage_discount), is_reimbursable=VALUES(is_reimbursable),
            is_sales_tax=VALUES(is_sales_tax), is_subcontracted=VALUES(is_subcontracted),
            is_tax_discount=VALUES(is_tax_discount), is_taxable=VALUES(is_taxable), is_taxed=VALUES(is_taxed),
            `order`=VALUES(`order`), visit_id=VALUES(visit_id), vendor_id=VALUES(vendor_id),
            created_at=VALUES(created_at), updated_at=VALUES(updated_at)";
  $stmt = db()->prepare($sql);

  while (($row = fgetcsv($fh)) !== false) {
    $total++;
    try {
      $params = [
        $row[$map['id']],
        $row[$map['job_id']],
        $row[$map['item_id']] ?? null,
        $row[$map['name']] ?? null,
        $row[$map['identifier']] ?? null,
        $row[$map['option_type_cd']] ?? null,
        isset($map['option_accepted']) ? parse_bool($row[$map['option_accepted']]) : null,
        parse_decimal($row[$map['quantity']] ?? None),
        parse_decimal($row[$map['completed_quantity']] ?? None),
        parse_decimal($row[$map['unit_price']] ?? None),
        parse_decimal($row[$map['completed_unit_cost']] ?? None),
        parse_decimal($row[$map['unit_markup']] ?? None),
        parse_decimal($row[$map['total']] ?? None),
        isset($map['is_actual']) ? parse_bool($row[$map['is_actual']]) : null,
        isset($map['is_approved']) ? parse_bool($row[$map['is_approved']]) : null,
        isset($map['is_archived']) ? parse_bool($row[$map['is_archived']]) : null,
        isset($map['is_billable']) ? parse_bool($row[$map['is_billable']]) : null,
        isset($map['is_estimate']) ? parse_bool($row[$map['is_estimate']]) : null,
        isset($map['is_locked']) ? parse_bool($row[$map['is_locked']]) : null,
        isset($map['is_percentage_discount']) ? parse_bool($row[$map['is_percentage_discount']]) : null,
        isset($map['is_reimbursable']) ? parse_bool($row[$map['is_reimbursable']]) : null,
        isset($map['is_sales_tax']) ? parse_bool($row[$map['is_sales_tax']]) : null,
        isset($map['is_subcontracted']) ? parse_bool($row[$map['is_subcontracted']]) : null,
        isset($map['is_tax_discount']) ? parse_bool($row[$map['is_tax_discount']]) : null,
        isset($map['is_taxable']) ? parse_bool($row[$map['is_taxable']]) : null,
        isset($map['is_taxed']) ? parse_bool($row[$map['is_taxed']]) : null,
        isset($map['order']) ? (int)$row[$map['order']] : null,
        $row[$map['visit_id']] ?? null,
        $row[$map['vendor_id']] ?? null,
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
  record_import_log(basename($csv), 'line_items', $total, $ok, $err, $errors);
  echo "Line Items import: total=$total ok=$ok err=$err\n";
}
?>
