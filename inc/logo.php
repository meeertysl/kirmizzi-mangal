<?php
require_once __DIR__ . '/config.php';

/*
 * Orijinal logonun SVG çizimi:
 * - Üstte: çatal (sola yatık) + alev + spatula (sağa yatık)
 * - Ortada: KIRMIZZI — batı/western tarzı, gövdeleri ortadan çentikli harfler
 * - Altta: MAN + çapraz satırlar (cleaver) + GAL
 */

function km_logo_stem($x)
{
    return "M{$x},0 h24 v42 l-7,8 7,8 v42 h-24 v-42 l7,-8 -7,-8 Z";
}

function km_logo($size = 'md', $light = false)
{
    $scale = ['sm' => 0.7, 'md' => 1, 'lg' => 1.5][$size] ?? 1;
    $red = '#d1201f';
    $dark = $light ? '#f5f0eb' : '#1f1f1f';
    $inner = $light ? '#2b2b2b' : '#f5f0eb';
    $hole = $light ? '#1a1a1a' : '#faf7f2';
    $mangalCls = $light ? 'logo-mangal logo-mangal-light' : 'logo-mangal';

    $paths = [
        // K (0-70)
        km_logo_stem(0),
        'M24,54 L46,0 H70 L38,54 Z',
        'M24,46 H38 L70,100 H46 Z',
        // I (84-108)
        km_logo_stem(84),
        // R (122-188)
        km_logo_stem(122),
        'M146,0 H172 Q188,0 188,22 Q188,44 172,44 H146 V28 H166 Q172,28 172,22 Q172,16 166,16 H146 Z',
        'M150,44 L188,100 H164 L146,54 V44 Z',
        // M (202-286)
        km_logo_stem(202),
        km_logo_stem(262),
        'M222,0 H240 L244,26 L248,0 H266 L244,58 Z',
        // I (300-324)
        km_logo_stem(300),
        // Z (338-398)
        'M338,0 H398 V16 L364,84 H398 V100 H338 V84 L372,16 H338 Z',
        // Z (412-472)
        'M412,0 H472 V16 L438,84 H472 V100 H412 V84 L446,16 H412 Z',
        // I (486-510)
        km_logo_stem(486),
    ];

    $cleaver = '<path d="M4,8 H22 Q28,8 28,14 Q28,20 22,20 H8 Q4,20 4,14 Z" fill="' . $dark . '"/>'
        . '<rect x="26" y="11" width="18" height="6" rx="3" fill="' . $dark . '"/>'
        . '<circle cx="40" cy="14" r="1.8" fill="' . $hole . '"/>';

    $word = '';
    foreach ($paths as $d) {
        $word .= '<path d="' . $d . '" fill="' . $red . '"/>';
    }

    echo '<div class="logo" style="--logo-scale:' . $scale . '">'
        // Üst ikonlar: çatal + alev + spatula
        . '<svg class="logo-flame" viewBox="0 0 100 90" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
        . '<path d="M50 2 C47 14 36 21 33 36 C30 49 36 60 45 64 C40 55 43 47 50 39 C57 47 60 55 55 64 C64 60 70 49 67 36 C64 21 53 14 50 2 Z" fill="' . $dark . '"/>'
        . '<path d="M50 30 C48 38 42 42 42 51 C42 58 46 62 50 63 C54 62 58 58 58 51 C58 42 52 38 50 30 Z" fill="' . $inner . '"/>'
        . '<g transform="rotate(-35 20 40)" fill="' . $dark . '">'
        . '<rect x="17.5" y="34" width="5" height="36" rx="2.5"/>'
        . '<path d="M10 14 v18 q0 6 6 7 h8 q6 -1 6 -7 V14 h-4 v16 h-4 V14 h-4 v16 h-4 V14 Z"/>'
        . '</g>'
        . '<g transform="rotate(35 80 40)" fill="' . $dark . '">'
        . '<rect x="77.5" y="32" width="5" height="38" rx="2.5"/>'
        . '<rect x="69" y="10" width="22" height="26" rx="5"/>'
        . '<rect x="73.5" y="14" width="3" height="18" rx="1.5" fill="' . $inner . '"/>'
        . '<rect x="78.5" y="14" width="3" height="18" rx="1.5" fill="' . $inner . '"/>'
        . '<rect x="83.5" y="14" width="3" height="18" rx="1.5" fill="' . $inner . '"/>'
        . '</g>'
        . '</svg>'
        // KIRMIZZI
        . '<svg class="logo-word" viewBox="0 0 510 100" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="KIRMIZZI">' . $word . '</svg>'
        // MAN + çapraz satırlar + GAL
        . '<div class="logo-mangal-row" aria-label="MANGAL">'
        . '<span class="' . $mangalCls . '">MAN</span>'
        . '<svg class="logo-cleavers" viewBox="0 0 48 28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
        . '<g transform="rotate(40 24 14)">' . $cleaver . '</g>'
        . '<g transform="rotate(-40 24 14)">' . $cleaver . '</g>'
        . '</svg>'
        . '<span class="' . $mangalCls . '">GAL</span>'
        . '</div>'
        . '</div>';
}
