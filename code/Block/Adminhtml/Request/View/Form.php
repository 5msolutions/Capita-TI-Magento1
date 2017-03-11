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

        if ($request->getCategoryCount()) {
            $this->_addCategoryData($form, $request);
        }

        if ($request->getBlockCount()) {
            $this->_addBlockData($form, $request);
        }

        if ($request->getPageCount()) {
            $this->_addPageData($form, $request);
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
            'value' => Mage::app()->getLocale()->date($request->getCreatedAt(), Varien_Date::DATETIME_INTERNAL_FORMAT)
        ));
        $general->addField('updated_at', 'label', array(
            'label' => $this->__('Last Updated'),
            'value' => Mage::app()->getLocale()->date($request->getUpdatedAt(), Varien_Date::DATETIME_INTERNAL_FORMAT)
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

    protected function _addCategoryData(Varien_Data_Form $form, Capita_TI_Model_Request $request)
    {
        $categories = $form->addFieldset('categories', array(
            'legend' => $this->__('Categories')
        ));
        $categories->addField('category_count', 'label', array(
            'label' => $this->__('Number of categories selected'),
            'value' => $request->getCategoryCount()
        ));
        $categories->addField('category_attributes', 'label', array(
            'label' => $this->__('Category Attributes'),
            'value' => $request->getCategoryAttributeNames()
        ));

        return $categories;
    }

    protected function _addBlockData(Varien_Data_Form $form, Capita_TI_Model_Request $request)
    {
        $blocks = $form->addFieldset('blocks', array(
            'legend' => $this->__('Blocks')
        ));
        $blocks->addField('block_count', 'label', array(
            'label' => $this->__('Number of blocks selected'),
            'value' => $request->getBlockCount()
        ));

        return $categories;
    }

    protected function _addPageData(Varien_Data_Form $form, Capita_TI_Model_Request $request)
    {
        $pages = $form->addFieldset('pages', array(
            'legend' => $this->__('Pages')
        ));
        $pages->addField('page_count', 'label', array(
            'label' => $this->__('Number of pages selected'),
            'value' => $request->getPageCount()
        ));

        return $categories;
    }
}
