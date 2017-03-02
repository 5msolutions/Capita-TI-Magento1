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
        $locales = Mage::helper('capita_ti')->getStoreLocalesOptions();
        $defaultLocale = Mage::app()->getDefaultStoreView()->getConfig('general/locale/code');

        $fieldset->addField('source_language', 'select', array(
            'name' => 'source_language',
            'label' => $this->__('Source Language'),
            'required' => true,
            'values' => $locales,
            'value' => $defaultLocale
        ));
        $fieldset->addField('dest_language', 'multiselect', array(
            'name' => 'dest_language',
            'label' => $this->__('Requested Languages'),
            'required' => true,
            'values' => $locales
        ))
        ->setAfterElementHtml('<script type="text/javascript">
            (function(){
            var autoable = function(event) {
                $$("#dest_language option").invoke("writeAttribute","disabled",null);
                $$("#dest_language option[value="+$F(this)+"]").invoke("writeAttribute","disabled","disabled");
            };
            Event.observe("source_language", "change", autoable);
            autoable.call("source_language");
            })();
            </script>');

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
