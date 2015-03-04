<?php

/* So much TODO, so little time!
 * 
 * TODO: 
 */

class Mysql extends Audit
{
    protected $_mysqlData = array(
    "innodb_buffer_pool_size", 
    "innodb_thread_concurrency",
    "thread_cache_size", 
    "log_slow_queries", 
    "max_connections", 
    "sort_buffer_size", 
    "key_buffer_size", 
    "innodb_additional_mem_pool_size", 
    "version", 
    "version_compile_os", 
    "version_compile_machine",
    "dbsize" 
    );            
    protected $_varData = array();
    
    public function getAuditData()
    {
        foreach($this->_mysqlData as $var)
        {
            $value = false;
            switch($var)
            {
                case "dbsize":
                    // this needs to be rewritten
                    //$value = $this->_checkDbSize();
                break;
                default:  
               
                    $sql              = "SHOW VARIABLES WHERE Variable_name = '{$var}'";
                    $db_variable_data = current($this->db()->fetchAll($sql));
        	        $db_variable_data_value = $db_variable_data['Value']; 
        	        $value = $db_variable_data_value;
	           break;
            }
            $this->_varData[$var] = $value;
            $this->addMysqlData($var, $value);
        }
	   
		return $this->_data;
    }
	
	
	// Just like school! Adds up performance-type things and grades them.
	public function getPerformanceGrade()
	{
		$this->_sectionName = "MySQL";
		
		$toScore = array(
						      "innodb_buffer_pool_size" => array(self::STATUS_ERROR => 0, self::STATUS_SUCCESS => 4)
						    , "innodb_thread_concurrency" => array(self::STATUS_ERROR => 0, self::STATUS_SUCCESS => 2)
						    , "thread_cache_size" => array(self::STATUS_ERROR => 0, self::STATUS_SUCCESS => 2)
						    , "log_slow_queries" => array(self::STATUS_ERROR => 0, self::STATUS_SUCCESS => 1)
						    , "max_connections" => array(self::STATUS_ERROR => 0, self::STATUS_SUCCESS => 3)
						    , "sort_buffer_size" => array(self::STATUS_ERROR => 0, self::STATUS_SUCCESS => 1)
						    , "key_buffer_size" => array(self::STATUS_ERROR => 0, self::STATUS_SUCCESS => 1)
						    , "innodb_additional_mem_pool_size" => array(self::STATUS_ERROR => 0, self::STATUS_SUCCESS => 2)
	    				);
	    
		return $this->calculateScore($toScore);
	}
	
	
	// Mysql doesn't really have much to do with upgradability
	public function getUpgradabilityGrade()
	{
		return false;
	}
	
    protected function _checkDbSize()
    {
        $sql 	       = "SHOW TABLE STATUS";
        $size_in_bytes = 0;
        foreach($this->db()->fetchAll($sql) as $row)
        {
          $size_in_bytes += $row[ "Data_length" ] + $row[ "Index_length" ];
        }
        return $size_in_bytes;
    }
    public function addMysqlData($key, $value)
    {
	   switch ($key)
	   {
		    case "innodb_buffer_pool_size":
    			$poolM   = floor($value / (1028 * 1028));
    			$dbsizeM = floor($this->_varData['dbsize'] / (1028 * 1028));
                $minSize = (256 <= $poolM);
                $humanValue = $poolM . "M";
                
                $details = "Magento recommends a value of at least 256M. On a dedicated database server, this setting can be 80% of physical RAM. On a server where memory is shared between the database and application, this number should be around 40% physical RAM. Ideally, this value is larger than the size of your database";
                $status  = self::STATUS_ERROR;
                if ($poolM >= $dbsizeM && $minSize)
                {
                    $status = self::STATUS_SUCCESS;
                }
            break;
            case "innodb_thread_concurrency":
                $humanValue = $value;
                $minVal     = 8;
                
                $details = "Magento recommends a value of at least 8. If possible, this value should be set as high as the maximum (16)";
                $status  = self::STATUS_ERROR;
                if ($value >= $minVal)
                {
                    $status = self::STATUS_SUCCESS;
                }
            
            break;
            case "log_slow_queries":
                $humanValue = $value;
                $targetVal  = "OFF";
                
                $details    = "On a production server, this setting should be set to OFF as it is considerable overhead on the database";
                $status     = self::STATUS_ERROR;
                if ($targetVal == $value) $status = self::STATUS_SUCCESS;
            break;
            case "thread_cache_size":
                $humanValue = $value;
                $minValue   = 8;
                
                $details    = "This should be set to a minimum of 8 (the default is 0). On a server with a large amount of memory, it can be set to 32 or 64. (Note, this may be a false positive on Mysql5 installations, as in Mysql 5 a concurrency of 0 is unlimited";
                $status     = self::STATUS_ERROR;
                if ($humanValue >= $minValue) $status = self::STATUS_SUCCESS;
            break;
            case "max_connections":
                $humanValue = $value;
                $minValue   = 100;
                
                $details    = "This should be set to at least 100";
                $status     = self::STATUS_ERROR;
                if ($humanValue >= $minValue) $status = self::STATUS_SUCCESS;
            break;
            case "sort_buffer_size":
                $value = floor($value / (1028 * 1028));
                $humanValue = $value . "M";
                $minValue   = "7";
                
                $details    = "This should be at least 7M";
                $status     = self::STATUS_ERROR;
                if ($value >= $minValue) $status = self::STATUS_SUCCESS;
            break;
            case "key_buffer_size":
                $value      = floor($value / (1028 * 1028));
                $humanValue = $value. "M";
                $minValue   = "200";
                $details    = "We recommend this value be at least 200M for optimal search speed.";
                
                $status     = self::STATUS_ERROR;
                if ($value >= $minValue) $status = self::STATUS_SUCCESS;       
            break;
            case "innodb_additional_mem_pool_size":
                $value      = floor($value / (1028 * 1028));
                $humanValue = $value. "M";
                $minValue   = 15;
                $details    = "We recommend this value be at least 15M for optimal lookup time";
                
                $status     = self::STATUS_ERROR;
                if($value >= $minValue) $status = self::STATUS_SUCCESS;
            break;
            case "version":
                $humanValue = "v. " . $value;
                $details    = NULL;
                $status     = self::STATUS_INFO;
            break;
            case "version_compile_os":
                $humanValue = "OS " . $value;
                $details    = NULL;
                $status     = self::STATUS_INFO;
            break;
            case "version_compile_machine":
                $humanValue = "architecture " . $value;
                $details    = NULL;
                $status     = self::STATUS_INFO;
            break;         
            case "dbsize":
                $humanValue = floor($value / (1028 * 1028)) . "M";
                $details    = NULL;
                $status     = self::STATUS_INFO;
            break;                           
                
	   }
	   
	   if($details !== NULL)
	   {
			$details = array($details);
	   }
	   //$key = str_replace("_", " ", $key);
	$this->addData('MySQL', $key, $this->setValue("Currently set to {$humanValue}", $details, $status));
       
    }
}


?>
