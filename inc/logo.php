<?php
require_once __DIR__ . '/config.php';

/*
 * Orijinal logo görseli (assets/logo.png — şeffaf arka planlı).
 * Koyu zeminlerde ($light=true) logo, orijinalindeki gibi beyaz
 * yuvarlatılmış bir zemin kartı üzerinde gösterilir.
 */
function km_logo($size = 'md', $light = false)
{
    $img = '<img class="logo-img logo-' . $size . '" src="/assets/logo.png" alt="KIRMIZZI MANGAL">';
    if ($light) {
        echo '<span class="logo-badge logo-badge-' . $size . '">' . $img . '</span>';
    } else {
        echo $img;
    }
}
