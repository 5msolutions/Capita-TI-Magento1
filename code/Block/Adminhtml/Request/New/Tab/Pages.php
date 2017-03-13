<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Pages
extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function getTabLabel()
    {
        return $this->__('CMS Pages');
    }

    public function getTabTitle()
    {
        return $this->__('CMS Pages');
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
        /* @var $collection Mage_Cms_Model_Resource_Page_Collection */
        $collection = Mage::helper('capita_ti')->getCmsPagesByLanguage(Mage::getStoreConfig('general/locale/code'));

        $options = array();
        foreach ($collection as $page) {
            $options[] = array(
                'value' => $page->getId(),
                'label' => $page->getTitle()
            );
        }
        
        $fieldset = $form->addFieldset('pages', array(
            'legend' => $this->__('CMS Pages')
        ));
        $fieldset->addField('page_ids', 'multiselect', array(
            'name' => 'page_ids',
            'label' => $this->__('CMS Pages'),
            'values' => $options,
            'value' => $this->getPageIds()
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
