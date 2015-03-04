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
 * @package     Mage_Usaepay_Block_Form
 * @copyright   Copyright (c) 2010 USAePay  (www.usaepay.com)
 * @license     http://opensource.org/licenses/bsd-license.php  BSD License
 */


class Mage_Usaepay_Block_Form extends Mage_Payment_Block_Form
{
	protected $_paymentConfig;
	
    protected function _construct()
    {
		$this->_paymentConfig = Mage::getStoreConfig('payment/usaepay');
        $this->setTemplate('usaepay/form.phtml');
        parent::_construct();
    }
    
    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }
	
	public function createExtendedFraudProfilingSession()
	{
		if(!$this->_paymentConfig['extendedfraudprofiling']) return false;
		
		$checkout = Mage::getSingleton('checkout/session');
		$stepIsAllowed = $checkout->getStepData('payment', 'allow');
		$stepIsComplete = $checkout->getStepData('payment', 'complete');
		
		if(!$stepIsAllowed || $stepIsComplete)
		{
			return false;
		}
		
		try
		{
			$method = $this->getMethod();
		
			if(!$method) return false;
			
			// payment info might not be avliable at this point (first render?)
			$paymentInfo = $method->getInfoInstance();
		}
		catch (Exception $e)
		{
			Mage::logException($e);
			return false;
		}
		
		$results = self::_getExtendedFraudProfilingSession($this->_paymentConfig);
		
		// Mage::log('createExtendedFraudProfilingSession() sessionid: '.$results['sessionid']);
		
		if($results)
		{
			// efp = extended fraud profiling
			$paymentInfo->setAdditionalInformation('usaepay_efpSessionId', $results['sessionid']);
		}
		
		return $results;
	}
    
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code=>$name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }
    
    public function getCcMonths(){
      $months['01'] = 'January';
      $months['02'] = 'February';
      $months['03'] = 'March';
      $months['04'] = 'April';
      $months['05'] = 'May';
      $months['06'] = 'June';
      $months['07'] = 'July';
      $months['08'] = 'August';
      $months['09'] = 'September';
      $months['10'] = 'October';
      $months['11'] = 'November';
      $months['12'] = 'December';
      return $months;
    }
    public function getCcYears()
    {
      for($i=0; $i<=10; $i++)
         $years[date('Y',strtotime("+$i years"))] = date('Y',strtotime("+$i years"));
      return $years;
    }
	
	static protected function _getExtendedFraudProfilingSession($config)
	{
		$seed = md5(mt_rand());
		$action = 'getsession';

		// build hash
		$prehash = $action . ":" . $config['sourcepin'] . ":" . $seed;
		$hash = 's/' . $seed . '/' . sha1($prehash);

		// Figure out URL
		$url = 'https://www.usaepay.com/interface/profiler/';
		/*if(preg_match('~https://([^/]*)/~i', $this->gatewayurl, $m)) {
			$url = 'https://' . $m[1] . '/interface/profiler/';
		}*/

		// Look for usesandbox
		if($config['sandbox']) $url = "https://sandbox.usaepay.com/interface/profiler/";

		// Add request paramters
		$url .= $action . '?SourceKey=' . rawurlencode($config['sourcekey']) . '&Hash=' . rawurlencode($hash);

		// Make Rest Call
		$output = file_get_contents($url);
		
		// Mage::log("usaepay -> extended fraud profiling session\nrequest url:\n$url\nresponse:\n$output", null, 'usaepay.log');
		
		if(!$output) {
			Mage::log('usaepay -> payment form error -> extended fraud profiling -> Blank response', Zend_Log::ERR);
			return false;
		}

		// Parse response
		$xml = simplexml_load_string($output);
		if(!$xml) {
			Mage::log('usaepay -> payment form error -> extended fraud profiling -> Unable to parse response', Zend_Log::ERR);
			return false;
		}

		if($xml->Response!='A') {
			Mage::log('usaepay -> payment form error -> extended fraud profiling -> ('.$xml->ErrorCode.') '.$xml->Reason, Zend_Log::ERR);
			return false;
		}

		// assemble template
		$query ='org_id=' .$xml->OrgID . '&session_id=' . $xml->SessionID;
		$baseurl = "https://h.online-metrix.net/fp/";

		$out = '';
		$out .= '<p style="background:url('.$baseurl.'clear.png?'.$query.'&m=1)"></p>';
		$out .= '<img src="'.$baseurl.'clear.png?'.$query.'&m=2" width="1" height="1" alt="">';
		$out .= '<script src="'.$baseurl.'check.js?'.$query.'" type="text/javascript"></script>';
		$out .= '<object type="application/x-shockwave-flash" data="'.$baseurl.'fp.swf?'.$query.'" width="1" height="1" id="thm_fp">';
		$out .= ' <param name="movie" value="'.$baseurl.'fp.swf?'.$query.'" />';
		$out .= '<div></div></object>';

		// cast xml 'objects' as strings to avoid weird issues elsewhere (like setAdditionalInfo for payments)
		return array('sessionid'=> (string)$xml->SessionID, 'orgid'=> (string)$xml->OrgID, 'html'=> $out);
	}
}