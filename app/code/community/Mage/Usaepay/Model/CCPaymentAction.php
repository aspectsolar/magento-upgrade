<?php
/**
 * USA ePay Magento Plugin.
 * v1.1.4 - November 8th, 2013
 * 
 * For assistance please contact devsupport@usaepay.com
 * 
 * Copyright (c) 2010 USAePay
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions 
 * are met:
 * 
 *     - Redistributions of source code must retain the above copyright 
 *       notice, this list of conditions and the following disclaimer.
 *     - Redistributions in binary form must reproduce the above 
 *       copyright notice, this list of conditions and the following 
 *       disclaimer in the documentation and/or other materials 
 *       provided with the distribution.
 *     - Neither the name of the USAePay nor the names of its 
 *       contributors may be used to endorse or promote products 
 *       derived from this software without specific prior written 
 *       permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT 
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS 
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE 
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER 
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT 
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN 
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * @category    Mage
 * @package     Mage_Usaepay_Model_CCPaymentAction
 * @copyright   Copyright (c) 2010 USAePay  (www.usaepay.com)
 * @license     http://opensource.org/licenses/bsd-license.php  BSD License
 */

class Mage_Usaepay_Model_CCPaymentAction extends Mage_Payment_Model_Method_Cc
{
    protected $_code  = 'usaepay';
    protected $_formBlockType = 'usaepay/form';

    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;
    
    protected $_authMode                = 'auto';
   
