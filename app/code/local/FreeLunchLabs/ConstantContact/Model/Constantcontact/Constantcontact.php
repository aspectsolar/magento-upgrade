<?php

class FreeLunchLabs_ConstantContact_Model_Constantcontact_Constantcontact extends Varien_Object {

    var $_login = '';
    var $_password = '';
    var $_apiKey = '';
    var $_apiPath = '';
    var $contact_lists = array(); // Define which lists will be available for sign-up.
    var $force_lists = false; // Set this to true to take away the ability for users to select and de-select lists
    var $show_contact_lists = true;
    var $actionBy = 'ACTION_BY_CONTACT'; // Values: ACTION_BY_CUSTOMER or ACTION_BY_CONTACT
    // ACTION_BY_CUSTOMER - Constant Contact Account holder. Used in internal applications.
    // ACTION_BY_CONTACT - Action by Site visitor. Used in web site sign-up forms.
    var $curl_debug = true; // Set this to true to see the response code returned by cURL
    var $requestLogin;
    var $lastError = '';
    var $doNotIncludeLists = array('Removed', 'Do Not Mail', 'Active');

    public function __construct() {
        $this->_login = Mage::getStoreConfig('constantcontact/general/username');
        $this->_password = Mage::getStoreConfig('constantcontact/general/password');
        $this->_apiPath = Mage::getStoreConfig('constantcontact/general/url');
        $this->_apiKey = Mage::getStoreConfig('constantcontact/general/apikey');
        $this->requestLogin = $this->_apiKey . "%" . $this->_login . ":" . $this->_password;
        $this->_apiPath = $this->_apiPath . $this->_login;
    }

