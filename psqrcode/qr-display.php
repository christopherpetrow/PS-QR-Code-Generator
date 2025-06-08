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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Message</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 80px 20px 20px;
            box-sizing: border-box;
        }

        .page-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: rgba(255, 255, 255, 0.9);
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 0 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }

        .page-header .back-link {
            grid-column: 1;
            justify-self: start;
            color: #333;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .page-header .logo {
            grid-column: 2;
            justify-self: center;
            height: 40px;
        }

        @media (max-width: 480px) {
            .page-header .logo {
                height: 32px;
            }
            .page-header .back-link {
                font-size: 0.8rem;
            }
        }

        .message-container {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }

        .message-text {
            font-size: clamp(1rem, 2.5vw, 1.75rem);
            line-height: 1.5;
            color: #333;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
<header class="page-header">
    <a class="back-link" href="<?php echo __PS_BASE_URI__; ?>">&larr; Back to site</a>
    <img src="<?php echo __PS_BASE_URI__; ?>img/logo.png" alt="Logo" class="logo">
</header>

<main class="content">
    <div class="message-container">
    <?php if (!empty($deliveryNote)) { ?>
        <p class="message-text"><?php echo nl2br(htmlspecialchars($deliveryNote, ENT_QUOTES, 'UTF-8')); ?></p>
    <?php } ?>
    </div>
</main>
</body>
</html>