    public function authorize(Varien_Object $payment, $amount)
    {
        
        // initialize transaction object
        $tran = $this->_initTransaction($payment);
		
		$useExtendedFraudProfiling = $this->getConfigData('extendedfraudprofiling');
		
		if($useExtendedFraudProfiling)
		{
			// Mage::log('payment additional information (sessionid): '.print_r($payment->getAdditionalInformation('usaepay_efpSessionId'), true));
			$sessionId = $payment->getAdditionalInformation('usaepay_efpSessionId');
			if($sessionId) $tran->session = $sessionId;
		}
        
        // general payment data
        $tran->cardholder = $payment->getCcOwner();
        $tran->card       = $payment->getCcNumber();
        $tran->exp        = $payment->getCcExpMonth().substr($payment->getCcExpYear(), 2, 2);
        $tran->cvv2       = $payment->getCcCid();
        $tran->amount     = $amount;
        $tran->ponum      = $payment->getPoNumber();
        
        if($this->getConfigData('sandbox')) {
        	$tran->custreceipt=true;
        	$tran->custreceipt_template = $this->getConfigData('custreceipt_template');	
        }
        
        // if order exists,  add order data
        $order = $payment->getOrder();
        if (!empty($order)) {
        	
            $orderid = $order->getIncrementId();
            $tran->invoice = $orderid;
            $tran->orderid = $orderid;
            $tran->ip      = $order->getRemoteIp();
            $tran->email   = $order->getCustomerEmail();
            
            $tran->tax      = $order->getTaxAmount();
            $tran->shipping = $order->getShippingAmount();

            $tran->description=($this->getConfigData('description')?str_replace('[orderid]',$orderid,$this->getConfigData('description')):"Magento Order #" . $orderid);
            
            // billing info
			$billing = $order->getBillingAddress();
			if (!empty($billing)) {
				// avs data
				list($avsstreet) = $billing->getStreet();
				$tran->street = $avsstreet;
				$tran->zip    = $billing->getPostcode();
				
				$tran->billfname = $billing->getFirstname();
				$tran->billlname = $billing->getLastname();
				$tran->billcompany = $billing->getCompany();
				$tran->billstreet = $billing->getStreet(1);
				$tran->billstreet2 = $billing->getStreet(2);
				$tran->billcity = $billing->getCity();
				$tran->billstate = $billing->getRegion();
				$tran->billzip = $billing->getPostcode();
				$tran->billcountry = $billing->getCountry();
				$tran->billphone= $billing->getTelephone();
				$tran->custid = $billing->getCustomerId();
            }
            
            // shipping info
            $shipping = $order->getShippingAddress();
            if (!empty($shipping)) {
               $tran->shipfname   = $shipping->getFirstname();
               $tran->shiplname   = $shipping->getLastname();
               $tran->shipcompany = $shipping->getCompany();
               $tran->shipstreet  = $shipping->getStreet(1);
               $tran->shipstreet2 = $shipping->getStreet(2);
               $tran->shipcity    = $shipping->getCity();
               $tran->shipstate   = $shipping->getRegion();
               $tran->shipzip     = $shipping->getPostcode();
               $tran->shipcountry = $shipping->getCountry();
            }
            
            // line item data
            foreach ($order->getAllVisibleItems() as $item) {
               $tran->addLine($item->getSku(), $item->getName(), '', $item->getPrice(), $item->getQtyToInvoice(), $item->getTaxAmount());
			}
        }

        //file_put_contents(tempnam('/tmp','authorize'), print_r($payment,true));
    	
        // switch command based on pref
        if($this->getConfigData('payment_action') == self::ACTION_AUTHORIZE && $this->_authMode!='capture')   $tran->command='cc:authonly';
        else $tran->command='cc:sale';

        //ueLogDebug("CCPaymentAction::Authorize   Amount: $amount    AuthMode: " . $this->_authMode . "     Command: " . $tran->command . "\n" );
        
        // process transactions
        $tran->Process();
        
        // store response variables
        $payment->setCcApproval($tran->authcode)
            ->setCcTransId($tran->refnum)
            ->setCcAvsStatus($tran->avs_result_code)
            ->setCcCidStatus($tran->cvv2_result_code);
            
        if($tran->resultcode=='A')
        {
            if($this->getConfigData('payment_action') == self::ACTION_AUTHORIZE) $payment->setLastTransId('0');
            else $payment->setLastTransId($tran->refnum);
			
			$isResponseSet = false;
			if($useExtendedFraudProfiling)
			{
				$useSuspectedFraudConfig = (int)$this->getConfigData('usesuspectedfraud');
				$isFraud = ($useSuspectedFraudConfig === 2 && $tran->profilerResponse == 'reject') ||
					($useSuspectedFraudConfig === 3 && ($tran->profilerResponse == 'reject' || $tran->profilerResponse == 'review'));
				
				if($useSuspectedFraudConfig && $isFraud)
				{
					$payment->setIsTransactionPending(true);
					$payment->setIsFraudDetected(true);
					$isResponseSet = true;
				}
			}
			
			if(!$isResponseSet)
			{
				$payment->setStatus(self::STATUS_APPROVED);
			}
            
	        //ueLogDebug("CCPaymentAction::Authorize  Approved");
            
        } elseif($tran->resultcode == 'D') {
            
	        //ueLogDebug("CCPaymentAction::Authorize  Declined" );
            
        	Mage::throwException(Mage::helper('paygate')->__('Payment authorization transaction has been declined:  ' . $tran->error));
        } else {
            
	        //ueLogDebug("CCPaymentAction::Authorize  Error" );
            
        	Mage::throwException(Mage::helper('paygate')->__('Payment authorization error:  ' . $tran->error . '('.$tran->errorcode . ')'));
        }
		
		if ($useExtendedFraudProfiling && $tran->profilerResponse && !empty($order)) {
			$comment = "Extended Fraud Profiler Results:\n";
			if($tran->profilerResponse) $comment .= "<br>response: {$tran->profilerResponse}\n";
			// score can be 0 so check it strictly against empty string
			if($tran->profilerScore !== '') $comment .= "<br>score: {$tran->profilerScore}\n";
			if($tran->profilerReason) $comment .= "<br>reason: {$tran->profilerReason}\n";
			$order->addStatusHistoryComment($comment);
			$order->save();
		}
        
        return $this;
    }
    
