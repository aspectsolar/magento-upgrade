<?php

/* So much TODO, so little time!
 * 
 * TODO: Verify cache backend functionality (No idea how to do this)
 * 			TODO: Detect XML issues (grep log files?)
 * 		TODO: Verify DB schema (DB Repair tool)
 */

class Magento extends Audit
{
	protected $_version;
	protected $_modules;
	protected $_magentoLoaded;
	protected $_diffs;

	// Store info from diff so we don't have to run it twice
	protected $_diffBuffer;

	// Most recent release of Magento. TODO: Find a way to automate this.
	protected $_mostCurrentVersion;

	protected $_isEnterprise;

	// Used to make sure we don't overwrite something on the user's server on accident.
	protected $_cleanMagentoFolder;

	function __construct()
	{
		parent::__construct();
		
		$this->_magentoLoaded = false;
		$this->_cleanMagentoFolder = "clean-legacy";
		$x = 0;
		
		$this->_diffBuffer = "";
		
		$this->_isEnterprise = ( Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') ) ? true : false;


		$ch = curl_init('http://www.customerparadigm.com/code-audit/community.txt');
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     	$community = curl_exec($ch);
     	curl_close($ch);


		$ch = curl_init('http://www.customerparadigm.com/code-audit/enterprise.txt');
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     	$enterpise = curl_exec($ch);
     	curl_close($ch);

		

		if ( $this->_isEnterprise )
			$this->_mostCurrentVersion = $enterpise;
		else
			$this->_mostCurrentVersion = $community;
		
		while(is_dir("./" . $this->_cleanMagentoFolder))
		{
			$this->_cleanMagentoFolder = "clean-legacy" . $x;
			$x++;
		}
		
		// Used to check different parts of the file structure in getModifications($type) 
		$this->_diffs = array(
								  "core" => "../app/code/core {$this->_cleanMagentoFolder}/magento/app/code/core"
								, "base" => "../app/design/frontend/base/default {$this->_cleanMagentoFolder}/magento/app/design/frontend/base/default "
								, "default" => "../app/design/frontend/default/default {$this->_cleanMagentoFolder}/magento/app/design/frontend/default/default "
							 );
	}

	public function getAuditData()
	{

		// Version
		$this->addData("Magento", "Version", $this->getVersion());
		
		//echo "Version<br>";

		// Session handling
		$this->addData("Magento", "Session Handling", $this->getSessionHandling());
		
		//echo "Session<br>";

		// Cache backend
		$this->addData("Magento", "Cache Backend", $this->getCacheBackend());
		
		//echo "Cache<br>";

		// Get cache types and see if they're enabled/disabled
		$this->addData("Magento", "Cache Types", $this->getCacheStatus());
		
		//echo "Cache types<br>";

		// Check extensions installed and list them
		$this->addData("Magento", "Modules", $this->getModules());

		//echo "Modules<br>";

		// Check for overridden core files
		$this->addData("Magento", "Core Overrides", $this->getCoreOverrides());
		
		//echo "Core Overrides<br>";

		// Check for modified core files
		$this->addData("Magento", "Core Modifications", $this->getModifications("core"));
		
		//echo "Core Mods<br>";

		// Check for modified base files
		$this->addData("Magento", "Base Modifications", $this->getModifications("base"));

		//echo "Base Mod<br>";
		
		// Check for modified default files
		$this->addData("Magento", "Default Modifications", $this->getModifications("default"));

		//echo "Default Mod<br>";
		
		// Check for missing files
		$this->addData("Magento", "Missing Files", $this->getNewFiles("deleted"));
		
		//echo "Missing Files<br>";

		// Check for added files
		$this->addData("Magento", "Added Files", $this->getNewFiles("new"));

		//echo "Added Files<br>";
			
		// Return total products & how many of those are saleable
		$this->addData("Magento", "Products", $this->getProductInfo());
		
		//echo "Total Products<br>";

		// Get stats on the size of the store
		$this->addData("SecretData", "Sales Data", $this->getSalesData());

		//echo "Sales Data<br>";
		
		// Get customer count
		$this->addData("SecretData", "Customers", $this->getCustomerCount());

		//echo "Customer Count<br>";
		
		// Clean up clean-legacy folder
		$this->unloadCleanMagento();
		
		return $this->_data;
	}
	
	
	// Just like school! Adds up performance-type things and grades them.
	public function getPerformanceGrade()
	{
		$this->_sectionName = "Magento";
		
		$toScore = array(
							  "Session Handling" => NULL
							, "Cache Backend" => array(self::STATUS_ERROR => 1, self::STATUS_INFO => 2, self::STATUS_WARNING => 2, self::STATUS_SUCCESS => 4)
							, "Cache Types" => NULL
						);
		
		return $this->calculateScore($toScore);
	}
	
	
	// Scores upgrade-type things and makes a grade from it
	public function getUpgradabilityGrade()
	{
		$this->_sectionName = "Magento";
		
		
		$toScore = array(
							  "Core Overrides" => NULL
							, "Base Modifications" => array(self::STATUS_ERROR => 2, self::STATUS_WARNING => 3, self::STATUS_SUCCESS => 3)
							, "Core Modifications" => array(self::STATUS_ERROR => 1, self::STATUS_WARNING => 2, self::STATUS_SUCCESS => 3)
							, "Default Modifications" => array(self::STATUS_ERROR => 2, self::STATUS_WARNING => 3, self::STATUS_SUCCESS => 3)
							, "Missing Files" => NULL
							, "Added Files" => NULL
						);

		return $this->calculateScore($toScore);

		

		return false;
	}
	
	
	// What version of Magento is running?
	protected function getVersion()
	{
		$details = NULL;
		$status	 = self::STATUS_INFO;
		
		if (!isset($this->_version))
		{
		    $this->_version = Mage::getVersion();
		}
		
		$isCurrent = true;
		$x = 0;
		$currentVersion = explode(".", $this->_mostCurrentVersion);
		
		foreach(explode(".", $this->_version) as $v)
		{
			// A newer version exists, according to our script
			if($v < $currentVersion[$x])
			{
				$status = self::STATUS_ERROR;
				$isCurrent = false;
				break;
			}
			$x++;
		}
		
		if(!$isCurrent)
		{
			$details = array("The newest version of Magento is ". $this->_mostCurrentVersion .". You are running ". $this->_version .".");
		}
		
		return $this->setValue($this->_version, $details, $status);
	}
	
	// How does Magento cache?
	protected function getSessionHandling()
	{
		$value = (String)Mage::getConfig()->getNode("global/session_save");
		
		// If not memcached == error
		if($value != "memcached")
		{
			$status = self::STATUS_ERROR;
			$details = array("Using memcached will significantly speed up your store.");
		}
		else
		{
			$status = self::STATUS_SUCCESS;
			$details = NULL;
		}
		
		return $this->setValue($value, $details, $status);
	}
	
	// Specifics on the caching
	protected function getCacheBackend()
	{
		// Lots of code nabbed from https://github.com/colinmollenhour/magento-cache-benchmark/blob/master/cache-benchmark.php
		// Thanks Colin Mollenhour!
		
		// APC or Memcached or errror
		$backend = (string) Mage::getConfig()->getNode('global/cache/backend');
		$realBackend = Mage::app()->getCache()->getBackend();
   		$slowBackend = (string) Mage::getConfig()->getNode('global/cache/slow_backend');

		$backendClass = get_class($realBackend);
		if ($backendClass !== $backend)
		{
			if($backend === '')
			{
				$backend = "No backend explicitly specified. Using \"{$backendClass}\" by default.";
			}
			else
			{
				$backend = "Using {$backend} (Class: {$backendClass}).";	
			}
		}

		if ($realBackend instanceof Zend_Cache_Backend_TwoLevels && '' === $slowBackend)
		{
			$slowBackend = 'Zend_Cache_Backend_File';
		}
		
		if ('' === $slowBackend)
		{
			$value = "{$backend}";
		}
		else
		{
			$value = "{$backend} Also using {$slowBackend}.";
		}
		
		return $this->setValue( $value );
	}

	// Which caches are enabled/disabled
	protected function getCacheStatus()
	{
		// Have to manually clear the Varien_Cache_Core for the cache options to show up.
		// "Wtf?" ¯\_(ツ)_/¯
		Mage::app()->getCache()->clean();
		$useCache = Mage::app()->useCache();
		
		$disabledCount	= 0;
		$enabledCount	= 0;
		$details		= array();
		
		// Human readable cache types for building detail query
		$hrCacheTypes = array( 
								"block_html" => "Blocks HTML output"
								, "collections" => "Collections data"
								, "config" => "Configuration"
								, "config_api" => "Web Services Configuration"
								, "config_api2" => "Web Services Configuration (2)"
								, "eav" => "EAV types and attributes"
								, "layout" => "Layouts"
								, "translate" => "Translations"
							 );
		
		foreach($useCache as $key => $value)
		{
			if(isset($hrCacheTypes[$key]))
			{
				$hrType = $hrCacheTypes[$key];
			}
			else
			{
				$hrType = $key;
			}
			
			if($value == 0)
			{
				$details[] = "{$hrType} -- DISABLED";
				$disabledCount++;
			}
			else
			{
				$details[] = "{$hrType} -- enabled";
				$enabledCount++;
			}
		}
		
		// If more than half the available caches are disabled, we might have a problem.
		$status = ($disabledCount > count($useCache)/2) ? self::STATUS_ERROR : self::STATUS_SUCCESS;
		
		return $this->setValue( "{$enabledCount} cache types enabled, {$disabledCount} cache types disabled.", $details, $status );
	}

	// Check which Magento modules are installed
	protected function getModules()
	{
		$modules = $this->_modules = (array)Mage::getConfig()->getNode('modules')->children();
		
		// Count of extensions
		$count = 0;
		$activeCount = 0;
		$count3rd = 0;
		
		$modNames = array_keys($modules);
		
		foreach($modNames as $module)
		{
			

			// don't display Mage modules
			if(!preg_match('/^Mage/', $module) && !preg_match('/^Phoenix_Moneybookers/', $module) && !preg_match('/^Enterprise/', $module))
			{
				$count3rd++;

				if($modules[$module]->is('active'))
				{
					$activeCount++;
					$modNames[array_search($module, $modNames)] .= " is active";
				}
				else
				{
					$modNames[array_search($module, $modNames)] .= " is inactive";
				}
				
			}else{

				$count++;

				$modNames[array_search($module, $modNames)] = "";
			}

		}
		
		return $this->setValue( "There are " . $count . " modules installed, " . $count3rd . " of which are 3rd party modules. {$activeCount}/{$count} are active.", $modNames );
	}

	// Check Magento modules for overridden core functionality
	protected function getCoreOverrides()
	{
		$local_overrides = array( '../app/code/local/Mage', "../app/code/community/Mage");
		$overrides = 0;
		$status = self::STATUS_SUCCESS;
		$details = array();
		$value = "";
		
		foreach($local_overrides as $dir)
		{
			$contents = $this->dirToArray($dir);

			//echo "<pre>";print_r($contents);

			$count = 0;
			foreach($contents as $key => $vmagic) {  // go through overrides
				$details[$count] = $dir."/".$key;
				$count++;
			    $overrides++;
			}
			
			if ($count > 0){
				$value .= 'Override of ' . $count . ' core files found in: <strong><em>' . $dir . '</strong></em> <br>';
			}

		
		}



		// Ten is a lot of overrides(?) let's error
		if($overrides >= 10)
		{
			$status = self::STATUS_ERROR;
		}
		elseif($overrides >= 1)
		{
			$status = self::STATUS_WARNING;
		}


		return $this->setValue($value, $details, $status);
	}

	// throws entire directory structure into an array
	protected function dirToArray($dir) {
	  
	   $result = array();

	   $cdir = scandir($dir);
	   foreach ($cdir as $key => $value)
	   {
	      if (!in_array($value,array(".","..")))
	      {
	         if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
	         {
	            $result[$value] = $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value);
	         }
	         else
	         {
	            $result[] = $value;
	         }
	      }
	   }
	  
	   return $result;
	}

