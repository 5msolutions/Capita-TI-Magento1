<?php

class Capita_TI_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * Get unique ISO locale codes available on frontend
     * 
     * @return string[]
     */
    public function getStoreLocaleCodes()
    {
        $codes = array();
        /* @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $store) {
            $codes[] = (string) $store->getConfig('general/locale/code');
        }
        return array_unique($codes);
    }

    /**
     * Get used locales in options/values format
     * 
     * @return array
     */
    public function getStoreLocalesOptions()
    {
        $options = array();
        $codes = $this->getStoreLocaleCodes();
        foreach (Mage::app()->getLocale()->getOptionLocales() as $option) {
            if (is_array($option) && in_array(@$option['value'], $codes)) {
                $options[] = $option;
            }
        }
        return $options;
    }

    public function getStoreLocalesNames()
    {
        $names = array();
        foreach ($this->getStoreLocalesOptions() as $option) {
            $names[@$option['value']] = @$option['label'];
        }
        return $names;
    }

    /**
     * Get translateable attributes in options/values format
     * 
     * @return array
     */
    public function getProductAttributesOptions()
    {
        /* @var $attributes Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributes->addFieldToFilter('main_table.frontend_input', array('in' => array('text', 'textarea')));
        $attributes->addFieldToFilter('main_table.backend_type', array('in' => array('text', 'varchar')));
        $attributes->addFieldToFilter('main_table.attribute_code', array('nin' => array('created_at', 'custom_layout_update', 'has_options', 'required_options')));

        $options = array();
        /* @var $attribute Mage_Eav_Model_Entity_Attribute */
        foreach ($attributes as $attribute) {
            $options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontend()->getLabel()
            );
        }
        return $options;
    }

}
