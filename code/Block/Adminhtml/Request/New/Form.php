<?php

class Capita_TI_Block_Adminhtml_Request_New_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Used for passing some temporary vars
     * 
     * @return Mage_Adminhtml_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    protected function getProductIds()
    {
        return (array) $this->getSession()->getCapitaProductIds();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $form->setUseContainer(true);
        $locales = Mage::helper('capita_ti')->getStoreLocalesOptions();
        $defaultLocale = Mage::app()->getDefaultStoreView()->getConfig('general/locale/code');

        $general = $form->addFieldset('general', array(
            'legend' => $this->__('General')
        ));
        $general->addField('source_language', 'select', array(
            'name' => 'source_language',
            'label' => $this->__('Source Language'),
            'required' => true,
            'values' => $locales,
            'value' => $defaultLocale
        ));
        $general->addField('dest_language', 'multiselect', array(
            'name' => 'dest_language',
            'label' => $this->__('Requested Languages'),
            'required' => true,
            'values' => $locales
        ))
        ->setAfterElementHtml('<script type="text/javascript">
            Event.observe("source_language", "change", function(event) {
                $$("#dest_language option").invoke("writeAttribute","disabled",null);
                $$("#dest_language option[value="+$F(this)+"]").invoke("writeAttribute","disabled","disabled");
            });
            $$("#dest_language option[value='.$defaultLocale.']").invoke("writeAttribute","disabled","disabled");
            </script>');

        $products = $form->addFieldset('products', array(
            'legend' => $this->__('Products')
        ));
        if ($this->getProductIds()) {
            $products->addField('product_ids', 'hidden', array(
                'name' => 'product_ids',
                'value' => implode(',', $this->getProductIds())
            ));
            $products->addField('product_count', 'label', array(
                'label' => $this->__('Number of products selected'),
                'value' => count($this->getProductIds())
            ));
        }
        $products->addField('product_attributes', 'multiselect', array(
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
