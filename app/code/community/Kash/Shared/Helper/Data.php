<?php
class Kash_Shared_Helper_Data extends Mage_Core_Helper_Abstract
{

	function checkLicenseOnline($module, $license){
		$thisHost = $_SERVER['SERVER_NAME'];
		$url = 'http://kash.com/license/check.php?module='.urlencode($module).'&license='.urlencode($license).'&clienthost='.urlencode($thisHost);
		$result = @file_get_contents($url, false, null, 0, 2000);
		$okText = "kashOK";
		if (strcasecmp($result, $okText) == 0) {
		    return true;
		}
		return true;
	}
}