<?php

class Capita_TI_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * Returns module string as specified in config.xml
     * 
     * @return string
     */
    public function getModuleVersion()
    {
        return (string) Mage::getConfig()->getNode('modules/Capita_TI/version');
    }

    public function convertHashToOptions($hash)
    {
        $options = array();
        foreach ($hash as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }

    /**
     * Get used locales in options/values format
     * 
     * @return array
     */
    public function getStoreLocalesOptions()
    {
        $languages = Mage::getSingleton('capita_ti/api_languages')->getLanguagesInUse();
        return $this->convertHashToOptions($languages);
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
