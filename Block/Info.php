<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tapsys\Checkout\Block;

/**
 * Base payment information block
 *
 * @api
 * @since 100.0.2
 */
class Info extends \Magento\Payment\Block\Info
{
    const ADMIN_AREA_CODE = \Magento\Framework\App\Area::AREA_ADMINHTML;
    protected $_state;
    protected $_request;
    
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        $this->_state = $state;
        $this->_request = $request;
        parent::__construct($context, $data);
    }

    /**
     * Prepare information specific to current payment method
     *
     * @param null|\Magento\Framework\DataObject|array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        
        if (null === $this->_paymentSpecificInformation) {
            if (null === $transport) {
                $transport = new \Magento\Framework\DataObject();
            } elseif (is_array($transport)) {

                $transport = new \Magento\Framework\DataObject($transport);
            }
            $data = array();
            if (
                $this->getInfo()->getAdditionalInformation('tapsys_token_data') 
                && $this->_state->getAreaCode() == self::ADMIN_AREA_CODE
                && !(strpos($this->_request->getActionName(), 'print') !== false)
            )
            {
                $data['SIG'] = $this->getInfo()->getAdditionalInformation('tapsys_sig') ? $this->getInfo()->getAdditionalInformation('tapsys_sig') : null;
                $data['Reference'] = $this->getInfo()->getAdditionalInformation('tapsys_reference') ? $this->getInfo()->getAdditionalInformation('tapsys_reference') : null;
                $data['Tracker'] = $this->getInfo()->getAdditionalInformation('tapsys_tracker') ? $this->getInfo()->getAdditionalInformation('tapsys_tracker') : null;
                $data['Token'] = $this->getInfo()->getAdditionalInformation('tapsys_token_data') ? $this->getInfo()->getAdditionalInformation('tapsys_token_data') : null;
            }
            $transport->setData(array_merge($data, $transport->getData()));
            
            $this->_paymentSpecificInformation = $transport;
        }
        return $this->_paymentSpecificInformation;
    }
}
