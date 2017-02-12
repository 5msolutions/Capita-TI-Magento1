<?php

class Capita_TI_Block_Adminhtml_Request_New_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $form->setUseContainer(true);
        $datetimeFormat = 'y-MM-d HH:mm:ss';
        $nextWeek = new Zend_Date();
        $nextWeek->addWeek(1);

        $general = $form->addFieldset('general', array(
            'legend' => $this->__('General')
        ));
        $general->addField('instructions', 'textarea', array(
            'name' => 'instructions',
            'label' => $this->__('Instructions')
        ));
        $general->addField('languages', 'multiselect', array(
            'name' => 'languages',
            'label' => $this->__('Languages'),
            'required' => true,
            'values' => Mage::helper('capita_ti')->getStoreLocalesOptions()
        ));
        $general->addField('delivery_date', 'date', array(
            'name' => 'delivery_date',
            'label' => $this->__('Expected Delivery Date'),
            'required' => true,
            'time' => true,
            'format' => $datetimeFormat,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'value' => $nextWeek->toString($datetimeFormat)
        ));

        $products = $form->addFieldset('products', array(
            'legend' => $this->__('Products')
        ));
        $products->addField('product_attributes', 'multiselect', array(
            'name' => 'product_attributes',
            'label' => $this->__('Product Attributes'),
            'required' => true,
            'values' => Mage::helper('capita_ti')->getProductAttributesOptions()
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

}
