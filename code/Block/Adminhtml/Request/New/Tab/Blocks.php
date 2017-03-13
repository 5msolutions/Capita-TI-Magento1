<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Blocks
extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function getTabLabel()
    {
        return $this->__('CMS Blocks');
    }

    public function getTabTitle()
    {
        return $this->__('CMS Blocks');
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
        /* @var $collection Mage_Cms_Model_Resource_Block_Collection */
        $collection = Mage::helper('capita_ti')->getCmsBlocksByLanguage(Mage::getStoreConfig('general/locale/code'));

        $fieldset = $form->addFieldset('blocks', array(
            'legend' => $this->__('CMS Blocks')
        ));
        $fieldset->addField('block_ids', 'multiselect', array(
            'name' => 'block_ids',
            'label' => $this->__('CMS Blocks'),
            'values' => $collection->toOptionArray(),
            'value' => $this->getBlockIds()
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
