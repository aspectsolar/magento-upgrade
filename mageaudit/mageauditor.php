<?php
ini_set("display_errors", true);
ini_set("max_execution_time", 0);
ini_set("memory_limit", "256M");
putenv("PATH=/usr/bin:". getenv("PATH"));

function cpsystem($command)
{
	ob_start();   
	@system($command);
	
    $buffer = ob_get_contents();
	ob_end_clean();
	
    return $buffer;
}


function prepareAndSendPost($post)
{
	foreach($post as $key => $value) { 
		$post[urlencode($key)] = urlencode($value); 
	}


	$ch = curl_init("http://www.customerparadigm.com/code-audit/mageaudit_check.php");
	
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, count($post));
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	$ret = curl_exec($ch);
	$ret = json_decode($ret, true);
	curl_close($ch);
	
	return $ret;
}

function curPageURL() {
	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") { $pageURL .= "s"; }
	
	$pageURL .= "://";
	
	if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80")
	{
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}
	else
	{
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

$verified = false;

cleandir("clean-legacy");
cleandir("cp_report_files");
	
// Form was submitted, send info to CP
if(isset($_POST["name"]))
{
	$post = $_POST;
	$post["status"] = "running";
	$post["site"]	= curPageURL();
	$ret = prepareAndSendPost($post);

	if (!$ret){
		$ret["status"] = "error";
		$ret["message"] = "CP server didn't send a response.";
	}
	
	// Validated input, let's run the code audit
	if($ret["status"] === "success" && isset($ret["file_location"]) && isset($ret["file_name"]))
	{

		
			
			//go into low security mode

			$filename = $ret["file_name"];
			$cpReportFolder = "cp_report_files";
			$rfolder = substr($filename, 0, -4);
			$rzip = $rfolder.".zip";

			// Create Directory
			if (!is_dir("./" . $cpReportFolder)){
				mkdir($cpReportFolder, 0776);
			}
			//$cmd = "wget -q -P ./". $cpReportFolder ."/ ". rawurldecode($ret["file_location"]) . rawurldecode($rzip);
			
			//Download file
			if (function_exists('curl_init')) { 
				$source = rawurldecode($ret["file_location"]) . rawurldecode($rzip);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $source);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSLVERSION,3);
				$data = curl_exec ($ch);
				$error = curl_error($ch); 
				curl_close ($ch);

				$destination = "./".$cpReportFolder."/".rawurldecode($rzip);
				$file = fopen($destination, "w+");
				fputs($file, $data);
				fclose($file);
		

		$zip = new ZipArchive;
		if ($zip->open('./'.$cpReportFolder.'/'.$rzip) === TRUE) {
		  $zip->extractTo("./".$cpReportFolder);
		  $zip->close();
		  //echo 'ok';
		} else {
		  	$status		= "error";
			$message	= "The Customer Paradigm code audit tool cannot work given your system configuration settings. Contact us for further assistance.";
		}

		require_once("./".$cpReportFolder."/Report.php");

		$r = new Report();
			$post = array();
			$report = $r->buildReport();
			$report["SecretData"]["user_id"] = $ret["user_id"]["\$id"];
			$post["report"] = json_encode($report);
			$post["status"] = "reporting";
			$ret = prepareAndSendPost($post);


		}

		//cleandir("clean-legacy");
		//cleandir("cp_report_files");
		$verified = true;

	}else{
		$status		= "error";
		$message	= "The Customer Paradigm code audit tool cannot work given your system configuration settings. Contact us for further assistance.";
	}
	
	if(isset($ret["message"]))
	{
		$status		= $ret["status"];
		$message	= $ret["message"];
	}

}else{
	// clean all directories on first run
	//cpsystem("rm -rf clean*");
	//cpsystem("rm -rf cp_*");
	cleandir("clean-legacy");
	cleandir("cp_report_files");
	
}

function cleandir($path)
{
    if (is_dir($path) === true)
    {
        $files = array_diff(scandir($path), array('.', '..'));

        foreach ($files as $file)
        {
            cleandir(realpath($path) . '/' . $file);
        }

        return rmdir($path);
    }

    else if (is_file($path) === true)
    {
        return unlink($path);
    }

    return false;
}


