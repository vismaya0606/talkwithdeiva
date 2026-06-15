<?php
require_once __DIR__ . '/config/functions.php';
$page_title       = 'Contact | ' . setting('site_name');
$page_description = 'Get in touch with ' . setting('site_name') . '.';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5"><h1 class="section-title">Contact</h1></div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4">
        <div class="contact-box text-center p-4 h-100">
          <i class="bi bi-telephone-fill contact-ic"></i><h6 class="mt-2">Phone</h6>
          <a href="tel:<?= e(setting('contact_phone')) ?>" class="text-decoration-none"><?= e(setting('contact_phone')) ?></a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="contact-box text-center p-4 h-100">
          <i class="bi bi-envelope-fill contact-ic"></i><h6 class="mt-2">Email</h6>
          <a href="mailto:<?= e(setting('contact_email')) ?>" class="text-decoration-none"><?= e(setting('contact_email')) ?></a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="contact-box text-center p-4 h-100">
          <i class="bi bi-geo-alt-fill contact-ic"></i><h6 class="mt-2">Address</h6>
          <span class="text-muted"><?= e(setting('contact_address')) ?></span>
        </div>
      </div>
    </div>
    <div class="text-center mt-4 d-flex justify-content-center gap-2 flex-wrap">
      <?php if (setting('contact_whatsapp')): ?>
        <a href="https://wa.me/<?= e(setting('contact_whatsapp')) ?>" class="btn btn-success btn-lg">
          <i class="bi bi-whatsapp"></i> WhatsApp</a>
      <?php endif; ?>
      <a href="mailto:<?= e(setting('contact_email')) ?>" class="btn brand-btn btn-lg">
        <i class="bi bi-envelope"></i> Email Me</a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
