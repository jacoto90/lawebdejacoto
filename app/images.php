<?php

declare(strict_types=1);

function app_image_path(string $path): string
{
    static $paths;
    $paths ??= require __DIR__ . '/../data/image-assets.php';
    return $paths[ltrim($path, '/')] ?? $path;
}
