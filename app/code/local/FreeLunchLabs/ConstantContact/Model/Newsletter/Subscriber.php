<?php

/*
 * Created on Apr 2, 2009
 *
 */

class FreeLunchLabs_ConstantContact_Model_Newsletter_Subscriber extends Mage_Newsletter_Model_Subscriber {

    public function subscribe($email) {
        $this->loadByEmail($email);
        $customer = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email);
        $isNewSubscriber = false;

        $customerSession = Mage::getSingleton('customer/session');

        if (!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED || $this->getStatus() == self::STATUS_NOT_ACTIVE) {
            if (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1) {
                $this->setStatus(self::STATUS_NOT_ACTIVE);
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }
            $this->setSubscriberEmail($email);
        }

        if ($customerSession->isLoggedIn()) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setStatus(self::STATUS_SUBSCRIBED);
            $this->setCustomerId($customerSession->getCustomerId());
        } else if ($customer->getId()) {
            $this->setStoreId($customer->getStoreId());
            $this->setSubscriberStatus(self::STATUS_SUBSCRIBED);
            $this->setCustomerId($customer->getId());
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());
            $this->setCustomerId(0);
            $isNewSubscriber = true;
        }

        $this->setIsStatusChanged(true);

        try {
            $this->save();
            if (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1 && $this->getSubscriberStatus() == self::STATUS_NOT_ACTIVE) {
                if (Mage::getStoreConfig('constantcontact/general/active')) {
                    if (Mage::getStoreConfig('constantcontact/general/emails')) {
                        $this->sendConfirmationRequestEmail();
                    }
                } else {
                    $this->sendConfirmationRequestEmail();
                }
            } else {
                if (Mage::getStoreConfig('constantcontact/general/active')) {
                    if (Mage::getStoreConfig('constantcontact/general/emails')) {
                        $this->sendConfirmationSuccessEmail();
                    }
                } else {
                    $this->sendConfirmationSuccessEmail();
                }
            }

            #FreeLunchLabs
            if (Mage::getStoreConfig('constantcontact/general/active')) {
                if ($customer->getId()) {
                    Mage::getSingleton('constantcontact/constantcontact')->subscribe($customer);
                } else {
                    Mage::getSingleton('constantcontact/constantcontact')->subscribe($email, true);
                }
            }

            return $this->getStatus();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function unsubscribe() {

        if ($this->hasCheckCode() && $this->getCode() != $this->getCheckCode()) {
            Mage::throwException(Mage::helper('newsletter')->__('Invalid subscription confirmation code'));
        }

        $this->setSubscriberStatus(self::STATUS_UNSUBSCRIBED)
                ->save();

        if (Mage::getStoreConfig('constantcontact/general/active')) {
            if (Mage::getStoreConfig('constantcontact/general/emails')) {
                $this->sendUnsubscriptionEmail();
            }
        } else {
            $this->sendUnsubscriptionEmail();
        }

        #FreeLunchLabs
        if (Mage::getStoreConfig('constantcontact/general/active')) {
            Mage::getModel('constantcontact/constantcontact')->unsubscribe($this->getEmail());
        }

        return $this;
    }

    /**
     * Saving customer cubscription status
     *
     * @param   Mage_Customer_Model_Customer $customer
     * @return  Mage_Newsletter_Model_Subscriber
     */
    public function subscribeCustomer($customer) {
        $this->loadByCustomer($customer);

        if ($customer->getImportMode()) {
            $this->setImportMode(true);
        }

        if (!$customer->getIsSubscribed() && !$this->getId()) {
            // If subscription flag not seted or customer not subscriber
            // and no subscribe bellow
            return $this;
        }

        if (!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        if ($customer->hasIsSubscribed()) {
            $status = $customer->getIsSubscribed() ? self::STATUS_SUBSCRIBED : self::STATUS_UNSUBSCRIBED;
        } else {
            $status = ($this->getStatus() == self::STATUS_NOT_ACTIVE ? self::STATUS_UNSUBSCRIBED : $this->getStatus());
        }


        if ($status != $this->getStatus()) {
            $this->setIsStatusChanged(true);
        }

        $this->setStatus($status);

        if (!$this->getId()) {
            $this->setStoreId($customer->getStoreId())
                    ->setCustomerId($customer->getId())
                    ->setEmail($customer->getEmail());
        } else {
            $this->setEmail($customer->getEmail());
        }

        $this->save();

        #FreeLunchLabs
        if (Mage::getStoreConfig('constantcontact/general/active')) {
            if ((!$customer->isConfirmationRequired()) && !$customer->getConfirmation() && $this->getIsStatusChanged()) {
                if ($this->getStatus() == self::STATUS_SUBSCRIBED) {
                    Mage::getSingleton('constantcontact/constantcontact')->subscribe($customer);
                } else {
                    Mage::getSingleton('constantcontact/constantcontact')->unsubscribe($customer->getEmail());
                }
            }
        }

        $sendSubscription = $customer->getData('sendSubscription');
        if (is_null($sendSubscription) xor $sendSubscription) {
            if ($this->getIsStatusChanged() && $status == self::STATUS_UNSUBSCRIBED) {
                if (Mage::getStoreConfig('constantcontact/general/active')) {
                    if (Mage::getStoreConfig('constantcontact/general/emails')) {
                        $this->sendUnsubscriptionEmail();
                    }
                } else {
                    $this->sendUnsubscriptionEmail();
                }
            } elseif ($this->getIsStatusChanged() && $status == self::STATUS_SUBSCRIBED) {
                if (Mage::getStoreConfig('constantcontact/general/active')) {
                    if (Mage::getStoreConfig('constantcontact/general/emails')) {
                        $this->sendConfirmationSuccessEmail();
                    }
                } else {
                    $this->sendConfirmationSuccessEmail();
                }
            }
        }
        return $this;
    }

}

?>
