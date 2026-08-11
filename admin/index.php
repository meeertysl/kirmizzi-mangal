<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/logo.php';

$msg = $_GET['msg'] ?? null;
$err = $_GET['err'] ?? null;
$authed = km_is_admin();
$tab = $_GET['tab'] ?? 'products';
if (!in_array($tab, ['products', 'categories', 'qr', 'settings'], true)) {
    $tab = 'products';
}

$db = km_read_db();
$categories = km_sorted_categories($db);
$products = km_sorted_products($db);
$s = $db['settings'];

$editing = null;
if ($authed && $tab === 'products' && !empty($_GET['edit'])) {
    foreach ($products as $p) {
        if ($p['id'] === $_GET['edit']) $editing = $p;
    }
}

function cat_name($categories, $id)
{
    foreach ($categories as $c) {
        if ($c['id'] === $id) return $c['name'];
    }
    return '-';
}

// admin/ klasöründen kök dosyalara erişim
function img_src($url)
{
    return $url ? '..' . $url : '';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Yönetim Paneli | <?= e($s['name']) ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" href="../assets/icon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php if (!$authed): ?>
  <div class="login-wrap">
    <form class="login-card" method="post" action="actions.php">
      <input type="hidden" name="action" value="login">
      <?php km_logo('md'); ?>
      <h1>Yönetim Paneli</h1>
      <?php if ($err): ?><div class="error-msg"><?= e($err) ?></div><?php endif; ?>
      <input type="password" name="password" placeholder="Admin şifresi" autofocus>
      <button class="btn btn-red">Giriş Yap</button>
    </form>
  </div>
<?php else: ?>
  <div class="admin-shell">
    <div class="admin-topbar">
      <div class="title">KIRMIZZI <span>MANGAL</span> · Yönetim</div>
      <div style="display:flex;gap:10px">
        <a href="../menu.php" target="_blank" class="btn-sm btn-ghost" style="color:#eee;border-color:#555;text-decoration:none;line-height:1.4">Menüyü Gör ↗</a>
        <form method="post" action="actions.php" style="margin:0">
          <?= km_csrf_field() ?>
          <input type="hidden" name="action" value="logout">
          <button class="btn-sm btn-primary">Çıkış</button>
        </form>
      </div>
    </div>

    <main class="admin-main">
      <?php if ($msg): ?><div class="success-msg"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="error-msg"><?= e($err) ?></div><?php endif; ?>

      <div class="admin-tabs">
        <a class="admin-tab<?= $tab === 'products' ? ' active' : '' ?>" href="?tab=products">🍖 Ürünler</a>
        <a class="admin-tab<?= $tab === 'categories' ? ' active' : '' ?>" href="?tab=categories">📂 Kategoriler</a>
        <a class="admin-tab<?= $tab === 'qr' ? ' active' : '' ?>" href="?tab=qr">🔳 QR Kod</a>
        <a class="admin-tab<?= $tab === 'settings' ? ' active' : '' ?>" href="?tab=settings">⚙️ Ayarlar</a>
      </div>

      <?php if ($tab === 'products'): ?>
        <div class="panel">
          <h2><?= $editing ? 'Ürünü Düzenle' : 'Yeni Ürün Ekle' ?></h2>
          <form method="post" action="actions.php" enctype="multipart/form-data">
            <?= km_csrf_field() ?>
            <input type="hidden" name="action" value="product_save">
            <input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
            <div class="form-grid">
              <div class="field">
                <label>Ürün Adı *</label>
                <input name="name" value="<?= e($editing['name'] ?? '') ?>" placeholder="Örn: Adana Kebap" required>
              </div>
              <div class="field">
                <label>Kategori *</label>
                <select name="categoryId" required>
                  <option value="">Seçiniz...</option>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c['id']) ?>"<?= ($editing['categoryId'] ?? '') === $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label>Fiyat (₺) *</label>
                <input type="number" name="price" min="0" step="0.01" value="<?= e($editing['price'] ?? '') ?>" placeholder="250" required>
              </div>
              <div class="field">
                <label>Görsel</label>
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($editing['image'])): ?>
                  <span class="hint" style="display:flex;align-items:center;gap:8px;margin-top:4px">
                    <img src="<?= e(img_src($editing['image'])) ?>" alt="" style="width:36px;height:36px;border-radius:6px;object-fit:cover">
                    <label style="display:flex;align-items:center;gap:4px;font-weight:400"><input type="checkbox" name="remove_image" value="1"> Görseli kaldır</label>
                  </span>
                <?php endif; ?>
              </div>
            </div>
            <div class="field" style="margin-top:14px">
              <label>Açıklama</label>
              <textarea name="description" placeholder="Ürün içeriği, servis detayı..."><?= e($editing['description'] ?? '') ?></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;align-items:center">
              <button class="btn-sm btn-primary"><?= $editing ? 'Güncelle' : 'Ekle' ?></button>
              <?php if ($editing): ?>
                <a class="btn-sm btn-ghost" href="?tab=products" style="text-decoration:none">Vazgeç</a>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <div class="panel">
          <h2>Ürünler (<?= count($products) ?>)</h2>
          <div style="overflow-x:auto">
            <table class="admin-table">
              <thead>
                <tr><th></th><th>Ürün</th><th>Kategori</th><th>Fiyat</th><th>Durum</th><th></th></tr>
              </thead>
              <tbody>
                <?php foreach ($products as $p): ?>
                  <tr>
                    <td>
                      <?php if ($p['image']): ?>
                        <img class="admin-thumb" src="<?= e(img_src($p['image'])) ?>" alt="">
                      <?php else: ?>
                        <div class="admin-thumb-placeholder">🍖</div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong><?= e($p['name']) ?></strong>
                      <?php if ($p['description']): ?>
                        <div class="hint" style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($p['description']) ?></div>
                      <?php endif; ?>
                    </td>
                    <td><?= e(cat_name($categories, $p['categoryId'])) ?></td>
                    <td><strong style="color:var(--red)"><?= e($p['price']) ?> ₺</strong></td>
                    <td>
                      <form method="post" action="actions.php" style="margin:0">
                        <?= km_csrf_field() ?>
                        <input type="hidden" name="action" value="product_toggle">
                        <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                        <label class="switch" title="<?= $p['available'] ? 'Satışta' : 'Tükendi' ?>">
                          <input type="checkbox"<?= $p['available'] ? ' checked' : '' ?> onchange="this.form.submit()">
                          <span class="slider"></span>
                        </label>
                      </form>
                    </td>
                    <td>
                      <div class="row-actions">
                        <a class="btn-sm btn-ghost" style="text-decoration:none" href="?tab=products&amp;edit=<?= e(urlencode($p['id'])) ?>">Düzenle</a>
                        <form method="post" action="actions.php" style="margin:0" onsubmit="return confirm('&quot;<?= e($p['name']) ?>&quot; silinsin mi?')">
                          <?= km_csrf_field() ?>
                          <input type="hidden" name="action" value="product_delete">
                          <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                          <button class="btn-sm btn-danger">Sil</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (!$products): ?>
                  <tr><td colspan="6" style="text-align:center;color:var(--gray);padding:30px">Henüz ürün yok. Yukarıdan ekleyebilirsiniz.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      <?php elseif ($tab === 'categories'): ?>
        <div class="panel">
          <h2>Yeni Kategori</h2>
          <form method="post" action="actions.php" style="display:flex;gap:10px;flex-wrap:wrap">
            <?= km_csrf_field() ?>
            <input type="hidden" name="action" value="category_add">
            <input name="name" placeholder="Örn: Çorbalar" required style="flex:1;min-width:200px;padding:10px 13px;border:1.5px solid var(--border);border-radius:9px">
            <button class="btn-sm btn-primary">Ekle</button>
          </form>
        </div>

        <div class="panel">
          <h2>Kategoriler (<?= count($categories) ?>)</h2>
          <table class="admin-table">
            <thead>
              <tr><th>Sıra</th><th>Kategori</th><th>Ürün Sayısı</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $i => $c): ?>
                <tr>
                  <td>
                    <div class="row-actions">
                      <form method="post" action="actions.php" style="margin:0">
                        <?= km_csrf_field() ?>
                        <input type="hidden" name="action" value="category_move">
                        <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                        <input type="hidden" name="dir" value="-1">
                        <button class="btn-sm btn-ghost"<?= $i === 0 ? ' disabled' : '' ?>>↑</button>
                      </form>
                      <form method="post" action="actions.php" style="margin:0">
                        <?= km_csrf_field() ?>
                        <input type="hidden" name="action" value="category_move">
                        <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                        <input type="hidden" name="dir" value="1">
                        <button class="btn-sm btn-ghost"<?= $i === count($categories) - 1 ? ' disabled' : '' ?>>↓</button>
                      </form>
                    </div>
                  </td>
                  <td>
                    <form method="post" action="actions.php" style="display:flex;gap:8px;margin:0">
                      <?= km_csrf_field() ?>
                      <input type="hidden" name="action" value="category_rename">
                      <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                      <input name="name" value="<?= e($c['name']) ?>" style="padding:6px 10px;border:1.5px solid var(--border);border-radius:8px">
                      <button class="btn-sm btn-ghost">Kaydet</button>
                    </form>
                  </td>
                  <td><?= count(km_sorted_products($db, $c['id'])) ?></td>
                  <td>
                    <form method="post" action="actions.php" style="margin:0" onsubmit="return confirm('&quot;<?= e($c['name']) ?>&quot; silinsin mi? Kategorideki ürünler de silinir!')">
                      <?= km_csrf_field() ?>
                      <input type="hidden" name="action" value="category_delete">
                      <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                      <button class="btn-sm btn-danger">Sil</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      <?php elseif ($tab === 'qr'): ?>
        <div class="panel">
          <h2>QR Menü Kodu</h2>
          <div class="qr-preview">
            <canvas id="qrcanvas" width="280" height="280"></canvas>
            <p class="hint">Bu kod <strong id="qrurl"></strong> adresine yönlendirir.</p>
            <a href="#" id="qrdl" class="btn btn-red">⬇ QR Kodu İndir (PNG)</a>
            <p class="hint" style="max-width:480px;text-align:center">
              İndirdiğiniz yüksek çözünürlüklü QR kodu masa kartlarına, broşürlere veya vitrine
              bastırabilirsiniz. Müşterileriniz telefon kamerasıyla okutarak menüye ulaşır.
            </p>
          </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>
        <script>
          (function () {
            const url = location.origin + "/menu.php";
            document.getElementById("qrurl").textContent = url;
            const opts = { margin: 2, color: { dark: "#1a1a1a", light: "#ffffff" } };
            QRCode.toCanvas(document.getElementById("qrcanvas"), url, { ...opts, width: 280 });
            document.getElementById("qrdl").addEventListener("click", async (e) => {
              e.preventDefault();
              const c = document.createElement("canvas");
              await QRCode.toCanvas(c, url, { ...opts, width: 1000 });
              const a = document.createElement("a");
              a.href = c.toDataURL("image/png");
              a.download = "kirmizzi-mangal-qr.png";
              a.click();
            });
          })();
        </script>

      <?php elseif ($tab === 'settings'): ?>
        <div class="panel">
          <h2>Restoran Bilgileri</h2>
          <form method="post" action="actions.php">
            <?= km_csrf_field() ?>
            <input type="hidden" name="action" value="settings_save">
            <div class="form-grid">
              <div class="field"><label>Restoran Adı</label><input name="name" value="<?= e($s['name']) ?>"></div>
              <div class="field"><label>Slogan</label><input name="slogan" value="<?= e($s['slogan']) ?>"></div>
              <div class="field"><label>Telefon</label><input name="phone" value="<?= e($s['phone']) ?>"></div>
              <div class="field"><label>WhatsApp (opsiyonel)</label><input name="whatsapp" value="<?= e($s['whatsapp']) ?>" placeholder="05xxxxxxxxx"></div>
              <div class="field"><label>Instagram (opsiyonel)</label><input name="instagram" value="<?= e($s['instagram']) ?>" placeholder="kirmizzimangal"></div>
              <div class="field"><label>Çalışma Saatleri</label><input name="hours" value="<?= e($s['hours']) ?>"></div>
              <div class="field"><label>Google Maps Linki (opsiyonel)</label><input name="mapsUrl" value="<?= e($s['mapsUrl']) ?>" placeholder="https://maps.app.goo.gl/..."></div>
            </div>
            <div class="field" style="margin-top:14px"><label>Adres</label><textarea name="address"><?= e($s['address']) ?></textarea></div>
            <div class="field" style="margin-top:14px"><label>Hakkımızda Yazısı</label><textarea name="about"><?= e($s['about']) ?></textarea></div>
            <button class="btn-sm btn-primary" style="margin-top:16px">Kaydet</button>
          </form>
        </div>

        <div class="panel">
          <h2>Admin Şifresi</h2>
          <form method="post" action="actions.php">
            <?= km_csrf_field() ?>
            <input type="hidden" name="action" value="password_change">
            <div class="form-grid">
              <div class="field"><label>Mevcut Şifre</label><input type="password" name="current_password" required></div>
              <div class="field"><label>Yeni Şifre</label><input type="password" name="new_password" required></div>
              <div class="field"><label>Yeni Şifre (Tekrar)</label><input type="password" name="confirm_password" required></div>
            </div>
            <button class="btn-sm btn-primary" style="margin-top:16px">Şifreyi Değiştir</button>
            <p class="hint" style="margin-top:10px">Varsayılan şifre <strong>admin123</strong>'tür. Güvenlik için ilk girişte değiştirmeniz önerilir.</p>
          </form>
        </div>
      <?php endif; ?>
    </main>
  </div>
<?php endif; ?>
</body>
</html>
