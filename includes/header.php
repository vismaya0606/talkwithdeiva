<?php
/**
 * Public site header: dynamic SEO meta, theme colours and navigation.
 * Expects optional $page_title / $page_description before inclusion.
 */
require_once __DIR__ . '/../config/functions.php';

$site_name = setting('site_name', 'My Website');
$primary   = setting('primary_color', '#E8112D');
$secondary = setting('secondary_color', '#F2867D');
$logo      = setting('logo');
$favicon   = setting('favicon');

// Social media links (only non-empty ones are shown).
$social = array_filter([
    'facebook'  => setting('social_facebook'),
    'instagram' => setting('social_instagram'),
    'youtube'   => setting('social_youtube'),
    'twitter-x' => setting('social_twitter'),
    'linkedin'  => setting('social_linkedin'),
]);

$title = $page_title       ?? setting('meta_title', $site_name);
$desc  = $page_description ?? setting('meta_description', '');
$ogimg = setting('og_image') ?: setting('hero_image');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="keywords" content="<?= e(setting('meta_keywords')) ?>">
<link rel="canonical" href="<?= e(base_url() . ltrim($_SERVER['REQUEST_URI'] ?? '', '/')) ?>">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:site_name" content="<?= e($site_name) ?>">
<meta property="og:url" content="<?= e(base_url()) ?>">
<?php if ($ogimg): ?><meta property="og:image" content="<?= e(img_src($ogimg)) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($desc) ?>">

<?php if ($favicon): ?>
<link rel="icon" href="<?= e(img_src($favicon)) ?>">
<?php endif; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= e(asset('assets/css/style.css')) ?>" rel="stylesheet">

<style>
  :root{
    --brand-primary: <?= e($primary) ?>;
    --brand-secondary: <?= e($secondary) ?>;
  }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top brand-nav">
  <div class="container">
    <a class="navbar-brand fw-bold fs-5" href="<?= e(base_url()) ?>index.php">
      <?= e($site_name) ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url()) ?>index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url()) ?>about.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url()) ?>index.php#gallery">Gallery</a></li>
      </ul>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-brand btn-sm px-3" href="<?= e(base_url()) ?>register.php#register-form">Register Now</a>
        <a class="btn btn-brand-outline-light btn-sm px-3" href="<?= e(base_url()) ?>contact.php">Contact Us</a>
      </div>
    </div>
  </div>
</nav>
<div class="nav-spacer"></div>

<?php if ($social): ?>
<div class="social-rail d-none d-lg-flex">
  <?php foreach ($social as $icon => $url): ?>
    <a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e($icon) ?>">
      <i class="bi bi-<?= e($icon) ?>"></i>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
