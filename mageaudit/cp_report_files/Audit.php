<?php

putenv("PATH=/usr/bin:". getenv("PATH"));

abstract class Audit
{
	// VARIABLES
	
	protected $_data;
	public $_sectionName;
	protected $_db = false;
	protected $_resource;
	
	// Status related constants
	const STATUS_SUCCESS	= "success";
	const STATUS_ERROR		= "error";
	const STATUS_WARNING	= "warning";
	const STATUS_INFO		= "info";
	
	
	
	
	// CONSTRUCTOR
	
	function __construct()
	{
		// Init the empty data array
		$this->_data = array();
	}
	
	
	
	
	// ABSTRACT METHODS
	
	/****
	 * Evaluate and return an array in this format:
	 * array("sectionName" => array(
	 * 								"key" => array(
	 * 												"value"		=> "xxx",
	 * 												"details"	=> array(for lists) or string
	 * 												"status"	=> "success" or "error" or "info" )));
	 ****/
	//abstract public function getAuditData();
	abstract public function getPerformanceGrade();
	abstract public function getUpgradabilityGrade();



	// CONCRETE METHODS
	
	// Run a shell command
	protected function cpsystem($command)
	{
		ob_start();
		@system($command);
		
	    $buffer = ob_get_contents();
		ob_end_clean();
		
	    return $buffer;
	}
    
	// Hook up with the database if not already, return the Magento db object
    public function db()
    {
        if (!$this->_db):
            $this->_resource = Mage::getSingleton('core/resource');        
            $this->_db = $this->_resource->getConnection('core_read');
        endif;
        return $this->_db;
    }
	
	// Add data to the appropriate assoc. arrays
	protected function addData($sectionName, $key, $value)
	{
		// Set up all the arrays if they're not already there
		if( !isset($this->_data[$sectionName]) )
		{
			$this->_data[$sectionName] = array();
		}
		
		$this->_data[$sectionName][$key] = $value;
	}
	
	// Format values in the expected format
	protected function setValue($value, $details = NULL, $status = self::STATUS_INFO)
	{
		return array(
						"value"		=> $value
					,	"details"	=> $details
					,	"status"	=> $status
					);
	}
	
	protected function calculateScore($toScore)
	{
		$score = 0;
		$totalScore = 0;
		
		foreach($toScore as $key => $value)
		{
			$s = $this->getValueGrade($key, $value);

			// $s will not be an array if it isn't in the data table.
			if(is_array($s))
			{
				$score += $s[0];
				$totalScore += $s[1];
			}
		}
		
		// If no data was returned, $totalScore will be zero
		if($totalScore === 0)
		{
			return 0;
		}
		else
		{
			return array('score' => $score, 'total' => $totalScore);
		}
	}
	
	protected function getValueGrade($value, $weight)
	{
		// Use default weights
		if($weight === NULL)
		{
			$weight = array(self::STATUS_ERROR => 0, self::STATUS_WARNING => 1, self::STATUS_SUCCESS => 2, self::STATUS_INFO => 2);
		}
		
		// CALCULATE!
		if(isset($this->_data[$this->_sectionName][$value]["status"]))
		{
			return array($weight[$this->_data[$this->_sectionName][$value]["status"]], $weight[self::STATUS_SUCCESS]);
		}
		else
		{
			// If it's misconfigured, don't let it affect the score.
			return false;
		}
	}
}