    public function quicksale(Varien_Object $payment, $amount)
    {
        
        // initialize transaction object
        $tran = $this->_initTransaction($payment);
        
        if(!$payment->getLastTransId())  Mage::throwException(Mage::helper('paygate')->__('Unable to find previous transaction to reference'));
        
        // payment data 
        $tran->refnum	  = $payment->getLastTransId();
        $tran->amount     = $amount;
        $tran->ponum      = $payment->getPoNumber();
        
        if($this->getConfigData('sandbox')) {
        	$tran->custreceipt=true;
        	$tran->custreceipt_template = $this->getConfigData('custreceipt_template');	
        }
        
        // if order exists,  add order data
        $order = $payment->getOrder();
        if (!empty($order)) {
        	
            $orderid = $order->getIncrementId();
            $tran->invoice = $orderid;
            $tran->orderid = $orderid;
            $tran->ip      = $order->getRemoteIp();
            $tran->email   = $order->getCustomerEmail();
            
            $tran->tax      = $order->getTaxAmount();
            $tran->shipping = $order->getShippingAmount();
            
            $tran->description=($this->getConfigData('description')?str_replace('[orderid]',$orderid,$this->getConfigData('description')):"Magento Order #" . $orderid);
            
            // billing info
            $billing = $order->getBillingAddress();
            if (!empty($billing)) {
				// avs data
				list($avsstreet) = $billing->getStreet();
				$tran->street = $avsstreet;
				$tran->zip    = $billing->getPostcode();
				
				$tran->billfname = $billing->getFirstname();
				$tran->billlname = $billing->getLastname();
				$tran->billcompany = $billing->getCompany();
				$tran->billstreet = $billing->getStreet(1);
				$tran->billstreet2 = $billing->getStreet(2);
				$tran->billcity = $billing->getCity();
				$tran->billstate = $billing->getRegion();
				$tran->billzip = $billing->getPostcode();
				$tran->billcountry = $billing->getCountry();
				$tran->billphone= $billing->getTelephone();
				$tran->custid = $billing->getCustomerId();
            }
            
            // shipping info
            $shipping = $order->getShippingAddress();
            if (!empty($shipping)) {
               $tran->shipfname   = $shipping->getFirstname();
               $tran->shiplname   = $shipping->getLastname();
               $tran->shipcompany = $shipping->getCompany();
               $tran->shipstreet  = $shipping->getStreet(1);
               $tran->shipstreet2 = $shipping->getStreet(2);
               $tran->shipcity    = $shipping->getCity();
               $tran->shipstate   = $shipping->getRegion();
               $tran->shipzip     = $shipping->getPostcode();
               $tran->shipcountry = $shipping->getCountry();
            }
        }

        //file_put_contents(tempnam('/tmp','quicksale'), print_r($payment,true));
    	

        //ueLogDebug("Sending quicksale for $amount on prior transid {$tran->refnum}");
        $tran->command='quicksale';
        
        // process transactions
        $tran->Process();
        
        // store response variables
        $payment->setCcApproval($tran->authcode)
            ->setCcTransId($tran->refnum)
            ->setCcAvsStatus($tran->avs_result_code)
            ->setCcCidStatus($tran->cvv2_result_code);
        
       // ueLogDebug("Tran:" . print_r($tran, true));
            
            
        if($tran->resultcode=='A')
        {
            if($tran->refnum) $payment->setLastTransId($tran->refnum);
            $payment->setStatus(self::STATUS_APPROVED);
            //ueLogDebug("Transaction Approved");
        } elseif($tran->resultcode == 'D') {
            //ueLogDebug("Transaction Declined");
            Mage::throwException(Mage::helper('paygate')->__('Payment authorization transaction has been declined:  ' . $tran->error));
        } else {
           //ueLogDebug("Transaction Error");
            Mage::throwException(Mage::helper('paygate')->__('Payment authorization error:  ' . $tran->error . '('.$tran->errorcode . ')'));
        }
        
        return $this;
    }
	
    public function refund(Varien_Object $payment, $amount)
    {
        
        // ueLogDebug("CCPaymentAction::refund amount: $amount  transid: " . $payment->getLastTransId());
        $error = false;
        
        $orderid=$payment->getOrder()->getIncrementId();
        
        list($avsstreet) = $payment->getOrder()->getBillingAddress()->getStreet();
        $avszip = $payment->getOrder()->getBillingAddress()->getPostcode();
        
        $tran = $this->_initTransaction($payment);
        
        if(!$payment->getLastTransId())  Mage::throwException(Mage::helper('paygate')->__('Unable to find previous transaction to reference'));
        
        // payment data 
        $tran->refnum	  = $payment->getLastTransId();
        $tran->amount=$amount;
        $tran->invoice=$orderid;
        $tran->orderid=$orderid;
        $tran->cardholder=$payment->getCcOwner();
        $tran->street=$avsstreet;
        $tran->zip=$avszip;
        $tran->description="Online Order";
        $tran->cvv2=$payment->getCcCid();
        $tran->command='quickcredit';
        
        if(!$tran->Process())
        {
            $payment->setStatus(self::STATUS_ERROR);
            $error = Mage::helper('paygate')->__('Error in authorizing the payment: '.$tran->error);
            Mage::throwException('Payment Declined: '.$tran->error.' ('.$tran->errorcode );
        } else {
            $payment->setStatus(self::STATUS_APPROVED);
        }
        
        if ($error !== false) {
            Mage::throwException($error);
        }
        return $this;
    }

