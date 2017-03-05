<?php

/**
 * @method int getProductCount()
 * @method int[] getProductIds()
 * @method int getCategoryCount()
 * @method int[] getCategoryIds()
 * @method int getBlockCount()
 * @method int[] getBlockIds()
 * @method int getPageCount()
 * @method int[] getPageIds()
 * @method string getDestLanguage()
 * @method string getCategoryAttributes()
 * @method string getProductAttributes()
 * @method string getSourceLanguage()
 * @method Capita_TI_Model_Request_Document[] getDocuments()
 * @method Capita_TI_Model_Request setProductIds(int[])
 * @method Capita_TI_Model_Request setSourceLanguage(string)
 */
class Capita_TI_Model_Request extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/request');
    }

    protected function _initOldFieldsMap()
    {
        $this->_oldFieldsMap = array(
            'RequestId' => 'remote_id',
            'RequestNo' => 'remote_no',
            'RequestStatus' => 'status',
            'Documents' => 'documents'
        );
        return $this;
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

    /**
     * Checks an ISO language code against this request's target languages
     * 
     * @param string $language
     * @return boolean
     */
    public function hasDestLanguage($language = null)
    {
        if (is_null($language)) {
            return parent::hasDestLanguage();
        }
        $destLanguage = $this->getDestLanguage();
        if (is_string($destLanguage)) {
            $destLanguage = explode(',', $destLanguage);
        }
        return in_array($language, $destLanguage);
    }

    /**
     * Converts from internal string to array of strings
     * 
     * @return string[]
     */
    public function getProductAttributesArray()
    {
        return explode(',', $this->getProductAttributes());
    }

    /**
     * Converts attribute codes to frontend labels, separated by commas
     * 
     * @return string
     */
    public function getProductAttributeNames()
    {
        if ($this->hasProductAttributeNames()) {
            return parent::getProductAttributeNames();
        }

        /* @var $attributes Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributes->addFieldToFilter('attribute_code', array('in' => $this->getProductAttributesArray()));
        $names = implode(', ', $attributes->getColumnValues('frontend_label'));
        $this->setProductAttributeNames($names);
        return $names;
    }

    /**
     * Either a comma-separated string or array of strings
     * 
     * @param mixed $attributeCodes
     * @return Capita_TI_Model_Request
     */
    public function setProductAttributes($attributeCodes)
    {
        parent::setProductAttributes(
            is_array($attributeCodes) ?
            implode(',', $attributeCodes) :
            (string) $attributeCodes);
        $this->unsProductAttributeNames();
        return $this;
    }

    /**
     * Converts from internal string to array of strings
     * 
     * @return string[]
     */
    public function getCategoryAttributesArray()
    {
        return explode(',', $this->getCategoryAttributes());
    }

    /**
     * Converts attribute codes to frontend labels, separated by commas
     * 
     * @return string
     */
    public function getCategoryAttributeNames()
    {
        if ($this->hasCategoryAttributeNames()) {
            return parent::getCategoryAttributeNames();
        }

        /* @var $attributes Mage_Catalog_Model_Resource_Category_Attribute_Collection */
        $attributes = Mage::getResourceModel('catalog/category_attribute_collection');
        $attributes->addFieldToFilter('attribute_code', array('in' => $this->getCategoryAttributesArray()));
        $names = implode(', ', $attributes->getColumnValues('frontend_label'));
        $this->setCategoryAttributeNames($names);
        return $names;
    }

    /**
     * Either a comma-separated string or array of strings
     * 
     * @param mixed $attributeCodes
     * @return Capita_TI_Model_Request
     */
    public function setCategoryAttributes($attributeCodes)
    {
        parent::setCategoryAttributes(
            is_array($attributeCodes) ?
            implode(',', $attributeCodes) :
            (string) $attributeCodes);
        $this->unsCategoryAttributeNames();
        return $this;
    }

    public function getStatusLabel()
    {
        return Mage::getSingleton('capita_ti/source_status')->getOptionLabel($this->getStatus());
    }

    /**
     * True if there is more to be learned from remote API
     * 
     * @return boolean
     */
    public function canUpdate()
    {
        return $this->getRemoteId() && in_array($this->getStatus(), array('onHold', 'inProgress'));
    }

    /**
     * Matches local filename to remote filename intelligently
     * 
     * If names are too dissimilar then a consistent order is
     * assumed and next available document is used.
     * 
     * @param string $filename
     */
    public function addLocalDocument($filename)
    {
        $documents = $this->getDocuments();
        foreach ($documents as &$document) {
            if ((basename($filename) == @$document['DocumentName']) || (basename($filename) == @$document['remote_name'])) {
                $document['local_name'] = $filename;
                $this->setDocuments($documents);
                return $this;
            }
        }

        // not found yet
        foreach ($documents as &$document) {
            if (!@$document['local_name']) {
                $document['local_name'] = $filename;
                $this->setDocuments($documents);
                return $this;
            }
        }

        // nothing to change
        return $this;
    }

    /**
     * What to do when a status changes?
     * 
     * It might mean downloading some files and importing them.
     * 
     * @param array $info Response decoded from API
     * @param Capita_TI_Model_Request_Document List of remote documents to download
     */
    public function updateStatus($info)
    {
        $newStatus = @$info['RequestStatus'];
        $documents = $this->getDocuments();
        foreach ($documents as &$document) {
            if ($document instanceof Capita_TI_Model_Request_Document) {
                $document->setStatus($newStatus);
            }
            else {
                $document['status'] = $newStatus;
            }
        }

        $downloads = array();
        if (($this->getStatus != 'completed') && ($newStatus == 'completed')) {
            // only care about nested arrays right now
            $remoteDocuments = call_user_func_array('array_merge_recursive', @$info['Documents']);
            $finalDocuments = (array) @$remoteDocuments['FinalDocuments'];

            foreach ($finalDocuments as $finalDocument) {
                $newdoc = Mage::getModel('capita_ti/request_document', $finalDocument);
                $filename = 'import'.DS.basename($newdoc->getRemoteName());
                // ensure directory exists
                Mage::getConfig()->getVarDir('import');
                $newdoc->setLocalName($filename)
                    ->setRequestId($this->getId());
                $downloads[] = $newdoc;
            }
        }

        $this->setDocuments(array_merge($documents, $downloads));
        $this->setStatus($newStatus);
        return $downloads;
    }
}
