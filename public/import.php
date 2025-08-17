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

<h1>Import CSVs</h1>
<?php if (!can('import')) { echo "<p>Forbidden.</p></div></body></html>"; exit; } ?>
<form method="post" enctype="multipart/form-data">
  <fieldset><legend class="legend">Upload clients.csv</legend>
    <input type="file" name="clients" accept=".csv">
  </fieldset>
  <fieldset><legend class="legend">Upload jobs.csv</legend>
    <input type="file" name="jobs" accept=".csv">
  </fieldset>
  <fieldset><legend class="legend">Upload line_items.csv</legend>
    <input type="file" name="line_items" accept=".csv">
  </fieldset>
  <div class="form-row"><button type="submit">Import</button></div>
</form>
<?php
if (is_post()) {
  $uploads = ['clients'=>'clients','jobs'=>'jobs','line_items'=>'line_items'];
  foreach ($uploads as $key=>$table) {
    if (!empty($_FILES[$key]['tmp_name'])) {
      $tmp = $_FILES[$key]['tmp_name'];
      $name = basename($_FILES[$key]['name']);
      $fh = fopen($tmp, 'r');
      $headers = fgetcsv($fh);
      if (!$headers) { echo "<div class='card'>Empty file: ".h($name)."</div>"; continue; }
      // Process in PHP (web) directly, small batches; for large files use CLI ETL.
      $map = array_flip($headers);
      $total=$ok=$err=0; $errors=[];
      db()->beginTransaction();
      try {
        if ($table === 'clients') {
          $sql = "INSERT INTO clients (id, company_name, display_name, first_name, middle_name, last_name, email, phone, mobile, fax,
                    bill_addr_1, bill_addr_2, bill_addr_3, bill_addr_4, bill_addr_5, bill_addr_city, bill_addr_state, bill_addr_postal_code, bill_addr_country,
                    customer_type_id, created_at, updated_at)
                  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                  ON DUPLICATE KEY UPDATE
                    company_name=VALUES(company_name), display_name=VALUES(display_name), first_name=VALUES(first_name),
                    middle_name=VALUES(middle_name), last_name=VALUES(last_name), email=VALUES(email), phone=VALUES(phone),
                    mobile=VALUES(mobile), fax=VALUES(fax), bill_addr_1=VALUES(bill_addr_1), bill_addr_2=VALUES(bill_addr_2),
                    bill_addr_3=VALUES(bill_addr_3), bill_addr_4=VALUES(bill_addr_4), bill_addr_5=VALUES(bill_addr_5),
                    bill_addr_city=VALUES(bill_addr_city), bill_addr_state=VALUES(bill_addr_state), bill_addr_postal_code=VALUES(bill_addr_postal_code),
                    bill_addr_country=VALUES(bill_addr_country), customer_type_id=VALUES(customer_type_id),
                    created_at=VALUES(created_at), updated_at=VALUES(updated_at)";
          $stmt = db()->prepare($sql);
        } elseif ($table === 'jobs') {
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
        } else {
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
        }

        while (($row = fgetcsv($fh)) !== false) {
          $total++;
          try {
            if ($table === 'clients') {
              $params = [
                $row[$map['id']],
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
                isset($map['created_at']) ? parse_datetime($row[$map['created_at']]) : null,
                isset($map['updated_at']) ? parse_datetime($row[$map['updated_at']]) : null,
              ];
            } elseif ($table === 'jobs') {
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
                parse_decimal($row[$map['total_price']] ?? null),
                parse_decimal($row[$map['total_payments']] ?? null),
                parse_decimal($row[$map['total_sales_tax']] ?? null),
                parse_decimal($row[$map['total_cogs']] ?? null),
                parse_decimal($row[$map['total_expenses']] ?? null),
                parse_decimal($row[$map['total_costs']] ?? null),
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
            } else {
              $params = [
                $row[$map['id']],
                $row[$map['job_id']],
                $row[$map['item_id']] ?? null,
                $row[$map['name']] ?? null,
                $row[$map['identifier']] ?? null,
                $row[$map['option_type_cd']] ?? null,
                isset($map['option_accepted']) ? parse_bool($row[$map['option_accepted']]) : null,
                parse_decimal($row[$map['quantity']] ?? null),
                parse_decimal($row[$map['completed_quantity']] ?? null),
                parse_decimal($row[$map['unit_price']] ?? null),
                parse_decimal($row[$map['completed_unit_cost']] ?? null),
                parse_decimal($row[$map['unit_markup']] ?? null),
                parse_decimal($row[$map['total']] ?? null),
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
            }
            $stmt->execute($params);
            $ok++;
          } catch (Throwable $e) {
            $err++; $errors[] = ['row'=>$total, 'error'=>$e->getMessage()];
          }
        }
        db()->commit();
        fclose($fh);
        record_import_log($name, $table, $total, $ok, $err, $errors);
        echo "<div class='card'>Imported ".h($name).": total=$total ok=$ok err=$err</div>";
      } catch (Throwable $e) {
        db()->rollBack();
        fclose($fh);
        echo "<div class='card'>Error importing ".h($name).": ".h($e->getMessage())."</div>";
      }
    }
  }
}
?>
</div><footer>© <?=date('Y')?> SRP</footer></body></html>
