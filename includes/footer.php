<?php
/** Public site footer + sticky "Register Now" button + scripts. */
$wa    = setting('contact_whatsapp');
$mail  = setting('contact_email');
$footer_social = array_filter([
    'facebook'  => setting('social_facebook'),
    'instagram' => setting('social_instagram'),
    'youtube'   => setting('social_youtube'),
    'twitter-x' => setting('social_twitter'),
    'linkedin'  => setting('social_linkedin'),
]);
?>
<footer class="brand-footer text-center text-white py-4">
  <div class="container">
    <div class="d-flex justify-content-center gap-3 mb-3 fs-5">
      <?php foreach ($footer_social as $icon => $url): ?>
        <a class="text-white" href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e($icon) ?>"><i class="bi bi-<?= e($icon) ?>"></i></a>
      <?php endforeach; ?>
      <?php if ($wa): ?><a class="text-white" href="https://wa.me/<?= e($wa) ?>" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a><?php endif; ?>
      <?php if ($mail): ?><a class="text-white" href="mailto:<?= e($mail) ?>" aria-label="Email"><i class="bi bi-envelope-fill"></i></a><?php endif; ?>
    </div>
    <p class="mb-0 small"><?= e(setting('footer_text', '© ' . date('Y'))) ?></p>
  </div>
</footer>

<!-- Sticky Register button (every page) -->
<a href="<?= e(base_url()) ?>register.php#register-form" id="stickyRegister" class="sticky-register">
  <i class="bi bi-pencil-square me-1"></i> Register Now
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('assets/js/main.js')) ?>"></script>
</body>
</html>