?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Customer Paradigm's Magento Code Audit Report V5</title>
		<link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
		<style>
			body { font-family: 'Droid Sans', sans-serif; background-color:#FFF; color:#000; }
			section { margin:0 auto; width:400px; text-align:center; }
			input { width:200px; padding:5px; font-size:14px; }
			.label { width:125px; display:inline-block; text-align:right; margin-right:5px; font-size:16px; font-weight:bold; }
			.submit { 
				background: #0075B9;
				display: inline-block;
				padding: 5px 10px 6px;
				color: #fff;
				text-decoration: none;
				font-weight: bold;
				line-height: 1;
				border-radius: 5px;
				box-shadow: 0 1px 3px rgba(0,0,0,0.5);
				text-shadow: 0 -1px 1px rgba(0,0,0,0.25);
				border-bottom: 1px solid rgba(0,0,0,0.25);
				position: relative;
				cursor: pointer;
				margin-top:10px;
				font-size:14px;
				border-color:#0085C3;
			}
			.message {
				padding:7px;
				font-weight:bold;
				margin-bottom:7px;
			}
			.alert-success {
				background-color:#80DC74;
				border:1px solid #47B033;
			}
			.alert-error {
				background-color:#D77475;
				border:1px solid #A83332;
			}
			.cpLogo {
				margin-top:10px;
			}
			h1 {
				margin-top:1.5em;
			}
			.intro {
				text-align:left;
				margin:15px 0;
				line-height:1.4em;
			}
			a {
				color:#0075B9;
				text-decoration:none;
			}
			.terms {
				font-size:12px;
			}
		</style>
	</head>
	<body>
		
		<section>
			<?php if(isset($message)): ?>
			<div class="message alert-<?php echo $status; ?>">
				<?php echo $message; ?>
			</div>
			<?php endif; ?>
			
			<img class="cpLogo" src="http://www.customerparadigm.com/images/cp-logo-new.jpg" alt="Customer Paradigm"></img>
			
			<h1>Customer Paradigm's Magento Code Audit Report V5</h1>
			
			
			<?php if(!$verified): ?>

				<div class="intro">
				Welcome to <a href="http://www.customerparadigm.com">Customer Paradigm's</a> Magento code auditing tool. The test can take a little while to run on some stores so we will email you the results.<br>
				<br>
				Questions?  Need more information before you run the report?  Call Customer Paradigm at 303.473.4400.<br>
				<br>
				Enter your information below to begin the Code Audit Report:<br>
				</div>
				<form action="mageauditor.php" method="post">
					<label for="name" class="label">Name</label>
					<input type="text" id="name" name="name" value="<?php if(isset($_POST["name"])) { echo $_POST["name"]; } ?>"><br>
					<label for="email" class="label">Email</label>
					<input type="text" id="email" name="email" value="<?php if(isset($_POST["email"])) { echo $_POST["email"]; } ?>"><br>
					<label for="phone" class="label">Phone Number</label>
					<input type="text" id="phone" name="phone" value="<?php if(isset($_POST["phone"])) { echo $_POST["phone"]; } ?>"><br>
					<input type="hidden" id="ver" name="ver" value="5"><br>
					<input type="checkbox" id="terms" name="terms" style="width:auto;" value="1"> <label for="terms" class="terms">I agree to the <a target="_blank" href="http://www.CustomerParadigm.com/code-audit-terms-conditions/">terms and conditions</a> for this code audit.</label><br>
					<br>
					<input type="submit" value="Begin Code Audit" class="submit">
				</form>

			<?php else: ?>
				<div class="intro">
					<?php echo $_POST["name"];; ?> &mdash;<br>
 					<br>
					Your Magento Code Audit ran successfully!<br>
					<br>
					Please check your email (<?php echo $_POST["email"]; ?>) for a link to code audit.<br>
					<br>
					Questions?  Need more information before you run the report?  Call Customer Paradigm at 303.473.4400, or visit <a href="www.customerparadigm.com">www.CustomerParadigm.com</a>
				</div>
			<?php endif; ?>
		</section>
	</body>
</html>
