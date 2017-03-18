<?php

class Capita_TI_Block_Adminhtml_Grid_Column_Renderer_Languages
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    const IMG_YES = 'images/fam_bullet_success.gif';
    const IMG_NO = 'images/error_msg_icon.gif';

    public function _getValue(Varien_Object $row)
    {
        $languages = Mage::getSingleton('capita_ti/api_languages')->getLanguagesInUse();
        unset($languages[Mage::getStoreConfig('general/locale/code')]);

        $html = '';
        $value = parent::_getValue($row);
        foreach ($languages as $locale => $name) {
            $html .= '<img src="'.
                $this->getSkinUrl(strpos($value, $locale) !== false ? self::IMG_YES : self::IMG_NO).
                '" title="'.$this->escapeHtml($name).'">&nbsp;';
        }
        return $html;
    }
}
