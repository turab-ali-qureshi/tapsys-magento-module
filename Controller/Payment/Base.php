<?php
namespace Tapsys\Checkout\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Tapsys\Checkout\Helper\Data as TapsysHelper;
use Tapsys\Checkout\Model\EnvVars;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class Base extends \Magento\Framework\App\Action\Action
{
    protected $_logger;
    protected $_checkoutSession;
    protected $_storeManager;
    protected $_resultFactory;
    protected $_scopeConfig;
    protected $_jsonHelper;
    protected $_tapsysHelper;
    protected $_messageManager;
    protected $_entryModel;
    protected $_formKeyValidator;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        ResultFactory $resultFactory,
        ScopeConfigInterface $scopeConfig,
        JsonHelper $jsonHelper,
        TapsysHelper $tapsysHelper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ){
        parent::__construct($context);
        $this->_logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->_resultFactory = $resultFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_jsonHelper = $jsonHelper;
        $this->_tapsysHelper = $tapsysHelper;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_messageManager = $context->getMessageManager();
    }
}
