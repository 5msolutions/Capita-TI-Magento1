<?php

class Capita_TI_Block_Adminhtml_Request_View_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        /* @var $request Capita_TI_Model_Request */
        $request = Mage::registry('capita_request');

        $this->_addGeneralData($form, $request);

        if ($request->getProductCount()) {
            $this->_addProductData($form, $request);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _addGeneralData(Varien_Data_Form $form, Capita_TI_Model_Request $request)
    {
        $general = $form->addFieldset('general', array(
            'legend' => $this->__('General')
        ));
        $general->addField('status', 'label', array(
            'label' => $this->__('Status'),
            'value' => $request->getStatusLabel()
        ));
        $general->addField('source_language', 'label', array(
            'label' => $this->__('Source Language'),
            'value' => $request->getSourceLanguageName()
        ));
        $general->addField('dest_language', 'label', array(
            'label' => $this->__('Requested Languages'),
            'value' => $request->getDestLanguageName()
        ));
        $general->addField('created_at', 'label', array(
            'label' => $this->__('Submission Date'),
            'value' => Mage::app()->getLocale()->date($request->getCreatedAt())
        ));
        return $general;
    }

    protected function _addProductData(Varien_Data_Form $form, Capita_TI_Model_Request $request)
    {
        $products = $form->addFieldset('products', array(
            'legend' => $this->__('Products')
        ));
        $products->addField('product_count', 'label', array(
            'label' => $this->__('Number of products selected'),
            'value' => $request->getProductCount()
        ));
        $products->addField('product_attributes', 'label', array(
            'label' => $this->__('Product Attributes'),
            'value' => $request->getProductAttributeNames()
        ));

        return $products;
    }
}