	//   pass two file names
	//   returns TRUE if files are the same, FALSE otherwise
	protected function files_identical($fn1, $fn2) {

    	$check = 5;

	    if(filetype($fn1) !== filetype($fn2)){
	    	$check = 1;
	    }
	        
	    if(filesize($fn1) !== filesize($fn2)){
	    	$check = 2;
	    }

	    if(md5_file($fn1) !== md5_file($fn2)){
	    	$check = 3;
	    }

	    if (!$fn1){
	    	$check = 4;
	    }

	    if (!$fn2){
	    	$check = 5;
	    }

	    return $check;
	}

	protected function loadCleanMagento()
	{
		if(!$this->_magentoLoaded)
		{
			

			// Create Directory
			
			mkdir($this->_cleanMagentoFolder, 0776);

			$rzip = "http://www.customerparadigm.com/code-audit/versions/".$this->_version.".zip";

			$source = rawurldecode($rzip);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $source);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSLVERSION,3);
			$data = curl_exec ($ch);
			$error = curl_error($ch); 
			curl_close ($ch);

			$destination = "./".$this->_cleanMagentoFolder."/".rawurldecode($this->_version.".zip");
			$file = fopen($destination, "w+");
			fputs($file, $data);
			fclose($file);

			$zip = new ZipArchive;
			if ($zip->open('./'.$this->_cleanMagentoFolder.'/'.$this->_version.".zip") === TRUE) {
			  $zip->extractTo("./".$this->_cleanMagentoFolder);
			  $zip->close();
			  //echo 'ok';
			} 

