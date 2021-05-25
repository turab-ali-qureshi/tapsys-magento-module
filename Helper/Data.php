<?php

namespace Tapsys\Checkout\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Tapsys\Checkout\Model\EnvVars;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class Data extends AbstractHelper
{
    const STORE_SCOPE = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
    const SANDBOX = EnvVars::SANDBOX;
    const PRODUCTION = EnvVars::PRODUCTION;

    protected $_orderManagement;
    protected $_orderFactory;
    protected $_orderRepository;
    protected $_invoiceService;
    protected $_invoiceSender;
    protected $_coreSession;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlInterface
     * @param CoreSession $coreSession
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        OrderManagementInterface $orderManagement,
        OrderFactory $orderFactory,
        UrlInterface $urlInterface,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        CoreSession $coreSession
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_orderManagement = $orderManagement;
        $this->_orderFactory = $orderFactory;
        $this->_urlInterface = $urlInterface;
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_invoiceSender = $invoiceSender;
        $this->_coreSession = $coreSession;
        parent::__construct($context, $objectManager, $storeManager);
    }

    public function getEnvironment()
    {
        return $this->getStoreConfigValue('sandbox') ? self::SANDBOX : self::PRODUCTION;
    }

    public function getStoreConfigValue($fieldId)
    {
        return $this->_scopeConfig->getValue(
                    "payment/tapsys/".$fieldId,
                    self::STORE_SCOPE
                );
    }

    /**
     * int $orderId
     * Order cancel by order id $orderId
     */
    public function cancelOrder($orderId) {
        $this->_orderManagement->cancel($orderId);
    }

    public function getUrl($urlKey = null, $paramArray = [])
    {
        if (!empty($urlKey)) {
            if (empty($paramArray)) {
                return $this->_urlInterface->getUrl($urlKey);
            } else {
                return $this->_urlInterface->getUrl($urlKey, $paramArray);
            }
        } else {
            return $this->_urlInterface->getBaseUrl();
        }
    }

    public function constructUrl($order)
    {
        $this->_coreSession->start();
        $my_variable = $this->_coreSession->getMyVariable();
        $my_variable = explode("-_-", $my_variable);
        $tracker = $my_variable[0];
        $baseURL = $this->getStoreConfigValue('sandbox') ? EnvVars::SANDBOX_CHECKOUT_URL : EnvVars::PRODUCTION_CHECKOUT_URL;
        $order_id = $order->getId();
        $params = array(
            "env"            => $this->getStoreConfigValue('sandbox') ? self::SANDBOX : self::PRODUCTION,
            "beacon"         => $tracker,
            "source"         => 'magento2',
            "order_id"       => $order_id,
            "nonce"          => 'magento_order_id'. $order_id,
            "redirect_url"   => $this->getUrl('tapsys/payment/response'),
            "cancel_url"     => $this->getUrl('tapsys/payment/cancel')
        );

        $baseURL = $baseURL.'/?'.http_build_query($params);

        $baseURL = urldecode($baseURL);

        return $baseURL;
    }

    public function getSharedSecret()
    {
        $key = $this->getStoreConfigValue('sandbox') ? $this->getStoreConfigValue('sandbox_webhook_secret') : $this->getStoreConfigValue('production_webhook_secret');
        return $key;
    }

    public function getOrderById($orderId)
    {
        $order = $this->_orderFactory->create()->load($orderId);
        return $order;
    }

    public function validateSignature($tracker, $signature)
    {
        $secret = $this->getSharedSecret();
        $signature_2 = hash_hmac('sha256', $tracker, $secret);

        if ($signature_2 === $signature) {
            return true;
        }

        return false;
    }
    public function createInvoice($orderId)
    {
        $order = $this->_orderRepository->get($orderId);
        if($order->canInvoice()) {
            $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);
            $invoice->save();
            $transactionSave = $this->_transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            $this->_invoiceSender->send($invoice);
            //send notification code
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
            ->setIsCustomerNotified(true)
            ->save();
        }
    }
}
