<?php

class Capita_TI_Model_Source_Product_Attributes
{

    public function getOptions()
    {
        /* @var $attributes Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributes->addFieldToFilter('main_table.frontend_input', array('in' => array('text', 'textarea')));
        $attributes->addFieldToFilter('main_table.backend_type', array('in' => array('text', 'varchar')));
        $attributes->addFieldToFilter('main_table.attribute_code', array('nin' => array('created_at', 'custom_layout_update', 'has_options', 'required_options')));
        return $attributes->getColumnValues('frontend_label');
    }

}