    protected function doServerCall($request, $parameter = '', $type = "GET") {
        $ch = curl_init();
        $request = str_replace('http://', 'https://', $request);
        // Convert id URI to BASIC compliant
        curl_setopt($ch, CURLOPT_URL, $request);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->requestLogin);
        # curl_setopt ($ch, CURLOPT_FOLLOWLOCATION  ,1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:application/atom+xml", 'Content-Length: ' . strlen($parameter)));
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        switch ($type) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                break;
        }

        $emessage = curl_exec($ch);
        if ($this->curl_debug) {
            $error = curl_error($ch);
        }
        curl_close($ch);

        return $emessage;
    }

    public function getLists($path = '') {
        $mailLists = array();
        if (empty($path)) {
            $call = $this->_apiPath . '/lists';
        } else {
            $call = $this->_apiPath . $path;
        }

        $return = $this->doServerCall($call);

        if (!empty($return)) {
            $parsedReturn = simplexml_load_string($return);
            $call2 = '';

            foreach ($parsedReturn->link as $item) {
                $tmp = $item->Attributes();
                $nextUrl = '';
                if ((string) $tmp->rel == 'next') {
                    $nextUrl = (string) $tmp->href;
                    $arrTmp = explode($this->_login, $nextUrl);
                    $nextUrl = $arrTmp[1];
                    $call2 = $this->apiPath . $nextUrl;
                    break;
                }
            }

            foreach ($parsedReturn->entry as $item) {
                if ($this->contact_lists) {
                    if (in_array((string) $item->title, $this->contact_lists)) {
                        $tmp = array();
                        $tmp['id'] = (string) $item->id;
                        $tmp['title'] = (string) $item->title;
                        $mailLists[] = $tmp;
                    }
                } else if (!in_array((string) $item->title, $this->doNotIncludeLists)) {
                    $tmp = array();
                    $tmp['id'] = (string) $item->id;
                    $tmp['title'] = (string) $item->title;
                    $mailLists[] = $tmp;
                }
            }

            if (empty($call2)) {
                return $mailLists;
            } else {
                return array_merge($mailLists, $this->getLists($call2));
            }
        }
    }

    protected function _isValidEmail($email) {
        return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
    }

    private function _getCustomerByEmail($email) {
        if (($email instanceof Mage_Customer_Model_Customer)) {
            $customer = $email;
            return $customer;
        }
        $collection = Mage::getResourceModel('newsletter/subscriber_collection');
        $collection
                ->showCustomerInfo(true)
                ->addSubscriberTypeField()
                ->showStoreInfo()
                ->addFieldToFilter('subscriber_email', $email);
        return $collection->getFirstItem();
    }

    private function _getListIdByStoreId($storeId = NULL) {
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getStoreId();
        }
        $store = Mage::getModel('core/store')->load($storeId);
        $list_id = $store->getConfig('constantcontact/general/listid');
        return $list_id;
    }

    public function subscribe($customer, $emailonly = false) {
        if ($emailonly) {
            $newCustomer = array();
            $newCustomer['email_address'] = $customer;
            $newCustomer['first_name'] = '';
            $newCustomer['last_name'] = '';
            $newCustomer['status'] = 'Active';
            $newCustomer['company_name'] = '';
            $newCustomer['home_number'] = '';
            $newCustomer['address_line_1'] = '';
            $newCustomer['address_line_2'] = '';
            $newCustomer['city_name'] = '';
            $newCustomer['state_name'] = '';
            $newCustomer['country_code'] = '';
            $newCustomer['zip_code'] = '';
            $contactXML = $this->createContactXML('', $newCustomer, false, false);
        } else {
            $contactXML = $this->createContactXML('', $customer);
        }
        $call = $this->_apiPath . '/contacts';
        $return = $this->doServerCall($call, $contactXML, 'POST');
        $parsedReturn = simplexml_load_string($return);

        if ($return) {
            return true;
        } else {
            $xml = simplexml_load_string($contactXML);
            $emailAddress = $xml->content->Contact->EmailAddress;

            if ($this->subscriberExists($emailAddress)) {
                $this->readdSubscriber($emailAddress);
                //they exist and now are subscribing again
                //error_log("subscriber " . $emailAddress . " allready exists");
            } else {
                error_log("error adding subscriber, check api settings");
                return false;
            }
            return false;
        }
    }

    public function subscribeNewUser($customer, $emailonly = false) {
        if ($emailonly) {
            $newCustomer = array();
            $newCustomer['email_address'] = $customer;
            $newCustomer['first_name'] = '';
            $newCustomer['last_name'] = '';
            $newCustomer['status'] = 'Active';
            $newCustomer['company_name'] = '';
            $newCustomer['home_number'] = '';
            $newCustomer['address_line_1'] = '';
            $newCustomer['address_line_2'] = '';
            $newCustomer['city_name'] = '';
            $newCustomer['state_name'] = '';
            $newCustomer['country_code'] = '';
            $newCustomer['zip_code'] = '';
            $contactXML = $this->createContactXML('', $newCustomer, false, false);
        } else {
            $contactXML = $this->createContactXML('', $customer);
        }
        $call = $this->_apiPath . '/contacts';
        $this->doServerCall($call, $contactXML, 'POST');
    }

    public function unsubscribe($email) {
        if (Mage::getStoreConfig('constantcontact/unsubscribe/delete_member')) {
            return $this->removeSubscriber($email);
        } else {
            return $this->optoutSubscriber($email);
        }
    }

    public function optoutSubscriber($email) {
        if (empty($email)) {
            return false;
        }
        $contact = $this->getSubscribers($email);
        $id = $contact['items'][0]['id'];
        $return = $this->doServerCall($id, '', 'DELETE');
        if (!empty($return)) {
            return false;
        }
        return true;
    }

    public function readdSubscriber($email) {
        $contact = $this->getSubscriberDetails($email);
        if ($contact['status'][0] == 'Do Not Mail') {
            $this->actionBy = 'ACTION_BY_CONTACT';
        }
        $xml = $this->createContactXML($contact['id'], $contact);
        if ($this->editSubscriber($contact['id'], $xml)) {
            return true;
        } else {
            return false;
        }
    }

    public function removeSubscriber($email) {
        $contact = $this->getSubscriberDetails($email);
        $contact['lists'] = array();
        $xml = $this->createContactXML($contact['id'], $contact, false, true);
        if ($this->editSubscriber($contact['id'], $xml)) {
            return true;
        } else {
            return false;
        }
    }

    public function editSubscriber($contactUrl, $contactXML) {
        $return = $this->doServerCall($contactUrl, $contactXML, 'PUT');
        if (!empty($return)) {
            if (strpos($return, '<') !== false) {
                $parsedReturn = simplexml_load_string($return);
                if (!empty($parsedReturn->message)) {
                    $this->lastError = $parsedReturn->message;
                }
            } else {
                $this->lastError = $parsedReturn->message;
            }
            return false;
        }
        return true;
    }

    public function batchSubscribe() {
        $MAGE_collection = Mage::getResourceModel('newsletter/subscriber_collection')->showStoreInfo()->showCustomerInfo();
        $CC_Array = array();
        $CC_collection = $this->getSubscribers("");
        $this->actionBy = 'ACTION_BY_CUSTOMER';
        
        while ($CC_collection['50more'] != "") {
            $next50 = $this->getSubscribers("", $CC_collection['50more']);
            $CC_collection['items'] = array_merge($CC_collection['items'], $next50['items']);

            if ($next50['50more'] == "") {
                $CC_collection['50more'] = "";
            } else {
                $CC_collection['50more'] = $next50['50more'];
            }
        }

        if (count($MAGE_collection) > 0) {

            // Loop through Constant Contact list and store email address as key.
            foreach ($CC_collection['items'] as $key => $contact) {
                $CC_Array[$contact['EmailAddress']] = $contact;
            }
            
            unset($CC_collection);

            // Update Subscribers in Magento Based off Constant Contact
            // Loop through all Magento newsletter list.
            foreach ($MAGE_collection as $cust) {
                // Check if they are on the Constant Contact List
                if (isset($CC_Array[$cust->getEmail()])) {
                    if ($CC_Array[$cust->getEmail()]['status'] == "Active" && $cust->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
                        $cust->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                        $cust->save();
                    } elseif ($CC_Array[$cust->getEmail()]['status'] == "Unconfirmed" && $cust->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                        $cust->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE);
                        $cust->save();
                    } elseif ($CC_Array[$cust->getEmail()]['status'] == "Removed" && $cust->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
                        $cust->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
                        $cust->save();
                    } elseif ($CC_Array[$cust->getEmail()]['status'] == "Do Not Mail" && $cust->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
                        $cust->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
                        $cust->save();
                    }
                    
                } else {
                    if($cust->isSubscribed()) {
                        $this->subscribeNewUser($cust);
                    }
                }
            }

            Mage::getSingleton('adminhtml/session')->addSuccess("Successful Batch Synchronization.");
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess("You do not have any newsletter subscribers stored in Magento.");
        }
    }

    public function autoSync() {
        if (Mage::getStoreConfig('constantcontact/general/sync')) {
            $this->batchSubscribe();
        }
    }

    protected function subscriberExists($email = '') {
        $call = $this->_apiPath . '/contacts?email=' . $email;
        $return = $this->doServerCall($call);
        $xml = simplexml_load_string($return);
        $id = $xml->entry->id;
        if ($id) {
            return $id;
        } else {
            return false;
        }
    }

    public function getCustomerOldEmail() {
        if (Mage::getModel('core/session')->getCustomerOldEmail()) {
            $customer_old_email = Mage::getModel('core/session')->getCustomerOldEmail();
            return $customer_old_email;
        } else {
            return '';
        }
    }

    public function createContactXML($id, $params = array(), $reOptIn = false, $delete = false) {
        
        $emailAddress = '';
        $firstName = '';
        $lastName = '';
        $company = '';
        $home = '';
        $address1 = '';
        $address2 = '';
        $city = '';
        $state = '';
        $country = '';
        $zip = '';

        if (is_object($params)) {
            if (get_class($params) == 'Mage_Customer_Model_Customer') {
                $address = $params->getPrimaryBillingAddress();
                $emailAddress = $params->getEmail();
                $firstName = $params->getFirstname();
                $lastName = $params->getLastname();
                if ($address) {
                    $company = $address->getCompany();
                    $home = $address->getTelephone();
                    $addressarr = $address->getStreet();
                    if (isset($addressarr[0])) {
                        $address1 = $addressarr[0];
                    }
                    if (isset($addressarr[1])) {
                        $address2 = $addressarr[1];
                    }
                    $city = $address->getCity();
                    $state = $address->getRegion();
                    $country = $address->getCountryId();
                    $zip = $address->getPostcode();
                } else {
                    $company = '';
                    $home = '';
                    $addressarr = '';
                    $address1 = '';
                    $address2 = '';
                    $city = '';
                    $state = '';
                    $country = '';
                    $zip = '';
                }
            } elseif (get_class($params) == 'FreeLunchLabs_ConstantContact_Model_Newsletter_Subscriber') {
                $emailAddress = $params->getSubscriberEmail();
                $firstName = $params->getCustomerFirstname();
                $lastName = $params->getCustomerLastname();
            }
        } else {
            $emailAddress = $params['email_address'];
            $firstName = $params['first_name'];
            $lastName = $params['last_name'];
            $company = $params['company_name'];
            $home = $params['home_number'];
            $address1 = $params['address_line_1'];
            $address2 = $params['address_line_2'];
            $city = $params['city_name'];
            $state = $params['state_name'];
            $country = $params['country_code'];
            $zip = $params['zip_code'];
        }

        if (empty($id)) {
            $id = "urn:uuid:E8553C09F4xcvxCCC53F481214230867087";
        }

        $update_date = date("Y-m-d") . 'T' . date("H:i:s") . '+01:00';
        $xml_string = "<entry xmlns='http://www.w3.org/2005/Atom'></entry>";
        $xml_object = simplexml_load_string($xml_string);
        $title_node = $xml_object->addChild("title", htmlspecialchars(("TitleNode"), ENT_QUOTES, 'UTF-8'));
        $updated_node = $xml_object->addChild("updated", htmlspecialchars(($update_date), ENT_QUOTES, 'UTF-8'));
        $author_node = $xml_object->addChild("author");
        $author_name = $author_node->addChild("name", ("Contact"));
        $id_node = $xml_object->addChild("id", htmlspecialchars(($id), ENT_QUOTES, 'UTF-8'));
        $summary_node = $xml_object->addChild("summary", htmlspecialchars((""), ENT_QUOTES, 'UTF-8'));
        $summary_node->addAttribute("type", "text");
        $content_node = $xml_object->addChild("content");
        $content_node->addAttribute("type", "application/vnd.ctct+xml");
        $contact_node = $content_node->addChild("Contact", htmlspecialchars((""), ENT_QUOTES, 'UTF-8'));
        $contact_node->addAttribute("xmlns", "http://ws.constantcontact.com/ns/1.0/");
        $email_node = $contact_node->addChild("EmailAddress", htmlspecialchars($emailAddress, ENT_QUOTES, 'UTF-8'));
        $fname_node = $contact_node->addChild("FirstName", urldecode(htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8')));
        $lname_node = $contact_node->addChild("LastName", urldecode(htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8')));

        if ($params['status'] == 'Do Not Mail') {
            $this->actionBy = 'ACTION_BY_CONTACT';
        }

        $optin_node = $contact_node->addChild("OptInSource", htmlspecialchars($this->actionBy));

        if (is_object($params)) {
            if (get_class($params) != 'FreeLunchLabs_ConstantContact_Model_Newsletter_Subscriber') {
                $cname_node = $contact_node->addChild("CompanyName", urldecode(htmlspecialchars($company, ENT_QUOTES, 'UTF-8')));
                $hn_node = $contact_node->addChild("HomePhone", htmlspecialchars($home, ENT_QUOTES, 'UTF-8'));
                $ad1_node = $contact_node->addChild("Addr1", htmlspecialchars($address1, ENT_QUOTES, 'UTF-8'));
                $ad2_node = $contact_node->addChild("Addr2", htmlspecialchars($address2, ENT_QUOTES, 'UTF-8'));
                $city_node = $contact_node->addChild("City", htmlspecialchars($city, ENT_QUOTES, 'UTF-8'));
                $state_node = $contact_node->addChild("StateName", htmlspecialchars($state, ENT_QUOTES, 'UTF-8'));
                $ctry_node = $contact_node->addChild("CountryCode", htmlspecialchars($country, ENT_QUOTES, 'UTF-8'));
                $zip_node = $contact_node->addChild("PostalCode", htmlspecialchars($zip, ENT_QUOTES, 'UTF-8'));
            }
        }

        $note_node = $contact_node->addChild("Note", htmlspecialchars('', ENT_QUOTES, 'UTF-8'));
        $emailtype_node = $contact_node->addChild("EmailType", htmlspecialchars('', ENT_QUOTES, 'UTF-8'));

        if (!empty($params['custom_fields'])) {
            foreach ($params['custom_fields'] as $k => $v) {
                $contact_node->addChild("CustomField" . $k, htmlspecialchars(($v), ENT_QUOTES, 'UTF-8'));
            }
        }

        $contactlists_node = $contact_node->addChild("ContactLists");
        if ($delete) {
            foreach ($params['lists'] as $tmp) {
                $contactlist_node = $contactlists_node->addChild("ContactList");
                $contactlist_node->addAttribute("id", $tmp);
            }
        } else {
            //set list to add to
            if (isset($params['store_id'])) {
                $store_id = $params['store_id'];
            } else {
                $store_id = NULL;
            }
            $listId = urldecode($this->_getListIdByStoreId($store_id));
            $contactlist_node = $contactlists_node->addChild("ContactList");
            $contactlist_node->addAttribute("id", $listId);
        }

        $entry = $xml_object->asXML();
        return $entry;
    }

    public function getSubscribers($email = '', $page = '', $newStatus = '') {
        $contacts = array();
        $contacts['items'] = array();

        if ($page != '') {
            $call = $this->_apiPath . $page;
        } elseif ($email == '') {
            $call = $this->_apiPath . '/contacts';
        } else {
            $call = $this->_apiPath . '/contacts?email=' . $email;
        }

        $return = $this->doServerCall($call);
        $parsedReturn = simplexml_load_string($return);

        foreach ($parsedReturn->link as $item) {
            $attributes = $item->Attributes();
            if ($attributes['rel'] == 'next') {
                if (!empty($attributes['rel'])) {
                    $next = explode($this->_login, $attributes['href']);
                    $contacts['50more'] = $next[1];
                } else {
                    $contacts['50more'] = "";
                }
            } else {
                $contacts['50more'] = "";
            }
        }

        foreach ($parsedReturn->entry as $item) {
            $tmp = array();
            $tmp['id'] = (string) $item->id;
            $tmp['title'] = (string) $item->title;
            $tmp['status'] = (string) $item->content->Contact->Status;
            $tmp['EmailAddress'] = (string) $item->content->Contact->EmailAddress;
            $tmp['EmailType'] = (string) $item->content->Contact->EmailType;
            $tmp['Name'] = (string) $item->content->Contact->Name;
            $contacts['items'][] = $tmp;
        }

        return $contacts;
    }

    public function getSubscriberDetails($email) {
        $contact = $this->getSubscribers($email);

        $fullContact = array();
        $call = str_replace('http://', 'https://', $contact['items'][0]['id']);
        // Convert id URI to BASIC compliant
        $return = $this->doServerCall($call);
        $parsedReturn = simplexml_load_string($return);
        $fullContact['id'] = $parsedReturn->id;
        $fullContact['email_address'] = $parsedReturn->content->Contact->EmailAddress;
        $fullContact['first_name'] = $parsedReturn->content->Contact->FirstName;
        $fullContact['last_name'] = $parsedReturn->content->Contact->LastName;
        $fullContact['middle_name'] = $parsedReturn->content->Contact->MiddleName;
        $fullContact['company_name'] = $parsedReturn->content->Contact->CompanyName;
        $fullContact['job_title'] = $parsedReturn->content->Contact->JobTitle;
        $fullContact['home_number'] = $parsedReturn->content->Contact->HomePhone;
        $fullContact['work_number'] = $parsedReturn->content->Contact->WorkPhone;
        $fullContact['address_line_1'] = $parsedReturn->content->Contact->Addr1;
        $fullContact['address_line_2'] = $parsedReturn->content->Contact->Addr2;
        $fullContact['address_line_3'] = $parsedReturn->content->Contact->Addr3;
        $fullContact['city_name'] = (string) $parsedReturn->content->Contact->City;
        $fullContact['state_code'] = (string) $parsedReturn->content->Contact->StateCode;
        $fullContact['state_name'] = (string) $parsedReturn->content->Contact->StateName;
        $fullContact['country_code'] = $parsedReturn->content->Contact->CountryCode;
        $fullContact['zip_code'] = $parsedReturn->content->Contact->PostalCode;
        $fullContact['sub_zip_code'] = $parsedReturn->content->Contact->SubPostalCode;
        $fullContact['custom_field_1'] = $parsedReturn->content->Contact->CustomField1;
        $fullContact['custom_field_2'] = $parsedReturn->content->Contact->CustomField2;
        $fullContact['custom_field_3'] = $parsedReturn->content->Contact->CustomField3;
        $fullContact['custom_field_4'] = $parsedReturn->content->Contact->CustomField4;
        $fullContact['custom_field_5'] = $parsedReturn->content->Contact->CustomField5;
        $fullContact['custom_field_6'] = $parsedReturn->content->Contact->CustomField6;
        $fullContact['custom_field_7'] = $parsedReturn->content->Contact->CustomField7;
        $fullContact['custom_field_8'] = $parsedReturn->content->Contact->CustomField8;
        $fullContact['custom_field_9'] = $parsedReturn->content->Contact->CustomField9;
        $fullContact['custom_field_10'] = $parsedReturn->content->Contact->CustomField10;
        $fullContact['custom_field_11'] = $parsedReturn->content->Contact->CustomField11;
        $fullContact['custom_field_12'] = $parsedReturn->content->Contact->CustomField12;
        $fullContact['custom_field_13'] = $parsedReturn->content->Contact->CustomField13;
        $fullContact['custom_field_14'] = $parsedReturn->content->Contact->CustomField14;
        $fullContact['custom_field_15'] = $parsedReturn->content->Contact->CustomField15;
        $fullContact['notes'] = $parsedReturn->content->Contact->Note;
        $fullContact['mail_type'] = $parsedReturn->content->Contact->EmailType;
        $fullContact['status'] = $parsedReturn->content->Contact->Status;
        $fullContact['lists'] = array();

        if ($parsedReturn->content->Contact->ContactLists->ContactList) {
            foreach ($parsedReturn->content->Contact->ContactLists->ContactList as $item) {
                $fullContact['lists'][] = trim((string) $item->Attributes());
            }
        }

        return $fullContact;
    }

}