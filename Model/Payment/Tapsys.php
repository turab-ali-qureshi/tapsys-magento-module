<?php

namespace Tapsys\Checkout\Model\Payment;

/**
 * Class Tapsys
 *
 * @package Tapsys\Checkout\Model\Payment
 */
class Tapsys extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CODE = "tapsys";

    protected $_code = self::PAYMENT_METHOD_CODE;
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = true;
    protected $_canUseForMultishipping  = false;
    protected $_infoBlockType = \Tapsys\Checkout\Block\Info::class;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}
