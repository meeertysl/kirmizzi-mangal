<?php
require_once __DIR__ . '/../inc/auth.php';

const ALLOWED_MIME = [
    'image/jpeg' => '.jpg',
    'image/png' => '.png',
    'image/webp' => '.webp',
    'image/gif' => '.gif',
];
const MAX_UPLOAD = 8 * 1024 * 1024;

function back($tab, $msg = null, $err = null)
{
    $q = 'tab=' . urlencode($tab);
    if ($msg) $q .= '&msg=' . urlencode($msg);
    if ($err) $q .= '&err=' . urlencode($err);
    header('Location: index.php?' . $q);
    exit;
}

function handle_image_upload()
{
    if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    $f = $_FILES['image'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Görsel yüklenemedi (hata kodu ' . $f['error'] . ')'];
    }
    if ($f['size'] > MAX_UPLOAD) {
        return [null, "Görsel 8MB'dan büyük olamaz"];
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($f['tmp_name']);
    if (!isset(ALLOWED_MIME[$mime])) {
        return [null, 'Sadece JPG, PNG, WEBP veya GIF yükleyebilirsiniz'];
    }
    if (!is_dir(KM_UPLOAD_DIR)) {
        mkdir(KM_UPLOAD_DIR, 0755, true);
    }
    $name = bin2hex(random_bytes(8)) . ALLOWED_MIME[$mime];
    if (!move_uploaded_file($f['tmp_name'], KM_UPLOAD_DIR . '/' . $name)) {
        return [null, 'Görsel kaydedilemedi'];
    }
    return ['/uploads/' . $name, null];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action = $_POST['action'] ?? '';

// Giriş / çıkış
if ($action === 'login') {
    if (km_login($_POST['password'] ?? '')) {
        header('Location: index.php');
    } else {
        header('Location: index.php?err=' . urlencode('Hatalı şifre'));
    }
    exit;
}

if (!km_is_admin()) {
    header('Location: index.php?err=' . urlencode('Oturum gerekli'));
    exit;
}

if (!km_csrf_check()) {
    header('Location: index.php?err=' . urlencode('Oturum doğrulaması başarısız, tekrar deneyin'));
    exit;
}

if ($action === 'logout') {
    km_logout();
    header('Location: index.php');
    exit;
}

$db = km_read_db();

switch ($action) {
    /* ---------- Ürünler ---------- */
    case 'product_save': {
        $id = trim($_POST['id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $categoryId = $_POST['categoryId'] ?? '';
        if ($name === '') back('products', null, 'Ürün adı gerekli');
        $catExists = array_filter($db['categories'], fn($c) => $c['id'] === $categoryId);
        if (!$catExists) back('products', null, 'Kategori seçiniz');

        [$imageUrl, $imgErr] = handle_image_upload();
        if ($imgErr) back('products', null, $imgErr);

        if ($id !== '') {
            foreach ($db['products'] as &$p) {
                if ($p['id'] === $id) {
                    $p['name'] = $name;
                    $p['categoryId'] = $categoryId;
                    $p['description'] = trim($_POST['description'] ?? '');
                    $p['price'] = (float) str_replace(',', '.', $_POST['price'] ?? '0');
                    if (!empty($_POST['remove_image'])) $p['image'] = '';
                    if ($imageUrl) $p['image'] = $imageUrl;
                }
            }
            unset($p);
            km_write_db($db);
            back('products', 'Ürün güncellendi');
        }

        $siblings = array_filter($db['products'], fn($p) => $p['categoryId'] === $categoryId);
        $maxOrder = $siblings ? max(array_map(fn($p) => $p['order'], $siblings)) : 0;
        $db['products'][] = [
            'id' => km_slugify($name),
            'categoryId' => $categoryId,
            'name' => $name,
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float) str_replace(',', '.', $_POST['price'] ?? '0'),
            'image' => $imageUrl ?? '',
            'available' => true,
            'order' => $maxOrder + 1,
        ];
        km_write_db($db);
        back('products', 'Ürün eklendi');
    }

    case 'product_delete': {
        $id = $_POST['id'] ?? '';
        $db['products'] = array_values(array_filter($db['products'], fn($p) => $p['id'] !== $id));
        km_write_db($db);
        back('products', 'Ürün silindi');
    }

    case 'product_toggle': {
        $id = $_POST['id'] ?? '';
        foreach ($db['products'] as &$p) {
            if ($p['id'] === $id) $p['available'] = !$p['available'];
        }
        unset($p);
        km_write_db($db);
        back('products');
    }

    /* ---------- Kategoriler ---------- */
    case 'category_add': {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') back('categories', null, 'Kategori adı gerekli');
        $orders = array_map(fn($c) => $c['order'], $db['categories']);
        $db['categories'][] = ['id' => km_slugify($name), 'name' => $name, 'order' => ($orders ? max($orders) : 0) + 1];
        km_write_db($db);
        back('categories', 'Kategori eklendi');
    }

    case 'category_rename': {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        if ($name === '') back('categories', null, 'Kategori adı gerekli');
        foreach ($db['categories'] as &$c) {
            if ($c['id'] === $id) $c['name'] = $name;
        }
        unset($c);
        km_write_db($db);
        back('categories', 'Kategori güncellendi');
    }

    case 'category_delete': {
        $id = $_POST['id'] ?? '';
        $db['categories'] = array_values(array_filter($db['categories'], fn($c) => $c['id'] !== $id));
        $db['products'] = array_values(array_filter($db['products'], fn($p) => $p['categoryId'] !== $id));
        km_write_db($db);
        back('categories', 'Kategori silindi');
    }

    case 'category_move': {
        $id = $_POST['id'] ?? '';
        $dir = (int) ($_POST['dir'] ?? 0);
        $sorted = km_sorted_categories($db);
        $idx = array_search($id, array_column($sorted, 'id'), true);
        if ($idx !== false && isset($sorted[$idx + $dir])) {
            $a = $sorted[$idx]['id'];
            $b = $sorted[$idx + $dir]['id'];
            $orderA = $sorted[$idx]['order'];
            $orderB = $sorted[$idx + $dir]['order'];
            foreach ($db['categories'] as &$c) {
                if ($c['id'] === $a) $c['order'] = $orderB;
                elseif ($c['id'] === $b) $c['order'] = $orderA;
            }
            unset($c);
            km_write_db($db);
        }
        back('categories');
    }

    /* ---------- Ayarlar ---------- */
    case 'settings_save': {
        foreach (['name', 'slogan', 'about', 'phone', 'whatsapp', 'address', 'instagram', 'hours', 'mapsUrl'] as $key) {
            if (isset($_POST[$key])) {
                $db['settings'][$key] = trim((string) $_POST[$key]);
            }
        }
        km_write_db($db);
        back('settings', 'Bilgiler kaydedildi');
    }

    case 'password_change': {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!km_check_password($current)) back('settings', null, 'Mevcut şifre hatalı');
        if (strlen($new) < 6) back('settings', null, 'Yeni şifre en az 6 karakter olmalı');
        if ($new !== $confirm) back('settings', null, 'Yeni şifreler eşleşmiyor');
        $db['settings']['adminPassword'] = password_hash($new, PASSWORD_DEFAULT);
        km_write_db($db);
        back('settings', 'Şifre değiştirildi');
    }
}

header('Location: index.php');
exit;
