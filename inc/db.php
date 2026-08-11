<?php
require_once __DIR__ . '/config.php';

function km_default_db()
{
    return [
        'settings' => [
            'name' => 'Kırmızzı Mangal',
            'slogan' => 'Ateşin ve lezzetin buluştuğu yer',
            'about' => 'Kırmızzı Mangal olarak en taze etleri, ustalıkla hazırlanan mezeleri ve geleneksel mangal lezzetlerini sofranıza getiriyoruz. Odun ateşinde, ustaların elinde pişen kebaplarımızla damaklarda iz bırakıyoruz.',
            'phone' => '0500 000 00 00',
            'whatsapp' => '',
            'address' => 'Adres bilgisini admin panelden güncelleyiniz',
            'instagram' => '',
            'hours' => 'Her gün 11:00 - 23:00',
            'mapsUrl' => '',
            'adminPassword' => null, // password_hash çıktısı; null = varsayılan "admin123"
        ],
        'categories' => [
            ['id' => 'kebaplar', 'name' => 'Kebaplar', 'order' => 1],
            ['id' => 'izgaralar', 'name' => 'Izgaralar', 'order' => 2],
            ['id' => 'mezeler', 'name' => 'Mezeler & Salatalar', 'order' => 3],
            ['id' => 'icecekler', 'name' => 'İçecekler', 'order' => 4],
            ['id' => 'tatlilar', 'name' => 'Tatlılar', 'order' => 5],
        ],
        'products' => [
            ['id' => 'adana-kebap', 'categoryId' => 'kebaplar', 'name' => 'Adana Kebap', 'description' => 'Zırh ile çekilmiş dana eti, közlenmiş domates ve biber ile', 'price' => 250, 'image' => '', 'available' => true, 'order' => 1],
            ['id' => 'urfa-kebap', 'categoryId' => 'kebaplar', 'name' => 'Urfa Kebap', 'description' => 'Acısız, özel baharatlarla hazırlanmış kebap', 'price' => 250, 'image' => '', 'available' => true, 'order' => 2],
            ['id' => 'kusbasi-izgara', 'categoryId' => 'izgaralar', 'name' => 'Kuşbaşı Izgara', 'description' => 'Marine edilmiş dana kuşbaşı, mangalda', 'price' => 300, 'image' => '', 'available' => true, 'order' => 1],
            ['id' => 'ezme', 'categoryId' => 'mezeler', 'name' => 'Ezme', 'description' => 'Acılı, taze domates ve biberle', 'price' => 60, 'image' => '', 'available' => true, 'order' => 1],
            ['id' => 'ayran', 'categoryId' => 'icecekler', 'name' => 'Ayran', 'description' => '', 'price' => 30, 'image' => '', 'available' => true, 'order' => 1],
            ['id' => 'kunefe', 'categoryId' => 'tatlilar', 'name' => 'Künefe', 'description' => 'Antep fıstıklı, sıcak servis', 'price' => 120, 'image' => '', 'available' => true, 'order' => 1],
        ],
    ];
}

function km_db_path()
{
    return KM_DATA_DIR . '/db.json';
}

function km_read_db()
{
    if (!is_dir(KM_DATA_DIR)) {
        mkdir(KM_DATA_DIR, 0755, true);
    }
    $path = km_db_path();
    if (!file_exists($path)) {
        km_write_db(km_default_db());
    }
    $raw = file_get_contents($path);
    $db = json_decode($raw, true);
    return is_array($db) ? $db : km_default_db();
}

function km_write_db($db)
{
    if (!is_dir(KM_DATA_DIR)) {
        mkdir(KM_DATA_DIR, 0755, true);
    }
    $tmp = km_db_path() . '.tmp';
    file_put_contents($tmp, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    rename($tmp, km_db_path());
}

function km_slugify($text)
{
    $map = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u', 'Ç' => 'c', 'Ğ' => 'g', 'İ' => 'i', 'Ö' => 'o', 'Ş' => 's', 'Ü' => 'u', 'I' => 'i'];
    $base = strtr($text, $map);
    $base = strtolower($base);
    $base = preg_replace('/[^a-z0-9]+/', '-', $base);
    $base = trim($base, '-');
    return ($base !== '' ? $base : 'urun') . '-' . bin2hex(random_bytes(3));
}

function km_sorted_categories($db)
{
    $cats = $db['categories'];
    usort($cats, fn($a, $b) => $a['order'] <=> $b['order']);
    return $cats;
}

function km_sorted_products($db, $categoryId = null)
{
    $items = array_filter($db['products'], fn($p) => $categoryId === null || $p['categoryId'] === $categoryId);
    usort($items, fn($a, $b) => $a['order'] <=> $b['order']);
    return array_values($items);
}
