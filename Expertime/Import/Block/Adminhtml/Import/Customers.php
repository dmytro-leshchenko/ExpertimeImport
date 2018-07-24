<?php


namespace Expertime\Import\Block\Adminhtml\Import;

class Customers extends \Magento\Framework\View\Element\Template
{
    /**
     * Import constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context,
     * @param \Expertime\Import\Model\Webapi $webapi
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Expertime\Import\Model\Webapi $webapi
    ) {
        $this->_webapi = $webapi;
        parent::__construct($context);
    }

    /**
     * Get all webapi customers
     */
    public function getCustomers()
    {
        return $this->_webapi->getCustomers();
    }
}
