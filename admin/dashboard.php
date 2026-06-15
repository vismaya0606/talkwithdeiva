<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$tid = admin_tenant_id();

function count_for(string $sql, int $tid): int {
    $stmt = db()->prepare($sql);
    $stmt->execute([$tid]);
    return (int) $stmt->fetchColumn();
}

$total_regs    = count_for('SELECT COUNT(*) FROM registrations WHERE tenant_id = ?', $tid);
$today_regs    = (function($tid){
    $stmt = db()->prepare('SELECT COUNT(*) FROM registrations WHERE tenant_id = ? AND DATE(created_at)=CURDATE()');
    $stmt->execute([$tid]); return (int)$stmt->fetchColumn();
})($tid);
$total_svc     = count_for('SELECT COUNT(*) FROM services WHERE tenant_id = ?', $tid);
$total_testi   = count_for('SELECT COUNT(*) FROM testimonials WHERE tenant_id = ?', $tid);
$total_gallery = count_for('SELECT COUNT(*) FROM gallery WHERE tenant_id = ?', $tid);

$recent = db()->prepare('SELECT * FROM registrations WHERE tenant_id = ? ORDER BY created_at DESC LIMIT 8');
$recent->execute([$tid]);
$recent = $recent->fetchAll();

$active = 'dashboard';
$page_heading = 'Dashboard';
include __DIR__ . '/inc/header.php';

$cards = [
  ['Total Registrations', $total_regs,    'bi-people-fill',  'linear-gradient(135deg,#0d6efd,#6610f2)'],
  ["Today's Registrations",$today_regs,   'bi-calendar-day', 'linear-gradient(135deg,#11998e,#38ef7d)'],
  ['Total Services',       $total_svc,     'bi-stars',        'linear-gradient(135deg,#f7971e,#ffd200)'],
  ['Total Testimonials',   $total_testi,   'bi-chat-quote',   'linear-gradient(135deg,#ee0979,#ff6a00)'],
  ['Gallery Images',       $total_gallery, 'bi-images',       'linear-gradient(135deg,#4568dc,#b06ab3)'],
];
?>
<div class="row g-3 mb-4">
  <?php foreach ($cards as $c): ?>
    <div class="col-sm-6 col-xl">
      <div class="card stat-card shadow-sm" style="background:<?= $c[3] ?>">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="h2 fw-bold mb-0"><?= (int)$c[1] ?></div>
            <small class="text-white-50"><?= e($c[0]) ?></small>
          </div>
          <i class="bi <?= $c[2] ?> fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white"><strong>Recent Registrations</strong>
    <a href="registrations.php" class="float-end small">View all</a></div>
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-light"><tr>
        <th>Name</th><th>Mobile</th><th>City</th><th>Service</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($recent as $r): ?>
          <tr>
            <td><?= e($r['full_name']) ?></td>
            <td><?= e($r['mobile']) ?></td>
            <td><?= e($r['city']) ?></td>
            <td><?= e($r['interested_service']) ?></td>
            <td><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recent): ?><tr><td colspan="5" class="text-center text-muted py-4">No registrations yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
