<?php
/**
 * Shopping Cart Editor
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aiteditablecart
 * @version      2.1.2
 * @license:     sJyHIXeWlUU1XYytqPDfVrCdIMO1jSQ6obarjJbRep
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aiteditablecart_Model_Rewrite_FrontCatalogProductOptionTypeFile extends Mage_Catalog_Model_Product_Option_Type_File
{
    // override parent    
    
    public function validateUserValue($values)
    {
        Mage::getSingleton('checkout/session')->setUseNotice(false);

        $this->setIsValid(true);
        $option = $this->getOption();

        // Set option value from request (Admin/Front reorders)
        if (isset($values[$option->getId()]) && is_array($values[$option->getId()])) {
            if (isset($values[$option->getId()]['order_path'])) {
                $orderFileFullPath = Mage::getBaseDir() . $values[$option->getId()]['order_path'];
            } else {
                $this->setUserValue(null);
                return $this;
            }

            $ok = is_file($orderFileFullPath) && is_readable($orderFileFullPath)
                && isset($values[$option->getId()]['secret_key'])
                && substr(md5(file_get_contents($orderFileFullPath)), 0, 20) == $values[$option->getId()]['secret_key'];

            $this->setUserValue($ok ? $values[$option->getId()] : null);
            return $this;
        } elseif ($this->getProduct()->getSkipCheckRequiredOption()) {
            $this->setUserValue(null);
            return $this;
        }

        /**
         * Upload init
         */
        $upload = new Zend_File_Transfer_Adapter_Http();
        $file = 'options_' . $option->getId() . '_file';
        
// start aitoc code        

        if ($item = Mage::registry('aitoc_cart_options_item'))
        {
            $iOptionItemId = $item->getId();
            
            $file = 'cartoptions_' . $iOptionItemId . '_' . $option->getId() . '_file';
            
            if (!$upload->isUploaded($file))
            {
                $sReqKey = 'aitoc_cart_option_file_value_' . $option->getId();
                
                if ($quoteItemOption = $item->getOptionByCode('option_' . $option->getId()) AND $quoteItemOption->getValue())
                {
                    $aQuoateValue = unserialize($quoteItemOption->getValue());
                    
                    $this->setUserValue($aQuoateValue);
                    
                    return $this;
        #            Mage::register($sReqKey, $aQuoateValue);
                }
            }
        }
