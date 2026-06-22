<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$tid = admin_tenant_id();

// Build filter query (shared by listing + export)
$search = trim($_GET['q'] ?? '');
$from   = trim($_GET['from'] ?? '');
$to     = trim($_GET['to'] ?? '');

$where  = ['tenant_id = ?'];
$params = [$tid];

if ($search !== '') {
    $where[] = '(full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR child_name LIKE ? OR city LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if ($from !== '') { $where[] = 'DATE(created_at) >= ?'; $params[] = $from; }
if ($to !== '')   { $where[] = 'DATE(created_at) <= ?'; $params[] = $to; }

$whereSql = implode(' AND ', $where);

// ---- CSV export ----
if (isset($_GET['export'])) {
    $stmt = db()->prepare("SELECT * FROM registrations WHERE $whereSql ORDER BY created_at DESC");
    $stmt->execute($params);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="registrations_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Parent Name','WhatsApp','Email','Child Name','Grade/Class','Syllabus','City','Heard About','Question / Expectation','Date']);
    while ($r = $stmt->fetch()) {
        fputcsv($out, [
            $r['id'],$r['full_name'],$r['mobile'],$r['email'],$r['child_name'] ?? '',
            $r['grade'] ?? '',$r['syllabus'] ?? '',$r['city'],$r['heard_about'] ?? '',
            $r['message'],$r['created_at'],
        ]);
    }
    fclose($out);
    exit;
}

// ---- Listing ----
$stmt = db()->prepare("SELECT * FROM registrations WHERE $whereSql ORDER BY created_at DESC LIMIT 500");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$active = 'registrations';
$page_heading = 'Registrations';
include __DIR__ . '/inc/header.php';
?>
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form class="row g-2 align-items-end" method="get">
      <div class="col-md-4"><label class="form-label small">Search (parent, WhatsApp, email, child, city)</label>
        <input class="form-control" name="q" value="<?= e($search) ?>"></div>
      <div class="col-md-3"><label class="form-label small">From</label>
        <input type="date" class="form-control" name="from" value="<?= e($from) ?>"></div>
      <div class="col-md-3"><label class="form-label small">To</label>
        <input type="date" class="form-control" name="to" value="<?= e($to) ?>"></div>
      <div class="col-md-2 d-grid gap-1">
        <button class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
      </div>
      <div class="col-12">
        <a href="registrations.php" class="btn btn-sm btn-link">Reset</a>
        <a href="?export=1&q=<?= urlencode($search) ?>&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>"
           class="btn btn-sm btn-success float-end"><i class="bi bi-file-earmark-excel"></i> Export CSV</a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white"><strong><?= count($rows) ?></strong> record(s)</div>
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-light"><tr>
        <th>#</th><th>Parent Name</th><th>WhatsApp</th><th>Email</th><th>Child Name</th><th>Grade</th>
        <th>Syllabus</th><th>City</th><th>Heard About</th><th>Question / Expectation</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= e($r['full_name']) ?></td>
            <td><a href="tel:<?= e($r['mobile']) ?>"><?= e($r['mobile']) ?></a></td>
            <td><?= e($r['email']) ?></td>
            <td><?= e($r['child_name'] ?? '') ?></td>
            <td><?= e($r['grade'] ?? '') ?></td>
            <td><?= e($r['syllabus'] ?? '') ?></td>
            <td><?= e($r['city']) ?></td>
            <td><?= e($r['heard_about'] ?? '') ?></td>
            <td class="small"><?= e($r['message']) ?></td>
            <td class="text-nowrap small"><?= e(date('d M Y H:i', strtotime($r['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="11" class="text-center text-muted py-4">No registrations found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
