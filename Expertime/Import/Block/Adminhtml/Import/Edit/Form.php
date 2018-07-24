<?php


namespace Expertime\Import\Block\Adminhtml\Import\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('expertime_import/*/import'),
                    'method' => 'post',
                ],
            ]
        );

        // base fieldset
        $fieldsets['base'] = $form->addFieldset('base_fieldset', ['legend' => __('Import Settings')]);
        $fieldsets['base']->addField(
            'import_type',
            'select',
            [
                'name' => 'import_type',
                'title' => __('Import type'),
                'label' => __('Import type'),
                'required' => true,
                'values' => [0 => 'Import All Customers', 1 => 'Import Single Customer'],
            ]
        );

        $fieldsets['base']->addField(
            'customer_id',
            'text',
            [
                'name' => 'customer_id',
                'title' => __('Customer Id'),
                'label' => __('Customer Id'),
                'required' => false,
                'display' => 'none'
            ],
            'import_type'
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "import_type",
                'import_type'
            )
                ->addFieldMap(
                    "customer_id",
                    'customer_id'
                )
                ->addFieldDependence(
                    'customer_id',
                    'import_type',
                    '1'
                )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}