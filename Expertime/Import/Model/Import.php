<?php

namespace Expertime\Import\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\File\UploaderFactory;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Framework\Filesystem\Io\File;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\Model\Context;

class Import extends \Magento\Framework\Model\AbstractModel
{
    protected $_fileSystem;

    protected $_file;

    protected $_uploaderFactory;

    protected $_logger;


    /**
     * Import constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param File $file
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        File $file,
        LoggerInterface $logger
    ) {
        $this->_fileSystem = $fileSystem;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_file = $file;
        $this->_logger = $logger;
        parent::__construct($context, $registry);
    }


    /**
     * Create customer
     *
     * @todo Add validation
     * @todo add functionality to send customer email about registration based on configurations.
     *
     * @param $customerData
     * @return bool
     */
    public function createCustomer($customerData)
    {
        $objectManager = ObjectManager::getInstance();

        $url = ObjectManager::getInstance();

        $storeManager = $url->get('\Magento\Store\Model\StoreManagerInterface');

        // Customer Factory to Create Customer

        $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory');

        $websiteId = $storeManager->getWebsite()->getWebsiteId();

        // Instantiate object (this is the most important part)

        $customer = $customerFactory->create();

        $customer->setWebsiteId($websiteId);

        $email = strtolower($customerData['first_name'] . '_' . $customerData['last_name']) . '@gmail.com';
        $customer->setEmail($email); // @todo save correct customer email

        $customer->setFirstname($customerData['first_name']);

        $customer->setLastname($customerData['last_name']);

        // skip password generation. If it's required - I can add password generation
        // or send the customer email his next steps to finish profile creating.

        $avatar = $this->_getCustomerAvatar($customerData['avatar']);
        $customer->setAvatar($avatar);

        try {
            $customer->save();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @todo replace it with a loop for large collections.
     *
     * @param $customersData
     */
    public function createCustomers($customersData)
    {
        foreach ($customersData as $customerData) {
            if(!$this->createCustomer($customerData)) {
                continue;
            }
        }
    }

    /**
     * Return unique image value to store in database
     *
     * @todo add validation for wrong imgUrl
     * @param $imgUrl
     * @return bool|string
     */
    private function _getCustomerAvatar($imgUrl)
    {
        $result = '';
        if (empty($imgUrl)) {
            return $result;
        }

        $pathInfo = pathinfo(parse_url($imgUrl, PHP_URL_PATH)); // file path from url

        try {
            $imgExtensions = ['jpg', 'jpeg', 'gif', 'png'];
            if (!isset($pathInfo['extension']) || !in_array(strtolower($pathInfo['extension']), $imgExtensions)) {
                throw new \Exception('Please correct the image file type.');
            }

            // get correct file name and generate dispersion path
            $fileName = Uploader::getCorrectFileName($pathInfo['basename']);
            $dispersionPath = Uploader::getDispersionPath($fileName);

            // prepare folder to store image
            $mediaDir = $this->_fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
            $customerDir = $mediaDir->getAbsolutePath(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
            $dirPath = $customerDir . $dispersionPath . DIRECTORY_SEPARATOR;
            $this->_file->checkAndCreateFolder($dirPath);

            // get unig file name to avoid overrides.
            $fileName = $dirPath . Uploader::getNewFileName($dirPath . $fileName);

            // generate filename to store as attribute for customer
            $customerFileName = $dispersionPath . DIRECTORY_SEPARATOR . basename($fileName);

            /** read file from URL and copy it to the new destination */
            $imageSave = $this->_file->read($imgUrl, $fileName);
            if ($imageSave) {
                $result = $customerFileName;
            }

        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return $result;
    }

}
