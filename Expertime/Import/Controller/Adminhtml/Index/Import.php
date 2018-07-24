<?php


namespace Expertime\Import\Controller\Adminhtml\Index;

class Import extends \Magento\Backend\App\Action
{
    protected $_webapi;

    protected $_import;

    /**
     * Import constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Expertime\Import\Model\Webapi $webapi
     * @param \Expertime\Import\Model\Import $import
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Expertime\Import\Model\Webapi $webapi,
        \Expertime\Import\Model\Import $import
    ) {
        $this->_webapi = $webapi;
        $this->_import = $import;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $importType = $this->getRequest()->getParam('import_type');
            try {
                if ($importType == '1') {
                    $customerId = $this->getRequest()->getParam('customer_id');
                    if (empty($customerId)) {
                        $this->messageManager->addErrorMessage('Please fill the Customer Id value');
                        $resultRedirect->setPath('*/*/');
                    } else {
                        $customerData = $this->_webapi->getCustomerById($customerId);
                        $this->_import->createCustomer($customerData);
                        $this->messageManager->addSuccessMessage(__('Customer imported successfully'));
                    }
                } else {
                    $customersData = $this->_webapi->getCustomers();
                    $this->_import->createCustomers($customersData);
                    $this->messageManager->addSuccessMessage(__('Customers imported successfully'));
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Import.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
