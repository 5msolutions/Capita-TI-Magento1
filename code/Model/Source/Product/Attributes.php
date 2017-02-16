<?php

class Capita_TI_Model_Source_Product_Attributes
{

    protected function getBestAttributes()
    {
        /* @var $attributes Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('main_table.frontend_input', array('in' => array('text', 'textarea')))
            ->addFieldToFilter('additional_table.is_global', 0);
        return $attributes;
    }

    public function getOptions()
    {
        return $this->getBestAttributes()->getColumnValues('frontend_label');
    }

    public function toOptionArray()
    {
        $result = array();
        foreach ($this->getBestAttributes() as $attr) {
            $result[] = array(
                'value' => $attr->getAttributeCode(),
                'label' => $attr->getFrontendLabel()
            );
        }
        return $result;
    }
}
