<?php
/** Dynamic XML sitemap. Served at /sitemap.xml via .htaccess rewrite. */
require_once __DIR__ . '/config/functions.php';
header('Content-Type: application/xml; charset=utf-8');

$base  = base_url();
$pages = ['index.php', 'about.php', 'contact.php', 'register.php'];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $p): ?>
  <url>
    <loc><?= e($base . $p) ?></loc>
    <changefreq>weekly</changefreq>
    <priority><?= $p === 'index.php' ? '1.0' : '0.7' ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
