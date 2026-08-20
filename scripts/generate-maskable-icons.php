<?php

/**
 * Regenerates the maskable PWA icons from public/images/logo.png, scaling
 * it down to fit within the ~65% safe zone maskable icons require (Android
 * adaptive icon masks can crop content outside a centered ~80%-diameter
 * circle). Run: php scripts/generate-maskable-icons.php
 */

$logoPath = __DIR__.'/../public/images/logo.png';
$outDir = __DIR__.'/../public/icons';

$logo = imagecreatefrompng($logoPath);
$logoW = imagesx($logo);
$logoH = imagesy($logo);

function makeMaskable(\GdImage $logo, int $logoW, int $logoH, int $size, float $safeZoneScale): \GdImage
{
    $img = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $white);

    $maxDim = $size * $safeZoneScale;
    $ratio = min($maxDim / $logoW, $maxDim / $logoH);
    $destW = (int) round($logoW * $ratio);
    $destH = (int) round($logoH * $ratio);
    $destX = (int) (($size - $destW) / 2);
    $destY = (int) (($size - $destH) / 2);

    imagecopyresampled($img, $logo, $destX, $destY, 0, 0, $destW, $destH, $logoW, $logoH);

    return $img;
}

foreach ([192, 512] as $size) {
    $maskable = makeMaskable($logo, $logoW, $logoH, $size, 0.65);
    imagepng($maskable, "$outDir/icon-$size-maskable.png");
    imagedestroy($maskable);
}

echo "Generated maskable icons in $outDir\n";
