<?php
require_once __DIR__ . '/config/functions.php';

$tid = tenant_id();

$services = db()->prepare('SELECT * FROM services WHERE tenant_id = ? ORDER BY display_order, id');
$services->execute([$tid]);
$services = $services->fetchAll();

$gallery = db()->prepare('SELECT * FROM gallery WHERE tenant_id = ? ORDER BY display_order, id');
$gallery->execute([$tid]);
$gallery = $gallery->fetchAll();

$testimonials = db()->prepare('SELECT * FROM testimonials WHERE tenant_id = ? ORDER BY display_order, id');
$testimonials->execute([$tid]);
$testimonials = $testimonials->fetchAll();

$achievements = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', setting('achievements'))));
$hero_img = setting('hero_image');

include __DIR__ . '/includes/header.php';
?>

<!-- ===================== HERO ===================== -->
<header class="hero-section" id="home">
  <div class="container">
    <div class="row align-items-center g-5 py-5">
      <div class="col-lg-5 text-center order-1 order-lg-0">
        <div class="hero-photo-wrap">
          <img src="<?= e(img_src($hero_img, 'https://picsum.photos/seed/profile/500/600')) ?>"
               alt="<?= e(setting('site_name')) ?>" class="hero-photo">
        </div>
      </div>
      <div class="col-lg-7 text-center text-lg-start order-0 order-lg-1">
        <span class="hero-eyebrow"><?= e(setting('hero_eyebrow', 'Hello, I am')) ?></span>
        <h1 class="hero-name"><?= e(setting('hero_title', 'Welcome')) ?></h1>
        <?php if (setting('hero_subtitle')): ?>
          <p class="hero-role"><?= e(setting('hero_subtitle')) ?></p>
        <?php endif; ?>
        <p class="hero-desc"><?= nl2br(e(setting('hero_tagline'))) ?></p>
        <div class="hero-actions d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
          <a href="register.php#register-form" class="btn btn-brand btn-lg px-4">Register Now</a>
          <a href="contact.php" class="btn btn-brand-outline btn-lg px-4">Contact Us</a>
        </div>
        <?php $hero_vid = youtube_id(setting('hero_video')); ?>
        <?php if ($hero_vid): ?>
          <div class="hero-video mt-4 mx-auto mx-lg-0 <?= is_youtube_short(setting('hero_video')) ? 'hero-video-short' : '' ?>">
            <iframe
              src="https://www.youtube.com/embed/<?= e($hero_vid) ?>?rel=0&amp;modestbranding=1&amp;playsinline=1&amp;autoplay=1&amp;mute=1&amp;loop=1&amp;playlist=<?= e($hero_vid) ?>"
              title="Featured video" loading="lazy"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              referrerpolicy="strict-origin-when-cross-origin"
              allowfullscreen></iframe>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<!-- ===================== ABOUT ===================== -->
<section class="py-5" id="about">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title"><?= e(setting('about_title', 'About')) ?></h2>
    </div>
    <div class="row g-4 align-items-center">
      <div class="col-lg-7">
        <p class="text-muted fs-5"><?= nl2br(e(setting('about_content'))) ?></p>
      </div>
      <div class="col-lg-5">
        <div class="row g-3">
          <?php foreach ($achievements as $a):
              $parts = explode(' ', $a, 2); ?>
            <div class="col-6">
              <div class="achievement-box text-center p-3 h-100">
                <div class="h3 fw-bold mb-0 brand-text"><?= e($parts[0]) ?></div>
                <small class="text-muted"><?= e($parts[1] ?? '') ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== SERVICES ===================== -->
<section class="py-5 bg-light" id="services">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">Services</h2>
      <p class="text-muted">What I can help you with</p>
    </div>
    <div class="row g-4">
      <?php foreach ($services as $s): ?>
        <div class="col-md-6 col-lg-3">
          <div class="service-card text-center h-100 p-4">
            <div class="service-icon mb-3"><i class="bi <?= e($s['icon']) ?>"></i></div>
            <h5 class="fw-bold"><?= e($s['title']) ?></h5>
            <p class="text-muted small mb-0"><?= e($s['description']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$services): ?><p class="text-center text-muted">Services coming soon.</p><?php endif; ?>
    </div>
  </div>
