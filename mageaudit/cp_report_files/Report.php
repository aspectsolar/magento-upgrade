<?php

// Set up an instance of Mage for us to play with
require_once("../app/Mage.php");
Mage::app(0);

if(isset($cpReportFolder))
{
	require_once("./". $cpReportFolder ."/Audit.php");
	require_once("./". $cpReportFolder ."/Magento.php");
	require_once("./". $cpReportFolder ."/Mysql.php");
	require_once("./". $cpReportFolder ."/Php.php");
}
else
{
	echo "Files did not download, trying changing the permissions on the folder to 777";
	die();
}


class Report
{
	private $_auditTypes;
	private $_audit;
	private $_performanceGrade;
	private $_upgradeGrade;
	
	function __construct()
	{
		// The types of audits to run, and the order to run them in
		$this->_auditTypes = array(
										  Php
										, Magento
										, Mysql
									);
		
		// All the audits get put in here
		$this->_audit = array();
		
		$this->_performanceGrade	= array(0, 0);
		$this->_upgradeGrade		= array(0, 0);
	}
	
	// Get all the different audits and mash them together
	function buildReport()
	{
		if(empty($this->_audit))
		{
			$uReports = 0;
			$pReports = 0;
			
			foreach($this->_auditTypes as $class)
			{
				$audit = $this->getNewAudit($class);
				$this->_audit = array_merge_recursive($this->_audit, $audit->getAuditData());
				
				// Only count the reports if they're viable
				$p = $audit->getPerformanceGrade();
				if($p !== false)
				{
					$this->_performanceGrade[0] += $p['score'];
					$this->_performanceGrade[1] += $p['total'];
					$pReports++;
				}
				
				$u = $audit->getUpgradabilityGrade();
				if($u !== false)
				{
					$this->_upgradeGrade[0] += $u['score'];
					$this->_upgradeGrade[1] += $u['total'];
					$uReports++;
				}
			}
			
			$this->_performanceGrade = $this->_performanceGrade[0]/$this->_performanceGrade[1];
			$this->_upgradeGrade = $this->_upgradeGrade[0]/$this->_upgradeGrade[1];
		}
		// SecretData already exists as an array
		// $this->_audit["SecretData"] = array();
		
		$this->_audit["SecretData"]["Grades"] = array("Performance" => $this->_performanceGrade, "Upgradability" => $this->_upgradeGrade);
		return $this->_audit;
	}
	
	// Factory function just for function chaining, hooray!
	function getNewAudit($class)
	{
		return new $class;
	}
}

?>
