<?php
// Kırmızzı Mangal — genel yapılandırma
define('KM_ROOT', dirname(__DIR__));
define('KM_DATA_DIR', getenv('KM_DATA_DIR') ?: KM_ROOT . '/data');
define('KM_UPLOAD_DIR', KM_ROOT . '/uploads');

// HTML çıktıları için kısa kaçış yardımcısı
function e($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

// Statik dosya adresine sürüm ekler — dosya her değiştiğinde
// tarayıcı/CDN önbelleği otomatik olarak kırılır.
function km_asset($relPath, $prefix = '')
{
    $full = KM_ROOT . '/' . ltrim($relPath, '/');
    $v = file_exists($full) ? filemtime($full) : 1;
    return $prefix . $relPath . '?v=' . $v;
}