</section>

<!-- ===================== GALLERY ===================== -->
<section class="py-5" id="gallery">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">Gallery</h2>
    </div>
    <div class="row g-3">
      <?php foreach ($gallery as $g): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <a href="<?= e(img_src($g['image'])) ?>" class="gallery-item d-block" target="_blank" rel="noopener">
            <img src="<?= e(img_src($g['image'])) ?>" alt="<?= e($g['caption']) ?>" class="img-fluid rounded">
            <?php if ($g['caption']): ?><span class="gallery-caption"><?= e($g['caption']) ?></span><?php endif; ?>
          </a>
        </div>
      <?php endforeach; ?>
      <?php if (!$gallery): ?><p class="text-center text-muted">Gallery coming soon.</p><?php endif; ?>
    </div>
  </div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<?php if ($testimonials): ?>
<section class="py-5 bg-light" id="testimonials">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">Testimonials</h2>
    </div>
    <div id="testiCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php foreach ($testimonials as $i => $t): ?>
          <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
            <div class="testimonial-card text-center mx-auto">
              <img src="<?= e(img_src($t['photo'], 'https://ui-avatars.com/api/?name=' . urlencode($t['name']) . '&background=random')) ?>"
                   class="testimonial-photo mb-3" alt="<?= e($t['name']) ?>">
              <p class="fst-italic fs-5">&ldquo;<?= e($t['testimonial']) ?>&rdquo;</p>
              <h6 class="fw-bold mb-0"><?= e($t['name']) ?></h6>
              <small class="text-muted"><?= e($t['designation']) ?></small>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($testimonials) > 1): ?>
      <button class="carousel-control-prev" type="button" data-bs-target="#testiCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span></button>
      <button class="carousel-control-next" type="button" data-bs-target="#testiCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span></button>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== CONTACT ===================== -->
<section class="py-5" id="contact">
  <div class="container">
    <div class="text-center mb-5"><h2 class="section-title">Get in Touch</h2></div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4">
        <div class="contact-box text-center p-4 h-100">
          <i class="bi bi-telephone-fill contact-ic"></i>
          <h6 class="mt-2">Phone</h6>
          <a href="tel:<?= e(setting('contact_phone')) ?>" class="text-decoration-none"><?= e(setting('contact_phone')) ?></a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="contact-box text-center p-4 h-100">
          <i class="bi bi-envelope-fill contact-ic"></i>
          <h6 class="mt-2">Email</h6>
          <a href="mailto:<?= e(setting('contact_email')) ?>" class="text-decoration-none"><?= e(setting('contact_email')) ?></a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="contact-box text-center p-4 h-100">
          <i class="bi bi-geo-alt-fill contact-ic"></i>
          <h6 class="mt-2">Address</h6>
          <span class="text-muted"><?= e(setting('contact_address')) ?></span>
        </div>
      </div>
    </div>
    <div class="text-center mt-4 d-flex justify-content-center gap-2 flex-wrap">
      <?php if (setting('contact_whatsapp')): ?>
        <a href="https://wa.me/<?= e(setting('contact_whatsapp')) ?>" class="btn btn-success btn-lg">
          <i class="bi bi-whatsapp"></i> WhatsApp</a>
      <?php endif; ?>
      <a href="mailto:<?= e(setting('contact_email')) ?>" class="btn btn-primary btn-lg brand-btn">
        <i class="bi bi-envelope"></i> Email Me</a>
    </div>
  </div>
</section>

<!-- ===================== REGISTRATION ===================== -->
<section class="py-5 bg-light" id="register">
  <div class="container">
    <div class="text-center mb-4"><h2 class="section-title">Register</h2>
      <p class="text-muted">Fill in your details and I will get back to you.</p></div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <?php include __DIR__ . '/includes/register-form.php'; ?>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
