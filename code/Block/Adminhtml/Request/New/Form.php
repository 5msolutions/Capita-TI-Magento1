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

    protected function getCategoryIds()
    {
        return (array) $this->getSession()->getCapitaCategoryIds();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));
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

        if ($this->getParentBlock()->getEnableProducts()) {
            $this->_addProductsFieldset($form);
        }

        if ($this->getParentBlock()->getEnableCategories()) {
            $this->_addCategoriesFieldset($form);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _addProductsFieldset(Varien_Data_Form $form)
    {
        $products = $form->addFieldset('products', array(
            'legend' => $this->__('Products')
        ));
        $products->addField('product_ids', 'hidden', array(
            'name' => 'product_ids',
            'value' => implode(',', $this->getProductIds())
        ));
        $products->addField('product_count', 'label', array(
            'label' => $this->__('Number of products selected'),
            'value' => count($this->getProductIds())
        ));
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
    }

    protected function _addCategoriesFieldset(Varien_Data_Form $form)
    {
        $categories = $form->addFieldset('categories', array(
            'legend' => $this->__('Categories')
        ));
        $categories->addType('categories', Mage::getConfig()->getBlockClassName('capita_ti/adminhtml_categories'));
        $categories->addField('category_ids', 'categories', array(
            'name' => 'category_ids',
            'label' => $this->__('Categories'),
            'value' => implode(',', $this->getCategoryIds())
        ));
        $categories->addField('category_attributes', 'multiselect', array(
            'name' => 'category_attributes',
            'label' => $this->__('Category Attributes'),
            'note' => $this->__(
                'The default selection can be changed in <a href="%s">Configuration</a>.',
                $this->getUrl('*/system_config/edit', array('section' => 'capita_ti'))),
            'required' => true,
            'values' => Mage::getSingleton('capita_ti/source_category_attributes')->toOptionArray(),
            'value' => Mage::getStoreConfig('capita_ti/categories/attributes')
        ));
    }
}