    public function capture(Varien_Object $payment, $amount)
    {
    
    	//file_put_contents(tempnam('/tmp','capture'), print_r($payment,true));
    	
        //ueLogDebug("CCPaymentAction::Capture  Amount: $amount CcTransId: " . $payment->getCcTransId() . "    LastTransId: " . $payment->getLastTransId() . "  TotalPaid:   " . $payment->getOrder()->getTotalPaid() . "  Cardnumber(doh):" . $payment->getCcNumber() . "\n");
    		
        // we have already captured the original auth,  we need to do full sale    		
    	if($payment->getLastTransId()  && $payment->getOrder()->getTotalPaid()>0)
    	{
    		return $this->quicksale($payment, $amount);
    	}
    		
        // if we don't have a transid than we are need to authorize
        if(!$payment->getCcTransId() || $payment->getLastTransId()) {
           $this->_authMode='capture';
           return $this->authorize($payment, $amount);
        }
                
        $tran = $this->_initTransaction($payment);
        $tran->command='cc:capture';
        $tran->refnum=$payment->getCcTransId();
        
        $tran->amount=$amount;
        
        // process transaction
        $tran->Process();
        
        // look at result code
        if($tran->resultcode=='A')
        {
            $payment->setStatus(self::STATUS_APPROVED);
            $payment->setLastTransId($tran->refnum);
            return $this;
        } elseif($tran->resultcode == 'D') {
            Mage::throwException(Mage::helper('paygate')->__('Payment authorization transaction has been declined:  ' . $tran->error));
        } else {
            Mage::throwException(Mage::helper('paygate')->__('Payment authorization error:  ' . $tran->error . '('.$tran->errorcode . ')'));
        }    
    }

    public function canVoid(Varien_Object $payment)
    {
        return $this->_canVoid;
    }

    public function void(Varien_Object $payment)
    {
        //ueLogDebug("CCPaymentAction::refund amount: $amount  transid: " . $payment->getLastTransId());
        
        if ($payment->getCcTransId()) {
            $tran = $this->_initTransaction($payment);
            $tran->command='creditvoid';
            $tran->refnum=$payment->getCcTransId();
            
            // process transactions
            $tran->Process();
            if($tran->resultcode=='A')
            {
               $payment->setStatus(self::STATUS_SUCCESS);
               
            } elseif($tran->resultcode == 'D') {
            	$payment->setStatus(self::STATUS_ERROR);
               Mage::throwException(Mage::helper('paygate')->__('Payment authorization transaction has been declined:  ' . $tran->error));
            } else {
            	$payment->setStatus(self::STATUS_ERROR);
               Mage::throwException(Mage::helper('paygate')->__('Payment authorization error:  ' . $tran->error . '('.$tran->errorcode . ')'));
            }
        } else {
            $payment->setStatus(self::STATUS_ERROR);
            Mage::throwException(Mage::helper('paygate')->__('Invalid transaction id '));
        }
        return $this;
    }
    
    
    
   /**
     * Setup the USAePay transaction api class.
     * 
     * Much of this code is common to all commands
     *
     * @param Mage_Sales_Model_Document $pament
     * @return Mage_Usaepay_Model_TranApi
     */
    protected function _initTransaction(Varien_Object $payment)
    {
        $tran = Mage::getModel('usaepay/TranApi');
        
        if($this->getConfigData('sandbox')) $tran->usesandbox=true;
        
        $tran->key=$this->getConfigData('sourcekey');
        $tran->pin=$this->getConfigData('sourcepin');
        $tran->software = 'Mage_Usaepay 1.0.2';
        return $tran;
    }
}

/*
function ueLogDebug($mesg)
{
	global $debugfd;
	
	if(!$debugfd) {
		$debugfd = fopen('/tmp/uelog','a');
	}
	
	fwrite($debugfd, $mesg. "\n");
	
}
*/