			//echo "extracted: ".$destination; die();

			//$this->cpsystem("mkdir ". $this->_cleanMagentoFolder);
			//$command = "wget -q -P ./". $this->_cleanMagentoFolder ."/ http://www.customerparadigm.com/code-audit/versions/{$this->_version}.gz && tar zxf ./". $this->_cleanMagentoFolder ."/{$this->_version}.gz -C ./{$this->_cleanMagentoFolder}";
			//$this->cpsystem($command);
			
			$this->_magentoLoaded = true;
		}
	}

	protected function unloadCleanMagento()
	{
		$this->Delete($this->_cleanMagentoFolder);	
		$this->_magentoLoaded = false;
	}

	protected function Delete($path)
	{
	    if (is_dir($path) === true)
	    {
	        $files = array_diff(scandir($path), array('.', '..'));

	        foreach ($files as $file)
	        {
	            unlink(realpath($path) . '/' . $file);
	        }

	        return rmdir($path);
	    }

	    else if (is_file($path) === true)
	    {
	        return unlink($path);
	    }

	    return false;
	}

	protected function compareFiles($sourceDir)
	{

	   $result = array();
	   $final = array();
	   $count = 0;
	   $missingsource = 0;
	   $missingdest = 0;

		try{	 
			$directories = new RecursiveIteratorIterator(
			    new ParentIterator(new RecursiveDirectoryIterator($sourceDir)), 
			    RecursiveIteratorIterator::SELF_FIRST);

			foreach ($directories as $directory) {
			    //echo $directory."<br></pre>";
			    $allfiles = scandir($directory);
			    for ($i=0;$i < count($allfiles); $i++){
			    	if (!is_dir( $directory."/".$allfiles[$i])){
			    		$result[] = $directory."/".$allfiles[$i];
			    	}
			    }
			   
			    
			}
			
			foreach($result as $dir){
				$orig = $dir;
				$check = str_replace('clean-legacy','..',$dir);
				
				$type = $this->files_identical($orig, $check);
				if ($type == 1 || $type == 2 || $type == 3){
					if (strpos($check, "sql") === false){
						$final[$count] =  '<span style="color:#ff0000">MOD</span> - '.$check;
						$count++;
					}
				}elseif ($type == 4){
					$missingsource++;
				}elseif ($type == 5){
					$final[$count] =  '<span style="color:#ff0000">NEW</span> - '.$check;
				}

				
			}
			 $count++;
			 $final[$count] = '<br><br>Site Missing '.$missingsource.' files found in clean copy of Magento';
			
		}catch(Exception $e) {
		   //log exception or process silently
		   //just for test
		   //echo $e;
	 	}
		return $final;
	}

	
	protected function getModifications($type)
	{
		$this->loadCleanMagento();
		
		//mount directorys into arrays
		if ($type == "core"){
			$dir = "../app/code/core";
			$org = "clean-legacy/app/code/core";
		}elseif ($type == "base"){
			$dir = "../app/design/frontend/base/default";
			$org = "clean-legacy/app/design/frontend/base/default";
		}else{
			$dir = "../app/design/frontend/default/default";
			$org = "clean-legacy/app/design/frontend/default/default";
		}

		//$livedir = $this->dirToArray($dir);

		//$originaldir = $this->dirToArray($org);

		// check if there are any missing files
		//$result = array_diff($livedir, $originaldir);

		//compare source and dest directory recursively
		$details = $this->compareFiles($org);

		$mods = count($details);
		//echo "<pre>";print_r($list);

		//$command	= $this->_diffs[$type];
		//$buffer = $this->cpsystem($command);
		//$this->_diffBuffer .= $buffer;
		//$details	= $this->_formatDiff($buffer);
		
		// Explode returns an array with the empty string if \n isn't found.
		//if($details[0] == "") $details = NULL;
		//$mods		= count($details); 
		
		// Proper pluralization
		if($mods === 1)
		{
			$value = "Found " . $mods . " modification to {$type} files.";
		}
		else
		{
			$value = "Found " . $mods . " modifications to {$type} files.";
		}
		
		// Overrides are preferable to direct modifications.
		if($mods >= 5)
		{
			$status = self::STATUS_ERROR;
		}
		elseif($mods >= 1)
		{
			$status = self::STATUS_WARNING;
		}
		else
		{
			$status = self::STATUS_SUCCESS;
		}
		
		return $this->setValue($value, $details, $status);
	}
	
	protected function getNewFiles($type)
	{
		$details = array();
		
		$buffer = $this->_formatDiff($this->_diffBuffer);
		
		$new = 0;
		if($type === "deleted")
		{
			$pattern = "/Deleted\ File/";
		}
		else
		{
			$pattern = "/New\ File/";
		}
		
		foreach($buffer as $l)
		{
			if(preg_match($pattern, $l))
			{
				$details[] = $l;
				$new++;
			}
		}
		
		if($new === 1)
		{
			$value = "Found ". $new ." ". $type ." file.";
		}
		else
		{
			$value = "Found ". $new ." ". $type ." files.";
		}
		
		if($details[0] === NULL)
		{
			$details = NULL;
		}
		
		return $this->setValue($value, $details);
	}
	
	// Return total product count and how many of those products are sellable
	protected function getProductInfo()
	{ 
		$countSql = Mage::getModel('catalog/product')->getCollection()->getSelectCountSql();
#		$total = Mage::getModel('catalog/product')->getCollection()->count();
		$total = current(current($this->db()->fetchAll($countSql)));
		
		// Pluralize.
		$value = "";
	
		if($total === 1)
		{
			$value .= "Your store has ". $total ." product";
		}
		else
		{
			$value .= "Your store has ". $total ." products.";
		}
		
		return $this->setValue($value);
	}
	
	protected function getSalesData()
	{
        $collection = Mage::getResourceModel('reports/order_collection')->calculateSales(false);
		
		$collection->load();
        $sales = $collection->getFirstItem();
	
        // Here is where we get the first sale from Magento
 
		$totalSales = current(current($this->db()->fetchAll("SELECT count(*) FROM " . $this->_resource->getTableName('sales/order'))));
		// $totalSales = 0;
		
		$data = array("First Sale" => $firstsale[0], "Lifetime Sales" => $sales->getLifetime(), "Average Order Size" => $sales->getAverage(), "Total Sales" => $totalSales);
		return $data;
	}

	protected function getCustomerCount()
	{
		$this->db();
		$customerCount = current(current($this->db()->fetchAll("SELECT count(*) FROM " . Mage::getResourceModel("customer/customer")->getEntityTable())));
		return $customerCount;
	}

	protected function _formatDiff($buffer)
	{
		$buffer = trim($buffer);
		$buffer = str_replace(" ../app/code/core/", " legacy/core/", $buffer);
		$buffer = str_replace(" clean-legacy/app/code/core/", " clean-legacy/core/", $buffer);
		$buffer = str_replace(" ../app/design/frontend/base/default/", " legacy/base/", $buffer);
		$buffer = str_replace(" clean-legacy/app/design/frontend/base/default/", " clean-legacy/base/", $buffer);
		$buffer = str_replace(" ../app/design/frontend/default/default/", " legacy/default/default/", $buffer);
		$buffer = str_replace(" clean-legacy/app/design/frontend/default/default/", " clean-legacy/default/default/", $buffer);    
		$buffer = str_replace("Only in legacy/", "<strong>New File</strong> ", $buffer);
		$buffer = str_replace("Files", "<strong>Modification</strong>", $buffer);
		$buffer = preg_replace("/\ and .* differ/", "", $buffer);
		$buffer = str_replace("Only in clean-legacy/", "<strong>Deleted File</strong> ", $buffer);
		return explode("\n", $buffer);
	}
}
?>
