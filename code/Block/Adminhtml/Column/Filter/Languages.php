<?php

class Capita_TI_Block_Adminhtml_Column_Filter_Languages extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{

    protected function _getLanguages()
    {
        $languages = Mage::getSingleton('capita_ti/api_languages')->getLanguagesInUse();
        unset($languages[Mage::getStoreConfig('general/locale/code')]);
        return $languages;
    }

    protected function _getOptions()
    {
        $languages = $this->_getLanguages();
        $options = array(
            array('value' => null, 'label' => ''),
            array('value' => 'all', 'label' => $this->__('All')),
            array('value' => 'nall', 'label' => $this->__('Not All')),
            array('value' => 'some', 'label' => $this->__('Some')),
            array('value' => 'none', 'label' => $this->__('None'))
        );
        foreach ($languages as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }

    public function getHtml()
    {
        $languages = $this->_getLanguages();

        $html = '<p>'.parent::getHtml().'</p><p>';
        foreach ($languages as $locale => $name) {
            if (preg_match('/^[a-z]+_([A-Z]+)$/', $locale, $m)) {
                $country = strtolower($m[1]);
                $html .= '<img src="'.
                    $this->getSkinUrl('images/capita/flags/'.$country.'.gif').
                    '" title="'.$this->escapeHtml($name).'">&nbsp;';
            }
        }
        $html .= '</p>';
        return $html;
    }

    public function getCondition()
    {
        switch ($this->getValue()) {
            case 'all':
                return array('eq' => implode(',', array_keys($this->_getLanguages())));
            case 'nall':
                return array(
                array('neq' => implode(',', array_keys($this->_getLanguages()))),
                array('null' => true));
            case 'some':
                return array('notnull' => true);
            case 'none':
                return array('null' => true);
            default:
                return parent::getCondition();
        }
    }
}
