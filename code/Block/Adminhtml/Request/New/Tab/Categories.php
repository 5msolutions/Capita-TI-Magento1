<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Categories
extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function getTabLabel()
    {
        return $this->__('Catalog Categories');
    }

    public function getTabTitle()
    {
        return $this->__('Catalog Categories');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('categories', array(
            'legend' => $this->__('Categories')
        ));
        $fieldset->addType('categories', Mage::getConfig()->getBlockClassName('capita_ti/adminhtml_categories'));
        $fieldset->addField('category_ids', 'categories', array(
            'name' => 'category_ids',
            'label' => $this->__('Categories'),
            'value' => implode(',', (array) $this->getCategoryIds())
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
