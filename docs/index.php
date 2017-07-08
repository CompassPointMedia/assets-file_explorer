<?php 
/* image upload manager v0.9 -- see readme.txt file */
$bufferDocument=false;
$relatebase=false;// this is a standalone object, set to true to integrate with a login system

require(str_replace('docs/index.php','config.php',__FILE__));
/****
Compass Point Media PHP initial coding suite v1.0 - 2006-05-14 - for documentation see Compasspointmedia.com/help/codingsuites.php

****/

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<!-- Compass Point Media XHTML1.0 Template, v1.0 - 2006-05-14 http://www.compasspointmedia.com -->
<head>
<title>File Explorer Help</title>
<meta name="Description" content="File Explorer Help" />
<meta name="Keywords" content="Compass Point Media, Advanced Graphics and Database development/integration" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../Library/simple_100.css" rel="stylesheet" type="text/css" />
<link href="../Library/contextmenu_v400.css" rel="stylesheet" type="text/css" />
<script src="../Library/js/general_v100.js"></script>
<script language="javascript" type="text/javascript">
var feVersion='<?php echo $feVersion?>';
var thispage = 'docs/index.php';
</script>
</head>
<body><div id="verBlock">File Explorer Beta V<?php echo $feVersion?></div>
<div id="main" style="width:650px;">
	<!-- javascript tester -->
	<div id="js_show" onclick="g('js_tester').style.display=(g('js_tester').style.display=='block'?'none':'block');"><img src="../i/spacer.gif" width="5" height="5" /></div>
	<div id="js_tester">
		<form name="js_tester_form" action="" method="post">
			<textarea class="tw" name="test" cols="65" rows="3" id="test"></textarea><br>
			<input type="button" name="Submit" value="Test" onclick="jsTest('test');"><br>
			<textarea class="tw" name="result" cols="65" rows="3" id="result"></textarea>
		</form>
	</div>
   <h2>File Explorer Help
	</h2>
	Currently File Explorer is under
				development. This help page will also be developed as the features are added.
				Currently you can do the following:<br />
	<ul>
		<li>Navigate the folders by
		entering the path, double-clicking a folder or clicking
		the &quot;Up Folder&quot; icon</li>
		<li>Select multiple items by holding down the control key when clicking
		(shift-click not available yet)</li>
		<li>Right-click on items just like in windows - the only menu options
		available are Open and Delete</li>
		<li>Upload new files by clicking the &quot;Browse&quot; button. As soon as the
		file is selected from your computer, the upload begins automatically.</li>
	</ul>
	
</div>
<div id="ctrlSection" style="display:<?php echo $testModeA && false?'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
	<iframe name="w5" src="/Library/js/blank.htm"></iframe>
</div>
</body>
</html><?php
//this function can vary and may flush the document 
function_exists('page_end') ? page_end() : mail($developerEmail,'page end function not declared', 'File: '.__FILE__.', line: '.__LINE__,'From: '.$hdrBugs01);
?>
