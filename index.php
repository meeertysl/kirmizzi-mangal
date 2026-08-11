<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/logo.php';

$db = km_read_db();
$s = $db['settings'];
$categories = km_sorted_categories($db);
$featured = array_slice(array_values(array_filter($db['products'], fn($p) => $p['available'])), 0, 6);
$phoneHref = 'tel:' . preg_replace('/\s+/', '', $s['phone']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($s['name']) ?> | Mangal &amp; Kebap Restoranı</title>
  <meta name="description" content="<?= e($s['name']) ?> - Odun ateşinde pişen kebaplar, ızgaralar ve geleneksel lezzetler. QR menümüze göz atın.">
  <link rel="icon" href="assets/icon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <header class="site-header">
    <div class="site-header-inner">
      <a href="index.php"><?php km_logo('sm', true); ?></a>
      <nav class="site-nav">
        <a href="#hakkimizda">Hakkımızda</a>
        <a href="#lezzetler">Lezzetler</a>
        <a href="#iletisim">İletişim</a>
        <a href="menu.php" class="nav-cta">Menü</a>
      </nav>
    </div>
  </header>

  <section class="hero">
    <?php km_logo('lg', true); ?>
    <h1><?= e($s['slogan']) ?></h1>
    <p><?= e($s['about']) ?></p>
    <div class="hero-buttons">
      <a href="menu.php" class="btn btn-red">🔥 Menüyü İncele</a>
      <?php if ($s['phone']): ?>
        <a href="<?= e($phoneHref) ?>" class="btn btn-outline">📞 Rezervasyon</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="section" id="hakkimizda">
    <div class="container">
      <h2 class="section-title">Neden <span><?= e($s['name']) ?></span>?</h2>
      <p class="section-sub">Ustalık, tazelik ve ateşin lezzeti bir arada</p>
      <div class="features">
        <div class="feature-card">
          <div class="icon">🥩</div>
          <h3>Günlük Taze Et</h3>
          <p>Etlerimiz her gün taze olarak hazırlanır, özel baharatlarla marine edilir.</p>
        </div>
        <div class="feature-card">
          <div class="icon">🔥</div>
          <h3>Odun Ateşi</h3>
          <p>Tüm kebap ve ızgaralarımız geleneksel yöntemle odun ateşinde pişer.</p>
        </div>
        <div class="feature-card">
          <div class="icon">👨‍🍳</div>
          <h3>Usta Ellerden</h3>
          <p>Yılların tecrübesine sahip ustalarımızın elinden çıkan eşsiz lezzetler.</p>
        </div>
        <div class="feature-card">
          <div class="icon">🏠</div>
          <h3>Sıcak Ortam</h3>
          <p>Ailenizle ve sevdiklerinizle keyifle vakit geçirebileceğiniz samimi atmosfer.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section section-alt" id="lezzetler">
    <div class="container">
      <h2 class="section-title">Öne Çıkan <span>Lezzetler</span></h2>
      <p class="section-sub">Menümüzden seçmeler</p>
      <div class="menu-grid">
        <?php foreach ($featured as $p): ?>
          <div class="menu-card">
            <?php if ($p['image']): ?>
              <img class="menu-card-img" src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
            <?php else: ?>
              <div class="menu-card-img-placeholder">🍖</div>
            <?php endif; ?>
            <div class="menu-card-body">
              <div class="menu-card-top">
                <h3><?= e($p['name']) ?></h3>
                <span class="price"><?= e($p['price']) ?> ₺</span>
              </div>
              <?php if ($p['description']): ?>
                <p class="desc"><?= e($p['description']) ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div style="text-align:center;margin-top:36px">
        <a href="menu.php" class="btn btn-red">Tüm Menüyü Gör (<?= count($categories) ?> kategori)</a>
      </div>
    </div>
  </section>

  <section class="section" id="iletisim">
    <div class="container">
      <h2 class="section-title">Bize <span>Ulaşın</span></h2>
      <p class="section-sub">Sizi ağırlamaktan mutluluk duyarız</p>
      <div class="info-rows">
        <div class="info-card">
          <div class="icon">📍</div>
          <h3>Adres</h3>
          <?php if ($s['mapsUrl']): ?>
            <a href="<?= e($s['mapsUrl']) ?>" target="_blank" rel="noopener noreferrer"><?= e($s['address']) ?></a>
          <?php else: ?>
            <p><?= e($s['address']) ?></p>
          <?php endif; ?>
        </div>
        <div class="info-card">
          <div class="icon">📞</div>
          <h3>Telefon</h3>
          <a href="<?= e($phoneHref) ?>"><?= e($s['phone']) ?></a>
        </div>
        <div class="info-card">
          <div class="icon">🕐</div>
          <h3>Çalışma Saatleri</h3>
          <p><?= e($s['hours']) ?></p>
        </div>
        <?php if ($s['instagram']): $ig = ltrim($s['instagram'], '@'); ?>
          <div class="info-card">
            <div class="icon">📷</div>
            <h3>Instagram</h3>
            <a href="https://instagram.com/<?= e($ig) ?>" target="_blank" rel="noopener noreferrer">@<?= e($ig) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <footer class="site-footer">
    <div class="footer-grid">
      <div>
        <?php km_logo('sm', true); ?>
        <p style="margin-top:12px"><?= e($s['slogan']) ?></p>
      </div>
      <div>
        <h4>İletişim</h4>
        <p><?= e($s['address']) ?></p>
        <p><a href="<?= e($phoneHref) ?>"><?= e($s['phone']) ?></a></p>
        <p><?= e($s['hours']) ?></p>
      </div>
      <div>
        <h4>Menü</h4>
        <?php foreach ($categories as $c): ?>
          <p><a href="menu.php#<?= e($c['id']) ?>"><?= e($c['name']) ?></a></p>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="footer-bottom">© <?= date('Y') ?> <?= e($s['name']) ?>. Tüm hakları saklıdır.</div>
  </footer>
</body>
</html>
