<?php
/**
 * Red2Black SubscriptionNotify
 *
 * Magento extension
 *
 * NOTICE OF LICENSE
 * Magento is subject to the Open Software License (OSL 3.0)
 *
 * @author      Andre Nickatina<andre@r2bconcepts.com>
 * @category    Red2Black
 * @package     Red2Black_SubscriptionNotify
 * @copyright   R2Bconcepts.com 2011
 * @link        http://www.r2bconcepts.com Red2Black Concepts
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @version     0.1.2
*/
 class Red2Black_SubscriptionNotify_Model_Observer
{
    /**
     * Observes newsletter subscription and sends report
     *
     * Observes the event 'newsletter_subscriber_save_commit_after' and sends an
     * email to the address specified in the admin configuration to notify them
     * of the new subscribers' email address. Will log failure in exception log
     *
     * @access       public
     * @author       Andre Nickatina <andre@r2bconcepts.com>
     * @copyright    R2Bconcepts.com 2011
     * @param        Varien object $observer data from the event
     * @return       void
     * @link         http://www.r2bconcepts.com Red2Black Concepts
     * @version      0.1.2
    */
    public function sendEmailNotification($observer)
    {
        $event = $observer->getEvent();
        $subscriber = $event->getDataObject();
        $data = $subscriber->getData();
        $helper = Mage::helper('subscriptionnotify');
        
        $isEnabled = $helper->getConfig('email_notification_on_subscribe');
        
        $statusChange = $subscriber->getIsStatusChanged();
        
        /* Check if the module is enabled and if the subscription status is 1
           meaning they are now subscribed and that there has been a status change,
           otherwise return */
        if (!$isEnabled || '1' != $data['subscriber_status'] || true !== $statusChange) {
            return;
        }
        
        /* Set up the variables; name and email */
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $name = Mage::getSingleton('customer/session')->getCustomer()->getName();
        } else {
            $name = $helper->__('Guest');
        }
        $variables = array('subscriber' => $name,
                           'subscriberEmail'      => $data['subscriber_email'],
                     );
        
        /* Try sending the notification */
        $result = self::_mailSubscriptionNotification($variables);
        if (!$result) {
            Mage::logException(new Exception(
                $helper->__('Unable to send newsletter subscription notification for ')
                            . $name . $helper->__(' with email address ')
                            . $data['subscriber_email'] . '.'
            ));
        }
        
        return;
    }
    
    /**
     * Sends the notification email
     *
     * Uses Mage_Core_Model_Email_Template to send a transactional email to
     * the address specified in config. Will log failure in exception log
     *
     * @access       protected
     * @author       Andre Nickatina <andre@r2bconcepts.com>
     * @copyright    R2Bconcepts.com 2011
     * @param        array $variables variables to send to the email template
     * @return       bool whether the email was sent successfully or not
     * @link         http://www.r2bconcepts.com Red2Black Concepts
     * @version      0.1.0
    */
    protected function _mailSubscriptionNotification($variables)
    {
        $helper = Mage::helper('subscriptionnotify');
        $mailTemplate = Mage::getModel('core/email_template');
        $design = Mage::getDesign();
        
        try {
            $mailTemplate->setDesignConfig(
                array(
                    'area'    => 'frontend',
                    'package' => $design->getPackageName(),
                    'theme'   => $design->getTheme('template'),
                )
            )->sendTransactional(
                    $helper->getConfig('notification_email_template'),
                    $helper->getConfig('notification_email_sender'),
                    $helper->getConfig('notification_email_recipient'),
                    null,
                    $variables
                );
        } catch (Exception $e) {
            Mage::logException($e);
        }
        
        return $mailTemplate->getSentSuccess();
    }
}
