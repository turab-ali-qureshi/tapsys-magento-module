<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tapsys\Checkout\Model;

class EnvVars
{
    const SANDBOX = "sandbox";
    const PRODUCTION = "production";
    const PRODUCTION_CHECKOUT_URL = "https://gateway.tapsys.net/plugin/components";
    const SANDBOX_CHECKOUT_URL = "https://testgateway.tapsys.net/plugin/components";
    const SANDBOX_API_URL = 'https://testgateway.tapsys.net/plugin/';
    const PRODUCTION_API_URL = 'https://gateway.tapsys.net/plugin/';
    const INIT_TRANSACTION_ENDPOINT = "wordpress/order/v1/init";
}
