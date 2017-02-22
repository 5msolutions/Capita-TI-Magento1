<?php

class Capita_TI_Block_Adminhtml_Categories_Tree
extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
implements Varien_Data_Form_Element_Renderer_Interface
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('capita/ti/categories.phtml');
    }

    protected function getCategoryIds()
    {
        return explode(',', $this->getIdsString());
    }

    public function getIdsString()
    {
        return $this->getData('ids_string');
    }

    /**
     * Returns URL for loading tree
     *
     * @param null $expanded
     * @return string
     */
    public function getLoadTreeUrl($expanded = null)
    {
        return $this->getUrl('*/catalog_product/categoriesJson');
    }

    public function isReadonly()
    {
        return false;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setIdsString($element->getValue());
        return $this->toHtml();
    }
}
