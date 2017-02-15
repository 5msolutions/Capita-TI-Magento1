<?php

/**
 * @method int getProductCount()
 * @method int[] getProductIds()
 * @method string getDestLanguage()
 * @method string getProductAttributes()
 * @method string getSourceLanguage()
 * @method Capita_TI_Model_Request setProductAttributes(string[])
 * @method Capita_TI_Model_Request setProductIds(int[])
 * @method Capita_TI_Model_Request setSourceLanguage(string)
 */
class Capita_TI_Model_Request extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/request');
    }

    public function getSourceLanguageName()
    {
        $languages = Mage::getSingleton('capita_ti/api_languages')->getLanguages();
        return @$languages[$this->getSourceLanguage()];
    }

    public function getDestLanguageName()
    {
        $languages = Mage::getSingleton('capita_ti/api_languages')->getLanguages();
        // $dests can be string or array of strings
        $dests = $this->getDestLanguage();
        $names = str_replace(
            array_keys($languages),
            array_values($languages),
            $dests);
        if (is_array($names)) {
            $names = implode(', ', $names);
        }
        else {
            $names = preg_replace('/,(?!=\w)/', ', ', $names);
        }
        return $names;
    }

    public function getProductAttributeNames()
    {
        $codes = $this->getProductAttributes();
        /* @var $attributes Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributes->addFieldToFilter('attribute_code', array('in' => explode(',', $codes)));
        return implode(', ', $attributes->getColumnValues('frontend_label'));
    }

    public function getStatusLabel()
    {
        return Mage::getSingleton('capita_ti/source_status')->getOptionLabel($this->getStatus());
    }
}
