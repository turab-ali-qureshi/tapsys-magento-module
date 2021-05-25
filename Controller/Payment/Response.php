<?php

namespace Tapsys\Checkout\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Store\Model\StoreManagerInterface;
use Tapsys\Checkout\Helper\Data as TapsysHelper;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Tapsys\Checkout\Model\EnvVars;

class Response extends Base
{

    /**
     * @var OrderSender
     */
    private $orderSender;
    protected $_coreSession;

    /**
     * Response constructor.
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param ResultFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param JsonHelper $jsonHelper
     * @param TapsysHelper $tapsysHelper
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param OrderSender $orderSender
     * @param CoreSession $coreSession
     */
    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        ResultFactory $resultFactory,
        ScopeConfigInterface $scopeConfig,
        JsonHelper $jsonHelper,
        TapsysHelper $tapsysHelper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        OrderSender $orderSender,
        CoreSession $coreSession
    ) {
        parent::__construct(
            $context,
            $logger,
            $checkoutSession,
            $storeManager,
            $resultFactory,
            $scopeConfig,
            $jsonHelper,
            $tapsysHelper,
            $formKeyValidator
        );
        $this->orderSender = $orderSender;
        $this->_coreSession = $coreSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!empty($this->getRequest()->getParams())) {
            try {
                $postData = $this->getRequest()->getParams();
                $signature = ($postData["sig"]);
                $reference_code = ($postData["reference"]);
                $this->_coreSession->start();
                $my_variable = $this->_coreSession->getMyVariable();
                $my_variable = explode("-_-", $my_variable);
                $tracker = $my_variable[0];
                $my_amount = $my_variable[1];
                $this->_coreSession->unsMyVariable();
                
                $env = (bool)$this->_tapsysHelper->getStoreConfigValue('sandbox');
                $tokenApiBaseUrl = $env ? EnvVars::SANDBOX_API_URL : EnvVars::PRODUCTION_API_URL;
                $url = $tokenApiBaseUrl . "wordpress/validateSignature";
                $fields = array(
                    'beacon' => $tracker
                );
                $fields = json_encode($fields);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                $result = json_decode($result);
                $order_id = $result->orderId;
                $res = $result->status;

                if (empty($order_id)) {
                    $resultRedirect->setUrl($this->_tapsysHelper->getUrl('checkout/onepage/failure', ['_secure' => true]));
                    return $resultRedirect;
                }
                $order = $this->_tapsysHelper->getOrderById($order_id);
                
                if ($my_amount != $order->getGrandTotal()) {
                    $resultRedirect->setUrl($this->_tapsysHelper->getUrl('checkout/onepage/failure', ['_secure' => true]));
                    return $resultRedirect;
                }

                $success = false;
                $error = null;

                if (empty($order_id) || empty($signature)) {
                    $error = __('Payment to Tapsys Failed. No data received');
                } elseif ($res != "Success") {
                    $error = __('Payment is invalid. Failed security check.');
                } else {
                    $success = true;
                }

                if ($success) {
                    // Payment was successful, so update the order's state, send order email and move to the success page
                    $order->setState(Order::STATE_PROCESSING, true, __('Gateway has authorized the payment.'));
                    $order->setStatus(Order::STATE_PROCESSING, true, __('Gateway has authorized the payment.'));

                    $this->orderSender->send($order);

                    $this->_tapsysHelper->createInvoice($order_id);
                    $order->addStatusHistoryComment(__('Payment Gateway Reference %s and tracker id %s', $reference_code, $tracker));

                    // $order->sendNewOrderEmail();
                    // $order->setEmailSent(true);

                    $order->save();

                    $payment = $order->getPayment();
                    $payment->setAdditionalInformation('tapsys_sig', $postData['sig']);
                    $payment->setAdditionalInformation('tapsys_reference', $postData['reference']);
                    $payment->setAdditionalInformation('tapsys_tracker', $postData['tracker']);
                    if(isset($postData['token'])){
                        $payment->setAdditionalInformation('tapsys_token_data', $postData['token']);
                    }
                    $payment->save();

                    $resultRedirect->setUrl($this->_tapsysHelper->getUrl('checkout/onepage/success', ['_secure'=>true]));
                    return $resultRedirect;
                } else {
                    // There is a problem in the response we got
                    $this->_tapsysHelper->cancelOrder($order->getId());
                    $resultRedirect->setUrl($this->_tapsysHelper->getUrl('checkout/onepage/failure', ['_secure' => true]));
                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->_messageManager->addErrorMessage(__('Error occured while processing the payment: %1', $e->getMessage()));
                $resultRedirect->setUrl($this->_tapsysHelper->getUrl('checkout/onepage/failure', ['_secure' => true]));
                return $resultRedirect;
            }
        } else {
            $resultRedirect->setUrl($this->_tapsysHelper->getUrl('checkout/onepage/failure', ['_secure' => true]));
            return $resultRedirect;
        }
    }
}
