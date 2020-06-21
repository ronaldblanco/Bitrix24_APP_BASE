<?php

$id = isset($_POST['app_id']) ? trim($_POST["app_id"]) : '';
$secret = isset($_POST['app_secret']) ? trim($_POST["app_secret"]) : '';
$url = isset($_POST['app_redirect_url']) ? trim($_POST["app_redirect_url"]) : '';
$domain = isset($_POST['bitrix_domain']) ? trim($_POST["bitrix_domain"]) : '';

$tofile = json_encode(['app_id' => $id, 'app_secret' => $secret, 'app_redirect_url' => $url, 'bitrix_domain' => $domain], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
file_put_contents(__DIR__ . '/config.json', $tofile);

Header("Location: ".substr($_SERVER['HTTPS'], 0, -2) . 'index.php');

?>