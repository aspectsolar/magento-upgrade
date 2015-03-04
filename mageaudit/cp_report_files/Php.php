<?php

class Php extends Audit
{	
	public function getAuditData()
	{
		// Check PHP version is at least minimum specified by Magento
		$this->addData("PHP", "Version", $this->getPhpVersion());
		
		// Make sure all the required modules are present
		$this->addData("PHP", "Modules", $this->getPhpModules());
		
		// Make sure we're not running in safe_mode
		$this->addData("PHP", "Safe Mode", $this->getSafeMode());
		
		// Check to see if APC is installed and enabled.
		$this->addData("PHP", "APC", $this->getApcEnabled());
		
		// Make sure memory_limit is at minimum needed to run Magento.
		$this->addData("PHP", "Memory Limit", $this->getMemoryLimit());
		
		return $this->_data;
	}
	
	
	// Just like school! Adds up performance-type things and grades them.
	public function getPerformanceGrade()
	{
		$this->_sectionName = "PHP";
		
		$toScore = array(
							  "APC" => array(self::STATUS_ERROR => 0, self::STATUS_WARNING => 1, self::STATUS_SUCCESS => 3)
						);
		
		return $this->calculateScore($toScore);
	}
	
	
	// Scores upgrade-type things and makes a grade from it
	public function getUpgradabilityGrade()
	{	
		return false;
	}
	
	
	
	protected function getPhpVersion()
	{
		// Grab the required PHP version from index.php
		preg_match('/[0-9]\.[0-9]+\.[0-9]+/', $this->cpsystem('grep "version_compare" index.php'), $version);
		
		if(version_compare(phpversion(), $version[0], '<') === true)
		{
			$value	= "PHP version does not meet the minimum requirement for this Magento installation.";
			$status = self::STATUS_ERROR;
		}
		else
		{
			$value	= "PHP version meets minimum requirement for this Magento installation.";
			if(version_compare(phpversion(), "5.2.0", '<') === true)
			{
				$value .= " To upgrade, however, you will need to upgrade to PHP version 5.2 or above.";
			}
			$status = self::STATUS_SUCCESS;
		}
		
		$details	= array("Running PHP version " . phpversion());
		
		return $this->setValue($value, $details, $status);
	}
	
	
	
	protected function getPhpModules()
	{
		$modules = array( 
							  'curl'
							, 'dom'
							, 'gd'
							, 'hash'
							, 'iconv'
							, 'mcrypt'
							, 'pcre'
							, 'pdo'
							, 'pdo_mysql'
							, 'simplexml'
						);
		$details	= array();
		$missing	= 0;
		
		// Innocent until proven guilty
		$status		= self::STATUS_SUCCESS;
		$value		= "You have all the required PHP modules.";

		foreach($modules as $extension)
		{
			if(!extension_loaded($extension))
			{
				$missing++;
				$status		= self::STATUS_ERROR;
				$details[]	= '<strong>You are missing the "'.$extension.'" extension.</strong>';
			}
			else
			{
				$details[]	= 'You have the "'.$extension.'" extension.';
			}
		}
		
		if($missing > 0)
		{
			$value = "You are missing {$missing} PHP modules. See details.";
		}
		
		return $this->setValue($value, $details, $status);
	}
	
	
	
	protected function getSafeMode()
	{
		if(!ini_get('safe_mode'))
		{
			$value	= "safe_mode is off.";
			$status = self::STATUS_SUCCESS;
		}
		else
		{
			$value	= "safe_mode is on.";
			$status = self::STATUS_ERROR;
		}
		
		return $this->setValue($value, NULL, $status);
	}
	
	
	
	protected function getApcEnabled()
	{
		$status = self::STATUS_SUCCESS;
		if(extension_loaded('apc'))
		{
			if(ini_get('apc.enabled'))
			{
				$value = "APC is installed and enabled.";
			}
			else
			{
				$value = "APC is installed, but not enabled.";
				$status = self::STATUS_ERROR;
			}
		}
		else
		{
			$value = "APC is not installed.";
			$status = self::STATUS_ERROR;
		}
		
		return $this->setValue($value, NULL, $status);
	}
	
	protected function getMemoryLimit()
	{
		$status = self::STATUS_INFO;
		
		preg_match('/^[0-9]*/', ini_get('memory_limit'), $m);
		
		if($m[0] < 64)
		{
			$status = self::STATUS_ERROR;
			$value = "Magento recommends a memory limit of at least 64MB. Yours is set to ".ini_get('memory_limit');
		}
		else
		{
			$value = "Your memory limit is set to ".ini_get('memory_limit').", which should be sufficient for most Magento installations.";
		}
		
		return $this->setValue($value, NULL, $status);
	}
}

?>