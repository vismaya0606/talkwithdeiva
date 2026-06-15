<?php
require_once __DIR__ . '/config/functions.php';
$achievements = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', setting('achievements'))));
$page_title       = setting('about_title', 'About') . ' | ' . setting('site_name');
$page_description = setting('about_content');
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5"><h1 class="section-title"><?= e(setting('about_title', 'About')) ?></h1></div>
    <div class="row g-5 align-items-center">
      <div class="col-lg-5 text-center">
        <img src="<?= e(img_src(setting('hero_image'), 'https://picsum.photos/seed/profile/500/500')) ?>"
             class="img-fluid rounded shadow" alt="<?= e(setting('site_name')) ?>">
      </div>
      <div class="col-lg-7">
        <p class="fs-5 text-muted"><?= nl2br(e(setting('about_content'))) ?></p>
        <div class="row g-3 mt-2">
          <?php foreach ($achievements as $a): $p = explode(' ', $a, 2); ?>
            <div class="col-6 col-md-4">
              <div class="achievement-box text-center p-3 h-100">
                <div class="h3 fw-bold mb-0 brand-text"><?= e($p[0]) ?></div>
                <small class="text-muted"><?= e($p[1] ?? '') ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <a href="register.php#register-form" class="btn brand-btn btn-lg mt-4">Register Now</a>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
