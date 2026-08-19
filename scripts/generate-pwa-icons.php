<?php

/**
 * One-off generator for the PWA app icons. Draws a simple church glyph
 * (dome + cross + base) on the brand primary color, matching the icon
 * used on the login screen. Run: php scripts/generate-pwa-icons.php
 */

function drawChurchIcon(int $size, string $bg, bool $maskableSafeZone = false): \GdImage
{
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    [$r, $g, $b] = sscanf($bg, '#%02x%02x%02x');
    $bgColor = imagecolorallocate($img, $r, $g, $b);
    $white = imagecolorallocate($img, 255, 255, 255);

    $radius = (int) ($size * 0.22);
    imagefilledrectangle($img, $radius, 0, $size - $radius, $size, $bgColor);
    imagefilledrectangle($img, 0, $radius, $size, $size - $radius, $bgColor);
    imagefilledellipse($img, $radius, $radius, $radius * 2, $radius * 2, $bgColor);
    imagefilledellipse($img, $size - $radius, $radius, $radius * 2, $radius * 2, $bgColor);
    imagefilledellipse($img, $radius, $size - $radius, $radius * 2, $radius * 2, $bgColor);
    imagefilledellipse($img, $size - $radius, $size - $radius, $radius * 2, $radius * 2, $bgColor);

    // Glyph scale shrinks within a safe zone for maskable icons (~80% center).
    $scale = $maskableSafeZone ? 0.62 : 0.8;
    $cx = $size / 2;

    $baseW = $size * $scale * 0.62;
    $baseH = $size * $scale * 0.38;
    $baseTop = $size * (1 - $scale) / 2 + $size * $scale * 0.5;
    imagefilledrectangle($img, (int) ($cx - $baseW / 2), (int) $baseTop, (int) ($cx + $baseW / 2), (int) ($baseTop + $baseH), $white);

    $domeR = $size * $scale * 0.26;
    $domeCy = $baseTop;
    imagefilledellipse($img, (int) $cx, (int) $domeCy, (int) ($domeR * 2), (int) ($domeR * 2), $white);

    $doorW = $baseW * 0.28;
    $doorH = $baseH * 0.65;
    imagefilledellipse($img, (int) $cx, (int) ($baseTop + $baseH - $doorH / 2), (int) $doorW, (int) $doorH, $bgColor);
    imagefilledrectangle($img, (int) ($cx - $doorW / 2), (int) ($baseTop + $baseH - $doorH), (int) ($cx + $doorW / 2), (int) ($baseTop + $baseH), $bgColor);

    $crossW = $size * scale_cross($scale);
    $crossThick = max(2, (int) ($size * 0.018));
    $crossTop = $domeCy - $domeR - $size * $scale * 0.22;
    imagefilledrectangle($img, (int) ($cx - $crossThick / 2), (int) $crossTop, (int) ($cx + $crossThick / 2), (int) ($domeCy - $domeR + 2), $white);
    imagefilledrectangle($img, (int) ($cx - $crossW / 2), (int) ($crossTop + $size * $scale * 0.05), (int) ($cx + $crossW / 2), (int) ($crossTop + $size * $scale * 0.05 + $crossThick), $white);

    return $img;
}

function scale_cross(float $scale): float
{
    return $scale * 0.16;
}

$outDir = __DIR__.'/../public/icons';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$primary = '#1976d2';

foreach ([192, 512] as $size) {
    $img = drawChurchIcon($size, $primary, false);
    imagepng($img, "$outDir/icon-$size.png");
    imagedestroy($img);

    $maskable = drawChurchIcon($size, $primary, true);
    imagepng($maskable, "$outDir/icon-$size-maskable.png");
    imagedestroy($maskable);
}

// Apple touch icon: no transparency, solid background, 180x180.
$apple = drawChurchIcon(180, $primary, false);
imagepng($apple, "$outDir/apple-touch-icon.png");
imagedestroy($apple);

echo "Generated icons in $outDir\n";
