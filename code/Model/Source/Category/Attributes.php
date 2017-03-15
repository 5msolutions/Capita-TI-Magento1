<?php

class Capita_TI_Model_Source_Category_Attributes
{

    public function getBestAttributes()
    {
        /* @var $attributes Mage_Catalog_Model_Resource_Category_Attribute_Collection */
        $attributes = Mage::getResourceModel('catalog/category_attribute_collection')
            ->addFieldToFilter('main_table.frontend_input', array('in' => array('text', 'textarea')))
            ->addFieldToFilter('main_table.backend_type', array('in' => array('text', 'varchar')))
            ->addFieldToFilter('main_table.backend_model', array(
                array('eq' => ''),
                array('null' => true)
            ))
            ->addFieldToFilter('additional_table.is_global', 0)
            ->addFieldToFilter('additional_table.is_visible', 1);
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
