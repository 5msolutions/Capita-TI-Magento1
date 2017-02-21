<?php

class Capita_TI_Model_Api_Languages extends Capita_TI_Model_Api_Abstract
{

    protected $languages;

    public function __construct($config = null)
    {
        parent::__construct($this->getEndpoint('languages'), $config);
    }

    /**
     * Get available languages for this TI account
     * 
     * @return array
     */
    public function getLanguages()
    {
        if (isset($this->languages)) {
            return $this->languages;
        }

        // check recent cache
        $cacheId = 'capita_ti_languages_'.$this->getUsername();
        try {
            $data = Zend_Json::decode(Mage::app()->loadCache($cacheId));
        } catch (Zend_Json_Exception $e) {
            // cache was empty or corrupted
            $data = null;
        }

        // fallback to remote source
        if (!is_array($data)) {
            $response = $this->request();
            $data = $this->decode($response);
            // if exception throws here then cache is not written
            $cacheTags = array(
                // clear on "Flush Magento Cache"
                Mage_Core_Model_APP::CACHE_TAG,
                // clear with "Collection Data"
                Mage_Core_Model_Resource_Db_Collection_Abstract::CACHE_TAG
            );
            Mage::app()->saveCache($response->getBody(), $cacheId, $cacheTags, 3600);
        }

        // convert to Magento/Zend convention
        if (is_array($data)) {
            $this->languages = array();
            foreach ($data as $language) {
                $code = strtr(@$language['IsoCode'], '-', '_');
                $name = @$language['LanguageName'];
                $this->languages[$code] = $name;
            }
            return $this->languages;
        }

        // worst case scenario, no content but still traversable
        return array();
    }

    /**
     * Override list of languages with local fixed list. Useful for fallbacks.
     * 
     * @return Capita_TI_Model_Api_Languages
     */
    public function setLocalLanguages()
    {
        $this->languages = array();
        foreach (Mage::app()->getLocale()->getOptionLocales() as $locale) {
            $this->languages[@$locale['value']] = @$locale['label'];
        }
        return $this;
    }

    public function getLanguagesInUse()
    {
        $codes = array();
        /* @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $store) {
            $code = (string) $store->getConfig('general/locale/code');
            $codes[$code] = true;
        }
        $languages = $this->getLanguages();
        return array_intersect_key($languages, $codes);
    }

    /**
     * Successful response is cached transparently, this explicitly clears it.
     * 
     * Currently only languages are cacheable.
     * If this changes then consider moving this function to parent class.
     * 
     * @return Capita_TI_Model_Api_Languages
     */
    public function clearCache()
    {
        $cacheId = 'capita_ti_languages_'.$this->getUsername();
        Mage::app()->removeCache($cacheId);
        return $this;
    }
}
