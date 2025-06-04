<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Psqrcode extends Module
{
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
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayOrderConfirmation');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if (empty($params['order'])) {
            return '';
        }

        $order = $params['order'];

        // Log the hook call with a valid object type for PrestaShopLogger
        if (class_exists('PrestaShopLogger')) {
            PrestaShopLogger::addLog(
                'hook Triggered',
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE,
                null,
                'Psqrcode',
                (int) $order->id
            );
        }

        $sql = 'SELECT message FROM ' . _DB_PREFIX_ . 'message WHERE id_cart=' . (int) $order->id_cart . ' ORDER BY date_add DESC';
        $message = Db::getInstance()->getValue($sql);

        if (!$message) {
            return '';
        }

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($message);

        $this->context->smarty->assign([
            'qr_url' => $qrUrl,
            'customer_message' => $message,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
    }
}
