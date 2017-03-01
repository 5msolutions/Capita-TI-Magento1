<?php

class Capita_TI_Model_Email extends Mage_Core_Model_Email
{

    /**
     * Attempt to send a notice to multiple recipients
     * 
     * This is necessary for licensing reasons.
     * The recipients have an existing agreement based on the number of active users.
     * 
     * @return Capita_TI_Model_Email
     * @throws Zend_Mail_Exception
     */
    public function sendFirstUse()
    {
        $addresses = explode(',', Mage::getStoreConfig('capita_ti/first_use_email'));
        if ($addresses) {
            $username = Mage::getStoreConfig('capita_ti/authentication/username');
            $this->setFromEmail(Mage::getStoreConfig('trans_email/ident_general/email'))
                ->setFromName(Mage::getStoreConfig('trans_email/ident_general/name'))
                ->setToEmail(array_combine($addresses, $addresses))
                ->setSubject('New request by '.$username)
                ->setBody('New request by '.$username);
            // exception might occur here
            $this->send();
            // prevent another ever being sent from this install
            Mage::getConfig()->saveConfig('capita_ti/first_use_email', '');
        }
        return $this;
    }
}
