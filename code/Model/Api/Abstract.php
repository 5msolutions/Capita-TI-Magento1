<?php

class Capita_TI_Model_Api_Abstract extends Zend_Http_Client
{

    const CACHE_TAG = 'CAPITA_TI';

    protected function getUsername()
    {
        return Mage::getStoreConfig('capita_ti/authentication/username');
    }

    protected function getPassword()
    {
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('capita_ti/authentication/password'));
    }

    protected function getEndpoint($path)
    {
        $baseUrl = Mage::getStoreConfig('capita_ti/base_url');
        return rtrim($baseUrl, DS) . DS . ltrim($path, DS);
    }

    public function __construct($uri = null, $config = null)
    {
        $versionSlug = 'Magento '.Mage::getVersion();

        if (extension_loaded('curl') && !@$config['adapter']) {
            $config['adapter'] = 'Zend_Http_Client_Adapter_Curl';
            $curlInfo = curl_version();
            $versionSlug .= '; cURL '.@$curlInfo['version'];
        }

        if (!@$config['useragent']) {
            $config['useragent'] = sprintf(
                'Capita_TI/%s (%s)',
                Mage::helper('capita_ti')->getModuleVersion(),
                $versionSlug);
        }

        $this->setAuth($this->getUsername(), $this->getPassword());

        parent::__construct($uri, $config);
    }

    /**
     * Convert JSON to equivalent array
     * 
     * @param Zend_Http_Response $response
     * @throws Zend_Http_Exception
     * @throws Zend_Http_Client_Exception
     * @return array
     */
    protected function decode(Zend_Http_Response $response)
    {
        if (!preg_match('#^application/json(?:$|;)#', $response->getHeader('Content-Type'))) {
            if ($response->isError()) {
                throw new Zend_Http_Exception($response->getMessage(), $response->getStatus());
            }
            throw new Zend_Http_Client_Exception('Content type is not JSON');
        }
        $body = Zend_Json::decode($response->getBody());
        if ($response->isError()) {
            throw new Zend_Http_Exception(@$body['message'], $response->getStatus());
        }
        return $body;
    }
}
