<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Product_Attributes
extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('products', array(
            'legend' => $this->__('Details')
        ));
        $fieldset->addField('product_attributes', 'multiselect', array(
            'name' => 'product_attributes',
            'label' => $this->__('Product Attributes'),
            'note' => $this->__(
                'The default selection can be changed in <a href="%s">Configuration</a>.',
                $this->getUrl('*/system_config/edit', array('section' => 'capita_ti'))),
            'required' => true,
            'values' => Mage::getSingleton('capita_ti/source_product_attributes')->toOptionArray(),
            'value' => Mage::getStoreConfig('capita_ti/products/attributes')
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
