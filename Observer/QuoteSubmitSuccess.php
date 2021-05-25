<?php

namespace Tapsys\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Tapsys\Checkout\Model\Payment\Tapsys;

/**
 * Class QuoteSubmitSuccess
 * @package Tapsys\Checkout\Observer
 */
class QuoteSubmitSuccess implements ObserverInterface
{

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();
        if ($order->getPayment()->getMethod() === Tapsys::PAYMENT_METHOD_CODE) {
            $order->setCanSendNewEmailFlag(false);
        }
    }
}
