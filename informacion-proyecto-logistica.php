<?php
$lang = $_GET['lang'] ?? 'es';
$target = '/proyecto-logistica/?lang=' . rawurlencode($lang);
header('Location: ' . $target, true, 302);
exit;
