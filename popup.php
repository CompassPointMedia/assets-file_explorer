<?php
/* image upload manager v0.9 -- see readme.txt file */
$bufferDocument=false;
$relatebase=false;// this is a standalone object, set to true to integrate with a login system

$localSys['scriptID']='file_manager exe page';
$localSys['build']='001';
$localSys['buildDate']='2006-06-23 09:00:00';
$localSys['buildNotes']='[We are now at beta :-] Locked the first alpha version into build 1. Two modes present only';

$configPathReplace='popup.php';
require(str_replace($configPathReplace,'',__FILE__).'config.php');

if(!$_GET['uid']){
	// --------- all calls to FE require a unique identifier (uid) in the query string
	exit('no uid passed');
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $title?></title>
<script id="jsglobal" language="javascript" type="text/javascript" src="Library/js/global_04_i1.js"></script>
<script id="jscommon" language="javascript" type="text/javascript" src="Library/js/common_04_i1.js"></script>
</head>

<body>
<form action="file_manager_01_exe.php" method="post" target="w1">
<?php
if($layout=='box'){
	//see if the folder has stats on this
	list($width,$height)=is_boxed($systemRoot.($folder?'/':'').$folder);
	?>
	<div id="boxLayout">
		<h2>Box Folder</h2>
		This allows you to "box" a folder - all images uploaded to this folder (or copied and pasted) will be shrunk inside the "box" you create.  You may specify both a width and a height (for example 350 pixels by 350 pixels), or only one dimension.  To clear box constraints on a folder, click "Clear Box".
		<div id="folderHeader" style="font-size:169%;font-weight:400;"><img src="/images/i/fex104/folder0.gif" alt="folder" width="37" height="37" />&nbsp;<?php echo $HTTPRootFolderName.($folder?'/':'').$folder?>		</div>
		Box Width: <input name="width" type="text" id="width" value="<?php echo $width?>" size="5" maxlength="5" />
		<br />
		Box Height: 
		<input name="height" type="text" id="height" value="<?php echo $height?>" size="5" maxlength="5" />
		<br />
		<input type="submit" name="Submit" value="Set Box" onclick="g('mode').value='boxedfolder';" />
		<input type="submit" name="Submit" value="Clear Box" <?php echo !$width && !$height ? 'disabled':''?> onclick="g('mode').value='clearboxedfolder';" />
		<input type="button" name="Button" value="Cancel" onclick="window.close();" />
		<input name="mode" type="hidden" id="mode" value="boxedfolder" />
		<input name="folder" type="hidden" id="folder" value="<?php echo h($folder)?>" />
		<input name="uid" type="hidden" id="uid" value="<?php echo $uid?>" />
	</div>
	<?php
}

?>
</form>
<div id="ctrlSection" style="display:<?php echo $testModeC?'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
</div>
</body>
</html>
