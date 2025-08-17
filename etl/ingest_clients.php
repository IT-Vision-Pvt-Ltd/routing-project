<?php
// Usage (CLI): php etl/ingest_clients.php /path/to/clients.csv
require_once __DIR__ . '/../app/init.php';
if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only'); }
$csv = $argv[1] ?? null;
if (!$csv || !file_exists($csv)) { fwrite(STDERR, "Missing clients.csv\n"); exit(1); }

$fh = fopen($csv, 'r');
$headers = fgetcsv($fh);
$map = array_flip($headers);

$required = ['id'];
foreach ($required as $col) {
  if (!isset($map[$col])) { fwrite(STDERR, "Missing column: $col\n"); exit(2); }
}

$total=$ok=$err=0; $errors=[];
db()->beginTransaction();
try {
  $sql = "INSERT INTO clients (id, company_name, display_name, first_name, middle_name, last_name, email, phone, mobile, fax,
            bill_addr_1, bill_addr_2, bill_addr_3, bill_addr_4, bill_addr_5, bill_addr_city, bill_addr_state, bill_addr_postal_code, bill_addr_country,
            customer_type_id, latitude, longitude, created_at, updated_at)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE
            company_name=VALUES(company_name), display_name=VALUES(display_name), first_name=VALUES(first_name),
            middle_name=VALUES(middle_name), last_name=VALUES(last_name), email=VALUES(email), phone=VALUES(phone),
            mobile=VALUES(mobile), fax=VALUES(fax), bill_addr_1=VALUES(bill_addr_1), bill_addr_2=VALUES(bill_addr_2),
            bill_addr_3=VALUES(bill_addr_3), bill_addr_4=VALUES(bill_addr_4), bill_addr_5=VALUES(bill_addr_5),
            bill_addr_city=VALUES(bill_addr_city), bill_addr_state=VALUES(bill_addr_state), bill_addr_postal_code=VALUES(bill_addr_postal_code),
            bill_addr_country=VALUES(bill_addr_country), customer_type_id=VALUES(customer_type_id),
            latitude=VALUES(latitude), longitude=VALUES(longitude), created_at=VALUES(created_at), updated_at=VALUES(updated_at)";
  $stmt = db()->prepare($sql);

  while (($row = fgetcsv($fh)) !== false) {
    $total++;
    try {
      $id = $row[$map['id']];
      $created_at = isset($map['created_at']) ? parse_datetime($row[$map['created_at']]) : null;
      $updated_at = isset($map['updated_at']) ? parse_datetime($row[$map['updated_at']]) : null;

      $params = [
        $id,
        $row[$map['company_name']] ?? null,
        $row[$map['display_name']] ?? null,
        $row[$map['first_name']] ?? null,
        $row[$map['middle_name']] ?? null,
        $row[$map['last_name']] ?? null,
        $row[$map['email']] ?? null,
        $row[$map['phone']] ?? null,
        $row[$map['mobile']] ?? null,
        $row[$map['fax']] ?? null,
        $row[$map['bill_addr_1']] ?? null,
        $row[$map['bill_addr_2']] ?? null,
        $row[$map['bill_addr_3']] ?? null,
        $row[$map['bill_addr_4']] ?? null,
        $row[$map['bill_addr_5']] ?? null,
        $row[$map['bill_addr_city']] ?? null,
        $row[$map['bill_addr_state']] ?? null,
        $row[$map['bill_addr_postal_code']] ?? null,
        $row[$map['bill_addr_country']] ?? null,
        $row[$map['customer_type_id']] ?? null,
        null, null,
        $created_at, $updated_at
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
  record_import_log(basename($csv), 'clients', $total, $ok, $err, $errors);
  echo "Clients import: total=$total ok=$ok err=$err\n";
}
?>
