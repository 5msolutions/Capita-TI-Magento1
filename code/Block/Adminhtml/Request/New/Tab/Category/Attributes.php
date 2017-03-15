<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Category_Attributes
extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('categories', array(
            'legend' => $this->__('Details')
        ));
        $fieldset->addField('category_attributes', 'multiselect', array(
            'name' => 'category_attributes',
            'label' => $this->__('Category Attributes'),
            'note' => $this->__(
                'The default selection can be changed in <a href="%s">Configuration</a>.',
                $this->getUrl('*/system_config/edit', array('section' => 'capita_ti'))),
            'required' => true,
            'values' => Mage::getSingleton('capita_ti/source_category_attributes')->toOptionArray(),
            'value' => Mage::getStoreConfig('capita_ti/categories/attributes')
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
