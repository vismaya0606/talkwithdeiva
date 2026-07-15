<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$tid = admin_tenant_id();

$rows = db()->prepare('SELECT * FROM payments WHERE tenant_id = ? ORDER BY id DESC LIMIT 500');
$rows->execute([$tid]);
$rows = $rows->fetchAll();

$badge = ['success' => 'success', 'failed' => 'danger', 'created' => 'secondary'];

$active = 'payments';
$page_heading = 'Payments';
include __DIR__ . '/inc/header.php';
?>
<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th><th>Date</th><th>Buyer</th><th>Contact</th>
          <th>Service</th><th>Amount</th><th>Status</th><th>Payment Ref</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td class="text-nowrap small"><?= e(date('d M Y, h:i A', strtotime($r['created_at']))) ?></td>
          <td><?= e($r['buyer_name']) ?></td>
          <td class="small"><?= e($r['email']) ?><br><?= e($r['phone']) ?></td>
          <td><?= e($r['service_title']) ?></td>
          <td class="text-nowrap"><?= e(format_price($r['amount'])) ?></td>
          <td><span class="badge bg-<?= e($badge[$r['status']] ?? 'secondary') ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td class="small"><code><?= e($r['payment_id'] ?: ($r['payment_request_id'] ?: '—')) ?></code></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">No payments yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
