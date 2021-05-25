<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tapsys\Checkout\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use Tapsys\Checkout\Helper\Data as TapsysHelper;
use Tapsys\Checkout\Model\EnvVars;

/**
 * Class CheckoutConfigProvider
 */
class CheckoutConfigProvider implements ConfigProviderInterface
{
    protected $_tapsysHelper;
    /**
     * @param TapsysHelper $tapsysHelper
     */
    public function __construct(
        TapsysHelper $tapsysHelper
    ) {
        $this->_tapsysHelper = $tapsysHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $tapsyscheckoutConfig = array();
        $tapsyscheckoutConfig['sandbox'] = (bool)$this->getStoreConfigValue("sandbox");
        $tapsyscheckoutConfig['merchant_id'] = $this->getStoreConfigValue("merchant_id");
        $tapsyscheckoutConfig['environment'] = (bool)$this->getStoreConfigValue("sandbox") ? 'sandbox' : 'production';
        $tokenApiBaseUrl = $tapsyscheckoutConfig['sandbox'] ? EnvVars::SANDBOX_API_URL : EnvVars::PRODUCTION_API_URL;
        // $tapsyscheckoutConfig['api_key'] = $tapsyscheckoutConfig['sandbox'] ? $this->getStoreConfigValue("sandbox_key") : $this->getStoreConfigValue("production_key");
        // $tapsyscheckoutConfig['webhook_secret'] = $tapsyscheckoutConfig['sandbox'] ? $this->getStoreConfigValue("sandbox_webhook_secret") : $this->getStoreConfigValue("production_webhook_secret");
        $tapsyscheckoutConfig['token_api_url'] = $tokenApiBaseUrl . EnvVars::INIT_TRANSACTION_ENDPOINT;
        $tapsyscheckoutConfig['order_success_message'] = $this->getStoreConfigValue("order_success_message");
        $config = [
            'payment' => [
                'tapsys' => $tapsyscheckoutConfig
            ]
        ];
        return $config;
    }

    public function getStoreConfigValue($fieldId)
    {
        return $this->_tapsysHelper->getStoreConfigValue($fieldId);
    }
}
