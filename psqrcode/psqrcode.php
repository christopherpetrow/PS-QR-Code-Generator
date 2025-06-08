<?php

require_once __DIR__ . '/vendor/autoload.php';

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

    public function hookActionValidateOrder($params)
    {
        if (empty($params['order'])) {
            return;
        }

        $order = $params['order'];

        $token = $this->generateToken();

        $data = [
            'id_order'   => (int) $order->id,
            'token'      => pSQL($token),
            'created_at' => date('Y-m-d H:i:s'),
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

        $token = Db::getInstance()->getValue('SELECT token FROM ' . $this->table_qr_messages . ' WHERE id_order=' . (int) $order->id);

        if (!$token) {
            return '';
        }

        $baseUrl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__;
        $displayUrl = $baseUrl . 'modules/' . $this->name . '/qr-display.php?token=' . urlencode($token);

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($displayUrl);

        $this->context->smarty->assign([
            'qr_url' => $qrUrl,
            'display_url' => $displayUrl,
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

        $token = Db::getInstance()->getValue('SELECT token FROM ' . $this->table_qr_messages . ' WHERE id_order=' . $idOrder);

        if (!$token) {
            return '';
        }

        $baseUrl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__;
        $displayUrl = $baseUrl . 'modules/' . $this->name . '/qr-display.php?token=' . urlencode($token);

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($displayUrl);

        $this->context->smarty->assign([
            'qr_url' => $qrUrl,
            'display_url' => $displayUrl,
            'customer_message_label' => $this->l('Customer message'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/adminordermain.tpl');
    }
}
