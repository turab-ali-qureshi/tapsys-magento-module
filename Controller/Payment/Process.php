<?php
namespace Tapsys\Checkout\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;
use Tapsys\Checkout\Model\EnvVars;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Process extends Base
{
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $env = $this->_tapsysHelper->getEnvironment();
            $order = $this->_checkoutSession->getLastRealOrder();

            if (empty($order->getId())) {
                throw new NoSuchEntityException(__('No Order Found.'));
            }
            $hostedUrl = $this->_tapsysHelper->constructUrl($order);
            /* Redirect to gateway url for payment processing */
            $resultRedirect->setUrl($hostedUrl);
            return $resultRedirect;

        } catch (NoSuchEntityException $e) {
            $this->_logger->info($e->getMessage());
            $this->_messageManager->addErrorMessage(__('Something went wrong, please try again.'));
            $this->_checkoutSession->clearQuote()->clearStorage();
            $resultRedirect->setUrl($this->_tapsysHelper->getUrl('checkout/cart'));
            return $resultRedirect;
        } catch (\Exception $e) {
            $order = $this->_checkoutSession->getLastRealOrder();
            $order->setComment(__('Payment Failed.'))->addStatusHistoryComment(__('Payment Failed.'))->save();
            $this->_tapsysHelper->cancelOrder($order->getId());
            $this->_logger->info($e->getMessage());
            $this->_checkoutSession->clearQuote()->clearStorage();
            $resultRedirect->setUrl($this->_tapsysHelper->getUrl('tapsys/order/reorder', ['order_id' => $order->getId()]));
            return $resultRedirect;
        }
    }
}