// finish aitoc code        

        try {
            $runValidation = $option->getIsRequire() || $upload->isUploaded($file);
            if (!$runValidation) {
                $this->setUserValue(null);
                return $this;
            }

            $fileInfo = $upload->getFileInfo($file);
            $fileInfo = $fileInfo[$file];

        } catch (Exception $e) {
            // when file exceeds the upload_max_filesize, $_FILES is empty
            if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > $this->_getUploadMaxFilesize()) {
                $this->setIsValid(false);
                Mage::throwException(
                    Mage::helper('catalog')->__("The file you uploaded is larger than %s Megabytes allowed by server",
                        $this->_bytesToMbytes($this->_getUploadMaxFilesize())
                    )
                );
            } else {
                $this->setUserValue(null);
                return $this;
            }
        }

        /**
         * Option Validations
         */

        // Image dimensions
        $_dimentions = array();
        if ($option->getImageSizeX() > 0 && $this->_isImage($fileInfo)) {
            $_dimentions['maxwidth'] = $option->getImageSizeX();
        }
        if ($option->getImageSizeY() > 0 && $this->_isImage($fileInfo)) {
            $_dimentions['maxheight'] = $option->getImageSizeY();
        }
        if (count($_dimentions) > 0) {
            $upload->addValidator('ImageSize', false, $_dimentions);
        }

        // File extension
        $_allowed = $this->_parseExtensionsString($option->getFileExtension());
        if ($_allowed !== null) {
            $upload->addValidator('Extension', false, $_allowed);
        } else {
            $_forbidden = $this->_parseExtensionsString($this->getConfigData('forbidden_extensions'));
            if ($_forbidden !== null) {
                $upload->addValidator('ExcludeExtension', false, $_forbidden);
            }
        }

        // Maximum filesize
        $upload->addValidator('FilesSize', false, array('max' => $this->_getUploadMaxFilesize()));

        /**
         * Upload process
         */

        $this->_initFilesystem();

        if ($upload->isUploaded($file) && $upload->isValid($file)) {

            $extension = pathinfo(strtolower($fileInfo['name']), PATHINFO_EXTENSION);

            $fileName = Varien_File_Uploader::getCorrectFileName($fileInfo['name']);
            $dispersion = Varien_File_Uploader::getDispretionPath($fileName);

            $filePath = $dispersion;
            $destination = $this->getQuoteTargetDir() . $filePath;
            $this->_createWriteableDir($destination);
            $upload->setDestination($destination);

            $fileHash = md5(file_get_contents($fileInfo['tmp_name']));
            $filePath .= DS . $fileHash . '.' . $extension;

            $fileFullPath = $this->getQuoteTargetDir() . $filePath;

            $upload->addFilter('Rename', array(
                'target' => $fileFullPath,
                'overwrite' => true
            ));
//            if (!$upload->receive()) {
            if (!$upload->receive($file)) { // aitoc code
                $this->setIsValid(false);
                Mage::throwException(Mage::helper('catalog')->__("File upload failed"));
            }

            $_imageSize = @getimagesize($fileFullPath);
            if (is_array($_imageSize) && count($_imageSize) > 0) {
                $_width = $_imageSize[0];
                $_height = $_imageSize[1];
            } else {
                $_width = 0;
                $_height = 0;
            }

            $this->setUserValue(array(
                'type'          => $fileInfo['type'],
                'title'         => $fileInfo['name'],
                'quote_path'    => $this->getQuoteTargetDir(true) . $filePath,
                'order_path'    => $this->getOrderTargetDir(true) . $filePath,
                'fullpath'      => $fileFullPath,
                'size'          => $fileInfo['size'],
                'width'         => $_width,
                'height'        => $_height,
                'secret_key'    => substr($fileHash, 0, 20)
            ));

        } elseif ($upload->getErrors()) {
            $errors = array();
            foreach ($upload->getErrors() as $errorCode) {
                if ($errorCode == Zend_Validate_File_ExcludeExtension::FALSE_EXTENSION) {
                    $errors[] = Mage::helper('catalog')->__("The file '%s' for '%s' has an invalid extension",
                        $fileInfo['name'],
                        $option->getTitle()
                    );
                } elseif ($errorCode == Zend_Validate_File_Extension::FALSE_EXTENSION) {
                    $errors[] = Mage::helper('catalog')->__("The file '%s' for '%s' has an invalid extension",
                        $fileInfo['name'],
                        $option->getTitle()
                    );
                } elseif ($errorCode == Zend_Validate_File_ImageSize::WIDTH_TOO_BIG
                    || $errorCode == Zend_Validate_File_ImageSize::WIDTH_TOO_BIG)
                {
                    $errors[] = Mage::helper('catalog')->__("Maximum allowed image size for '%s' is %sx%s px.",
                        $option->getTitle(),
                        $option->getImageSizeX(),
                        $option->getImageSizeY()
                    );
                } elseif ($errorCode == Zend_Validate_File_FilesSize::TOO_BIG) {
                    $errors[] = Mage::helper('catalog')->__("The file '%s' you uploaded is larger than %s Megabytes allowed by server",
                        $fileInfo['name'],
                        $this->_bytesToMbytes($this->_getUploadMaxFilesize())
                    );
                }
            }
            if (count($errors) > 0) {
                $this->setIsValid(false);
                Mage::throwException( implode("\n", $errors) );
            }
        } else {
            $this->setIsValid(false);
            Mage::throwException(Mage::helper('catalog')->__('Please specify the product required option(s)'));
        }
#d(' ENNNND');        
        
        return $this;
    }
}