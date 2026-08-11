<?php
// Yerel geliştirme için: php -S localhost:8000 router.php
// (Sunucuda gerek yok; orada .htaccess devrededir.)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/menu' || $uri === '/menu/') {
    require __DIR__ . '/menu.php';
    return true;
}
if ($uri === '/admin' || $uri === '/admin/') {
    // göreli form/link adreslerinin doğru çözülmesi için tam adrese yönlendir
    header('Location: /admin/index.php');
    return true;
}
if ($uri === '/') {
    require __DIR__ . '/index.php';
    return true;
}
return false; // diğer dosyaları olduğu gibi servis et
