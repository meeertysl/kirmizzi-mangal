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
