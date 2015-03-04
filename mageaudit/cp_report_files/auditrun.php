<?php
ini_set("display_errors", true);

/*
 * TODO: Tighten up CSS
 * TODO: Add header from mockup (modify it a bit)
 * 		TODO: Find and add icons for the different categories
 * TODO: Add highlight on mouseover of different rows
 * 		TODO: Add info on each configuration option(?)
 */

require_once("./Report.php");

if(isset($_GET["id"]))
{
	$m = new MongoClient();
	$db = $m->selectDB("codeaudit");
	$c_reports = $db->reports;
	$reportData = $c_reports->findOne(array('_id' => new MongoId($_GET["id"])));
	$c_user_info = $db->user_info;
	$user = $c_user_info->findOne(array('_id' => new MongoId($reportData["SecretData"]["user_id"])));
	$site = parse_url($user["site"]);
	$date = date('M d, Y h:i:s', $reportData["SecretData"]["timestamp"]);
}
else
{
	// die("No report ID supplied.");
	$r = new Report();
	$reportData = $r->buildReport();
}

// print_r($reportData["SecretData"]);

function print_d($a)
{
	echo "<pre>" . str_ireplace(array("\n"," "),array('<br>','&nbsp;'),print_r($a,true)) . "</pre>";
}

function getLetterGrade($g)
{
	if($g > .90)
	{
		$letter = "<span style='color:#0F0;font-size:24px;'>A";
	}
	elseif($g > .80)
	{
		$letter = "<span style='color:#91C300;font-size:24px;'>B";
	}
	elseif($g > .70)
	{
		$letter = "<span style='color:#FF9600;font-size:24px;'>C";
	}
	elseif($g > .60)
	{
		$letter = "<span style='color:#FF4200;font-size:24px;'>D";
	}
	else
	{
		$letter = "<span style='color:#F00;font-size:24px;'>F";
	}
	
	return $letter . "</span>";
}

?>
<!DOCTYPE HTML>
<html>
	<head>
		<link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="./style.css">
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script>
			$(document).ready( function() {
				$("a[id^=detail-]").each(function(){
					$(this).click(function (e) {
						e.preventDefault();
						if($(this).html() == "Show Details")
						{
							$(this).html("Hide Details");
						}
						else
						{
							$(this).html("Show Details");
						}
						$("#" + this.getAttribute('data-box')).stop().slideToggle();
					});
				});
				$("#expandAll").click(function (e) {
					e.preventDefault();
					if($(this).html() == "Expand All")
					{
						$(this).html("Close All");
						$("a[id^=detail-]").each(function () { $(this).html("Hide Details") });
						$(".expandedInfo").each(function () {
							$(this).stop().slideDown();
						});
					}
					else
					{
						$(this).html("Expand All");
						$("a[id^=detail-]").each(function () { $(this).html("Show Details") });
						$(".expandedInfo").each(function () {
							$(this).stop().slideUp();
						});
					}
				});
			});
		</script>
	</head>
	<body>
		<section class="report">
			<a href="./pdfauditrun.php?id=<?php echo $_GET["id"]; ?>">Grab the PDF!</a>
			<div class="tableTop">
				<div class="topDetails" style="left:10px;">
					Performance Grade: <?php echo getLetterGrade($reportData["SecretData"]["Grades"]["Performance"]); ?><br>
					Upgradability Grade: <?php echo getLetterGrade($reportData["SecretData"]["Grades"]["Upgradability"]); ?>
				</div>
				<div>
				Magento Code Audit
				</div>
				<div class="topDetails" style="right:10px;">
					<?php echo $site["host"]; ?><br>
					<?php echo $date; ?>
				</div>
			</div>
			<table cellpadding="0" cellspacing="0" width="920px" style="table-layout:fixed;">
			<?php
				$rowCount = 1;
				$rowTypes = array("evenRow row", "oddRow row");
				$rowClass = $rowTypes[$rowCount];
			?>
				<tr class="evenRow row">
					<th class="category categoryTop categoryBottom">Category</th>
					<td class="sectionDivider"></td>
					<td class="dataColumn testName"><strong>Test Name</strong></td>
					<td class="dataColumn results">
						<strong>Results</strong>
						<?php if(!$_GET["expanded"]): ?>
							<a id="expandAll" class="detailButton" style="margin-top:5px;margin-right:5px;" href="#">Expand All</a>
						<?php endif; ?>
					</td>
				</tr>
			<?php foreach($reportData as $sectionKey => $sectionData): ?>
				<?php if($sectionKey !== "_id" && $sectionKey !== "SecretData"): ?>
				<tr class="<?php echo $rowClass; ?>">
					<?php $currRow = 0; ?>
					<?php foreach($sectionData as $key => $value): ?>
						<?php if($currRow > 0) echo "<tr class='".$rowClass."'>"; ?>
							<th class="category<?php if($currRow === 0) { echo " categoryTop"; } if($currRow === count($sectionData)-1) { echo " categoryBottom"; } ?>" >
								<?php if($currRow === 0) echo $sectionKey; ?>
							</th>
							<td class="sectionDivider"></td>
							<td class="dataColumn testName c<?php echo $value["status"]; ?>">
								<?php echo $key; ?>
							</td>
							<td class="dataColumn results c<?php echo $value["status"]; ?>">
								<div class="results">
									<span class="icon <?php echo $value["status"]; ?>"></span>
									<span class="resultsValue">
										<?php echo $value["value"]; ?>
									</span>
									<?php if($value["details"] !== NULL && !$_GET["expanded"]): ?>
									<a id="detail-<?php echo $rowCount; ?>" class="detailButton" data-box="moreInfo-<?php echo $rowCount; ?>" href="#">Show Details</a>
									<?php endif; // End details button if ?>
									<?php if(isset($value["details"])): ?>
									<div id="moreInfo-<?php echo $rowCount; ?>" class="expandedInfo" <?php if($_GET["expanded"]) { echo "style=display:block;"; } ?>>
										<?php foreach($value["details"] as $detail): ?>
										<?php echo $detail; ?><br>
										<?php endforeach; ?>
									</div>
									<?php endif; // End expanded info if ?>
								</div>
							</td>
						</tr>
					<?php
						$currRow++;
						$rowCount++;
						$rowClass = $rowTypes[$rowCount%count($rowTypes)];
					?>
					<?php endforeach; ?>
				<?php endif; // End section key check if ?>
			<?php endforeach; ?>
			</table>
		</section>
	</body>
</html>