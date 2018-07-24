<?php

namespace Expertime\Import\Model;

class Webapi extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    // @todo more api credentials to the system configuration
    private $_apiEndpoint = 'https://reqres.in/api/users/';

    protected $_logger;

    /**
     * Webapi constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_curl = $curl;
        $this->_logger = $logger;
        parent::__construct($context, $registry);
    }


    /**
     * Get customers
     *
     * @todo Implement pagination functionality
     * @todo add cache for this values instead of send request again for te same customers perPage
     *
     * @param int $perPage
     * @return array
     */
    public function getCustomers($perPage = 50)
    {
        try {
            $this->_curl->get($this->_apiEndpoint . '?per_page=' . $perPage);
            $response = json_decode($this->_curl->getBody(), true);
            if(isset($response['data'])) {
                return $response['data'];
            } else {
                return [];
            }
        } catch (\Exception $e) {
            $this->_logger->critical('Expertime Import Curl error', ['exception' => $e]);
        }
    }

    /**
     * Return customer data by customer id
     *
     * @param $customerId
     * @return array
     */
    public function getCustomerById($customerId)
    {
        $customerId = (int) $customerId;
        try {
            $this->_curl->get($this->_apiEndpoint . $customerId);
            $response = json_decode($this->_curl->getBody(), true);
            if(isset($response['data'])) {
                return $response['data'];
            } else {
                return [];
            }
        } catch (\Exception $e) {
            $this->_logger->critical('Expertime Import Curl error', ['exception' => $e]);
        }
    }

}
