<?php	

require_once('app/Mage.php'); //Path to Magento
umask(0);
Mage::app();

$emailaddr =$_POST["email"];	

            
            $email = $emailaddr;
            
            try{
                    // Transactional Email Template's ID
					$templateId = 11;
				 
					// Set sender information          
					$senderName = 'Aspect Solar';
					$senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');    
					$sender = array('name' => $senderName,
								'email' => $senderEmail);
					 
					// Set recepient information
					$recepientEmail = $email;
					$recepientName = '';       
					 
					// Get Store ID    
					$store = Mage::app()->getStore()->getId();
				 
					// Set variables that can be used in email template
					$vars = array('customerEmail' => $email);
							 
					$translate  = Mage::getSingleton('core/translate');
				 
					// Send Transactional Email
					Mage::getModel('core/email_template')
						->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
							 
					$translate->setTranslateInline(true);    
                    echo "Newsletter sent to ".$email;
    
            } catch(Exception $e) {
                echo $e->getMessage();
            }

			
			
		
			
			
			
			
			
			
			
			
			
			
			
			
 ?>