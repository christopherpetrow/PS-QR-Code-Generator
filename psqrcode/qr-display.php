<?php
require_once dirname(__DIR__, 2) . '/config/config.inc.php';

$token = Tools::getValue('token');
if (!$token) {
    die('Token required');
}

$token = pSQL($token);

$row = Db::getInstance()->getRow('SELECT id_order, delivery_note FROM ' . _DB_PREFIX_ . 'qr_messages WHERE token = "' . $token . '"');

if (!$row) {
    die('Invalid token');
}

//$message = Db::getInstance()->getValue('SELECT delivery_note FROM ' . _DB_PREFIX_ . 'qr_messages WHERE id_order=' . (int) $row['id_order'] . ' ORDER BY created_at DESC');
$deliveryNote = $row['delivery_note'];

if (!$deliveryNote) {
    die('No message found');
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Message</title>
</head>
<body>
<?php if (!empty($deliveryNote)) { ?>
<p><?php echo htmlspecialchars($deliveryNote, ENT_QUOTES, 'UTF-8'); ?></p>
<?php } ?>
</body>
</html>
