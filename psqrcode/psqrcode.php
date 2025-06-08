<?php

require_once __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Psqrcode extends Module
{
    /**
     * Name of the table used to store tokens
     *
     * @var string
     */
    protected $table_qr_messages;

    public function __construct()
    {
        $this->name = 'psqrcode';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'QR Module';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '8.0.0', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('QR Code Message');
        $this->description = $this->l('Generates a QR code from the customer message on order confirmation.');

        $this->table_qr_messages = _DB_PREFIX_ . 'qr_messages';
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionCarrierProcess')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('displayAdminOrderMain')
            && $this->createQrTable();
    }

    public function uninstall()
    {
        //$this->dropQrTable();

        return parent::uninstall();
    }

    protected function createQrTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . $this->table_qr_messages . '` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_order` INT UNSIGNED NOT NULL,
            `token` VARCHAR(64) NOT NULL UNIQUE,
            `created_at` DATETIME NOT NULL,
            `expires_at` DATETIME DEFAULT NULL,
            `delivery_note` TEXT DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4';

        return Db::getInstance()->execute($sql);
    }

    protected function dropQrTable()
    {
        $sql = 'DROP TABLE IF EXISTS `' . $this->table_qr_messages . '`';
        return Db::getInstance()->execute($sql);
    }

    protected function generateToken()
    {
        return bin2hex(random_bytes(16));
    }

    public function hookActionCarrierProcess(array $params)
    {
        $note = Tools::getValue('delivery_note');
        
        PrestaShopLogger::addLog(
            sprintf('Customer note is: %s', $note),
            1,
            null,
            'Cart',
            (int)$params['cart']->id
        );
        
        if ($note) {
            // Log at cart level
            PrestaShopLogger::addLog(
                sprintf('Note is true, Customer note is: %s', $note),
                1,
                null,
                'Cart',
                (int)$params['cart']->id
            );
            // Store in cookie for later use
            $this->context->cookie->delivery_note = $note;
            $this->context->cookie->write();
        }
    }

    public function hookActionValidateOrder($params)
    {
        if (empty($params['order'])) {
            return;
        }

        $order = $params['order'];

        $token = $this->generateToken();

        $baseUrl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__;
        $displayUrl = $baseUrl . 'modules/' . $this->name . '/qr-display.php?token=' . urlencode($token);

        $qrDir = _PS_MODULE_DIR_ . $this->name . '/qrcodes/';
        if (!is_dir($qrDir)) {
            @mkdir($qrDir, 0755, true);
        }

        $qrPath = $qrDir . $token . '.png';

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($displayUrl)
            ->size(200)
            ->margin(0)
            ->build();

        $result->saveToFile($qrPath);

        $note = isset($this->context->cookie->delivery_note)
            ? pSQL($this->context->cookie->delivery_note)
            : '';

        PrestaShopLogger::addLog(
                sprintf('Date check QR is: %d', $note),
                1,
                null,
                'Order',
                (int)$order->id
            );

        $data = [
            'id_order'   => (int) $order->id,
            'token'      => pSQL($token),
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'delivery_note' => pSQL($note, true),
        ];

        Db::getInstance()->insert('qr_messages', $data);
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if (empty($params['order'])) {
            return '';
        }

        $order = $params['order'];

        // Log the hook call with a valid object type for PrestaShopLogger.
        if (class_exists('PrestaShopLogger')) {
            PrestaShopLogger::addLog(
                'hook Triggered',
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE,
                null,
                'Psqrcode',
                (int) $order->id
            );
        }

        $tokenRow = Db::getInstance()->getRow('SELECT token, delivery_note FROM ' . $this->table_qr_messages . ' WHERE id_order=' . (int) $order->id);

        if (!$tokenRow) {
            return '';
        }

        $token = $tokenRow['token'];
        $deliveryNote = $tokenRow['delivery_note'];

        $baseUrl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__;
        $displayUrl = $baseUrl . 'modules/' . $this->name . '/qr-display.php?token=' . urlencode($token);

        $qrUrl = $baseUrl . 'modules/' . $this->name . '/qrcodes/' . $token . '.png';

        $this->context->smarty->assign([
            'qr_url' => $qrUrl,
            'display_url' => $displayUrl,
            'delivery_note' => $deliveryNote,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
    }

    public function hookDisplayAdminOrderMain($params)
    {
        $idOrder = 0;

        if (!empty($params['id_order'])) {
            $idOrder = (int) $params['id_order'];
        } elseif (!empty($params['order']) && $params['order'] instanceof Order) {
            $idOrder = (int) $params['order']->id;
        }

        if (!$idOrder) {
            return '';
        }

        if (class_exists('PrestaShopLogger')) {
            PrestaShopLogger::addLog(
                'hook Triggered admin',
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE,
                null,
                'Psqrcode',
                $idOrder
            );
        }

        $tokenRow = Db::getInstance()->getRow('SELECT token, delivery_note FROM ' . $this->table_qr_messages . ' WHERE id_order=' . $idOrder);

        if (!$tokenRow) {
            return '';
        }

        $token = $tokenRow['token'];
        $deliveryNote = $tokenRow['delivery_note'];

        $baseUrl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__;
        $displayUrl = $baseUrl . 'modules/' . $this->name . '/qr-display.php?token=' . urlencode($token);

        $qrUrl = $baseUrl . 'modules/' . $this->name . '/qrcodes/' . $token . '.png';

        $this->context->smarty->assign([
            'qr_url' => $qrUrl,
            'display_url' => $displayUrl,
            'customer_message_label' => $this->l('Customer message'),
            'delivery_note' => $deliveryNote,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/adminordermain.tpl');
    }
}
