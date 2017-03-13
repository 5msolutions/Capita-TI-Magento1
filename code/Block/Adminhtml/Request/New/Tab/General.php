<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_General
extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function getTabLabel()
    {
        return $this->__('General');
    }

    public function getTabTitle()
    {
        return $this->__('General');
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
        $fieldset = $form->addFieldset('general', array(
            'legend' => $this->__('General')
        ));
        $locales = Mage::getSingleton('capita_ti/api_languages')->getLanguagesInUse();
        $defaultLocale = Mage::getStoreConfig('general/locale/code');

        $fieldset->addField('source_language', 'label', array(
            'label' => $this->__('Source Language'),
            'value' => @$locales[$defaultLocale]
        ));

        unset($locales[$defaultLocale]);
        $fieldset->addField('dest_language', 'multiselect', array(
            'name' => 'dest_language',
            'label' => $this->__('Target Languages'),
            'required' => true,
            'values' => $this->helper('capita_ti')->convertHashToOptions($locales),
            'value' => array_keys($locales)
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
