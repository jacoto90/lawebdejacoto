<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../app/seo.php';

function check_seo(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$root = dirname(__DIR__);
$profile = require $root . '/data/profile.php';
$keys = ['home', 'about', 'experience', 'services', 'projects', 'hobby', 'contact', 'logistics', 'paso-seguro', 'cv'];
$titles = [];
foreach (['es', 'en'] as $lang) foreach ($keys as $key) {
    $seo = app_seo_page($key, $lang, in_array($key, ['logistics', 'paso-seguro'], true) ? [] : $profile);
    ob_start();
    require $root . '/components/seo-head.php';
    $head = ob_get_clean();
    $dom = new DOMDocument();
    @$dom->loadHTML('<!doctype html><html><head><meta charset="utf-8">' . $head . '</head><body></body></html>');
    $xpath = new DOMXPath($dom);
    check_seo($xpath->query('//title')->length === 1, 'Duplicate title');
    check_seo(!in_array($seo['title'], $titles, true), 'Metadata must describe each route/language');
    $titles[] = $seo['title'];
    foreach (['description', 'robots', 'twitter:card', 'twitter:image', 'twitter:image:alt'] as $name) {
        check_seo($xpath->query('//meta[@name="' . $name . '"]')->length === 1, 'Missing/duplicate ' . $name);
    }
    check_seo($xpath->query('//link[@rel="canonical"]')->length === 1, 'Missing canonical');
    check_seo($seo['canonical'] === $seo['alternates'][$lang], 'Self-reference must match language canonical');
    check_seo($xpath->query('//link[@hreflang="x-default"]')->length === 1, 'Missing default language');
    $json = json_decode($xpath->query('//script[@type="application/ld+json"]')->item(0)->textContent, true, 512, JSON_THROW_ON_ERROR);
    $ids = array_column($json['@graph'], '@id');
    check_seo(count($ids) === count(array_unique($ids)), 'Duplicate entity IDs');
    check_seo(!str_contains($head, 'name="keywords"'), 'Do not add ignored keyword metadata');
    $size = getimagesize($root . parse_url($seo['image'], PHP_URL_PATH));
    check_seo([$size[0], $size[1]] === $seo['dimensions'], 'Social image dimensions mismatch');
}

// Structured data must follow current Studio content and survive HTML-sensitive input.
$profile['name'] = 'Test </script><script>alert(1)</script> & "quoted"';
$profile['services'][0]['title']['es'] = 'Servicio actualizado';
$seo = app_seo_page('home', 'es', $profile);
ob_start();
require $root . '/components/seo-head.php';
$head = ob_get_clean();
check_seo(substr_count($head, '</script>') === 1, 'JSON-LD permits script breakout');
check_seo(str_contains($head, 'Servicio actualizado'), 'Service schema does not use current content');
check_seo(app_seo_page('services', 'es', $profile)['canonical'] === 'https://lawebdejacoto.com/', 'Single-page routes must consolidate');

$sitemap = simplexml_load_file($root . '/sitemap.xml');
check_seo($sitemap !== false && count($sitemap->url) === 8, 'Sitemap must list the eight distinct language documents');
foreach ($sitemap->url as $url) {
    $alternates = $url->children('http://www.w3.org/1999/xhtml')->link;
    check_seo(count($alternates) === 3, 'Sitemap lacks language alternates');
    foreach ($url->children('http://www.google.com/schemas/sitemap-image/1.1')->image as $image) {
        $location = (string) $image->children('http://www.google.com/schemas/sitemap-image/1.1')->loc;
        check_seo(is_file($root . parse_url($location, PHP_URL_PATH)), 'Sitemap points to absent image');
    }
}

// Validate every migration against the actual Apache redirect rules, including variants.
$rules = file($root . '/.htaccess', FILE_IGNORE_NEW_LINES);
foreach (require $root . '/data/image-assets.php' as $old => $new) {
    check_seo(is_file($root . '/' . $new), 'Missing migrated image: ' . $new);
    $found = false;
    foreach ($rules as $rule) {
        if (!preg_match('/^\s*RewriteRule\s+(\S+)\s+(\S+)\s+\[R=301,L\]$/', $rule, $parts)) continue;
        if (!preg_match('~' . $parts[1] . '~', $old)) continue;
        check_seo(preg_replace('~' . $parts[1] . '~', $parts[2], $old) === '/' . $new, 'Wrong image redirect');
        $found = true;
        break;
    }
    check_seo($found, 'No permanent redirect for ' . $old);
}
echo "PASS: 20 metadata variants, canonical/hreflang, JSON-LD escaping and live content, sitemap, social dimensions and 27 image redirects.\n";
