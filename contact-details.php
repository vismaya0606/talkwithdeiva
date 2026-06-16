<?php
require_once __DIR__ . '/config/functions.php';
$page_title       = 'Contact Details | ' . setting('site_name');
$page_description = 'Public contact details of ' . setting('site_name') . '.';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container" style="max-width:820px">
    <h1 class="section-title mb-4">Public Contact Details</h1>
    <p class="text-muted">The information below is provided for transparency and customer support.</p>

    <div class="card shadow-sm mt-3">
      <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex">
          <strong class="me-3" style="min-width:140px"><i class="bi bi-building me-2"></i>Business Name</strong>
          <span><?= e(setting('site_name')) ?></span>
        </li>
        <li class="list-group-item d-flex">
          <strong class="me-3" style="min-width:140px"><i class="bi bi-geo-alt me-2"></i>Address</strong>
          <span><?= nl2br(e(setting('contact_address'))) ?></span>
        </li>
        <li class="list-group-item d-flex">
          <strong class="me-3" style="min-width:140px"><i class="bi bi-telephone me-2"></i>Phone</strong>
          <span><a href="tel:<?= e(setting('contact_phone')) ?>" class="text-decoration-none"><?= e(setting('contact_phone')) ?></a></span>
        </li>
        <?php if (setting('contact_whatsapp')): ?>
        <li class="list-group-item d-flex">
          <strong class="me-3" style="min-width:140px"><i class="bi bi-whatsapp me-2"></i>WhatsApp</strong>
          <span><a href="https://wa.me/<?= e(wa_number(setting('contact_whatsapp'))) ?>" class="text-decoration-none">+<?= e(wa_number(setting('contact_whatsapp'))) ?></a></span>
        </li>
        <?php endif; ?>
        <li class="list-group-item d-flex">
          <strong class="me-3" style="min-width:140px"><i class="bi bi-envelope me-2"></i>Email</strong>
          <span><a href="mailto:<?= e(setting('contact_email')) ?>" class="text-decoration-none"><?= e(setting('contact_email')) ?></a></span>
        </li>
      </ul>
    </div>

    <p class="text-muted small mt-4">For any queries or support, please contact us using the details above.</p>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
