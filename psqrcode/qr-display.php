<?php
require_once dirname(__DIR__, 2) . '/config/config.inc.php';

$token = Tools::getValue('token');
if (!$token) {
    die('Token required');
}

$token = pSQL($token);

$row = Db::getInstance()->getRow('SELECT id_order FROM ' . _DB_PREFIX_ . 'qr_messages WHERE token = "' . $token . '"');

if (!$row) {
    die('Invalid token');
}

$message = Db::getInstance()->getValue('SELECT message FROM ' . _DB_PREFIX_ . 'message WHERE id_order=' . (int) $row['id_order'] . ' ORDER BY date_add DESC');

if (!$message) {
    die('No message found');
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Message</title>
</head>
<body>
<p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
</body>
</html>
