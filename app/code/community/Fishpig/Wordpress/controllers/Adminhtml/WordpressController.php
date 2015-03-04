<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Adminhtml_WordpressController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * URL to get release information for extension
	 *
	 * @const string
	 */
	const URL_RELEASES = 'http://connect20.magentocommerce.com/community/Fishpig_Wordpress_Integration/releases.xml';

	/**
	 * Display the form for auto-login details
	 *
	 */
	public function autologinAction()
	{
		$this->loadLayout();
		$this->_title($this->__('WordPress Admin'));
		$this->_setActiveMenu('wordpress');
		$this->renderLayout();
	}
	
	/**
	 * Save the auto-login details
	 *
	 */
	public function autologinpostAction()
	{
		if ($data = $this->getRequest()->getPost()) {
			try {
				$data['user_id'] = Mage::getSingleton('admin/session')->getUser()->getUserId();
				$autologin	= Mage::getModel('wordpress/admin_user');
				$autologin->setData($data)->setId($this->getRequest()->getParam('id'));

				$autologin->save();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Your Wordpress Auto-login details were successfully saved.'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);				
			}
			catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
			}
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('There was an error while trying to save your Wordpress Auto-login details.'));
		}
		
        $this->_redirect('*/*/autologin');
	}

	/**
	 * Attempt to login to the WordPress Admin action
	 *
	 */
	public function loginAction()
	{
		if (!Mage::helper('wordpress/system')->hasValidCurlMethods()) {
			Mage::getSingleton('adminhtml/session')->addError('Update Magento to version 1.7.0.0 to use the Auto-Login feature.');

			return $this->_redirect('adminhtml/system_config/edit/section/wordpress');
		}
		
		try {
			$user = Mage::getSingleton('wordpress/admin_user');
			
			if (!$user->getId()) {
				throw new Exception('WordPress Auto-Login details not set. Login failed.');
			}

			Mage::helper('wordpress/system')->loginToWordPress(
				$user->getUsername(), 
				$user->getPassword(), 
				Mage::helper('wordpress')->getAdminUrl()
			);

			$this->_redirectUrl(
				Mage::helper('wordpress')->getAdminUrl('index.php')
			);
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);

			Mage::getSingleton('adminhtml/session')->addError('Set your Wordpress Admin login details below. Once you have done this you will be able to login to Wordpress with 1 click by selecting Wordpress Admin from the top menu.');
			
			Mage::getSingleton('adminhtml/session')->addNotice($this->__('Having problems logging in to the WordPress Admin? The following article contains tips and advice on how to solve auto-login issues: %s', 'http://fishpig.co.uk/wordpress-integration/docs/wp-admin-auto-login.html'));
			
			$this->_redirect('adminhtml/wordpress/autologin');
		}
	}
	
	/**
	 * Check for the latest WordPress versions
	 *
	 */
	public function checkVersionAction()
	{
		$current = Mage::helper('wordpress/system')->getExtensionVersion();
		$cacheKey = 'wordpress_integration_update' . $current;

		try {
			if (($latest = Mage::app()->getCache()->load($cacheKey)) === false) {
				$response = Mage::helper('wordpress/system')->makeHttpPostRequest(
					self::URL_RELEASES
				);
				
				if (strpos($response, '<?xml') === false) {
					throw new Exception('Invalid response');
				}

				$response = trim(substr($response, strpos($response, '<?xml')));
				$xml = simplexml_load_string($response);
				$latest = false;
				
				foreach($xml->r as $release) {
					if ((string)$release->s === 'stable') {
						if (!$latest || version_compare($release->v, $latest, '>=')) {
							$latest = (string)$release->v;
						}
					}
				}
				
				Mage::app()->getCache()->save(
					$latest,
					$cacheKey, 
					array('WP_UPDATE'), 
					((60*60)*24)*7
				);
			}

			$this->getResponse()
				->setHeader('Content-Type', 'application/json; charset=UTF-8')
				->setBody(
					json_encode(
						array('latest_version' => $latest)
					)
				);
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);
		}		
	}
}
