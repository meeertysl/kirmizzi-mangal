<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/logo.php';

$db = km_read_db();
$s = $db['settings'];
$sections = [];
foreach (km_sorted_categories($db) as $c) {
    $items = km_sorted_products($db, $c['id']);
    if ($items) {
        $sections[] = ['id' => $c['id'], 'name' => $c['name'], 'items' => $items];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Menü | <?= e($s['name']) ?></title>
  <meta name="description" content="<?= e($s['name']) ?> QR menü - kebaplar, ızgaralar, mezeler ve daha fazlası.">
  <link rel="icon" href="assets/icon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(km_asset('assets/style.css')) ?>">
</head>
<body>
  <div class="menu-header">
    <a href="index.php"><?php km_logo('sm', true); ?></a>
    <p style="margin-top:6px;font-size:0.9rem;color:#c9c1b6"><?= e($s['slogan']) ?></p>
  </div>

  <?php if ($sections): ?>
    <nav class="category-tabs" id="category-tabs">
      <?php foreach ($sections as $i => $sec): ?>
        <a href="#<?= e($sec['id']) ?>" class="category-tab<?= $i === 0 ? ' active' : '' ?>" data-target="<?= e($sec['id']) ?>"><?= e($sec['name']) ?></a>
      <?php endforeach; ?>
    </nav>
  <?php endif; ?>

  <div class="container" style="padding-bottom:60px">
    <?php foreach ($sections as $sec): ?>
      <section class="menu-section" id="<?= e($sec['id']) ?>">
        <h2><?= e($sec['name']) ?></h2>
        <div class="menu-list">
          <?php foreach ($sec['items'] as $p): ?>
            <div class="menu-item<?= $p['available'] ? '' : ' unavailable' ?>">
              <?php if ($p['image']): ?>
                <img class="menu-item-img" src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
              <?php else: ?>
                <div class="menu-item-img-placeholder">🍖</div>
              <?php endif; ?>
              <div class="menu-item-body">
                <h3><?= e($p['name']) ?></h3>
                <?php if ($p['description']): ?>
                  <p class="desc"><?= e($p['description']) ?></p>
                <?php endif; ?>
                <?php if (!$p['available']): ?>
                  <span class="badge-unavailable">Tükendi</span>
                <?php endif; ?>
              </div>
              <span class="price"><?= e($p['price']) ?> ₺</span>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>

    <?php if (!$sections): ?>
      <p style="text-align:center;padding:60px 0;color:var(--gray)">Menü henüz hazırlanıyor, çok yakında burada!</p>
    <?php endif; ?>
  </div>

  <footer class="site-footer" style="margin-top:0">
    <div class="footer-bottom" style="border-top:none;margin-top:0;padding-top:0">
      © <?= date('Y') ?> <?= e($s['name']) ?> · <?= e($s['phone']) ?>
    </div>
  </footer>

  <script>
    (function () {
      const tabs = document.querySelectorAll(".category-tab");
      if (!tabs.length) return;
      function activate(id) {
        tabs.forEach(t => t.classList.toggle("active", t.dataset.target === id));
      }
      const observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
          if (entry.isIntersecting) activate(entry.target.id);
        }
      }, { rootMargin: "-20% 0px -70% 0px" });
      tabs.forEach(t => {
        const el = document.getElementById(t.dataset.target);
        if (el) observer.observe(el);
        t.addEventListener("click", () => activate(t.dataset.target));
      });
    })();
  </script>
</body>
</html>
