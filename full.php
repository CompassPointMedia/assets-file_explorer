<?php 
/* image upload manager v0.9 -- see readme.txt file */
$bufferDocument=false;
$relatebase=false;// this is a standalone object, set to true to integrate with a login system

$localSys['scriptID']='file_manager exe page';
$localSys['build']='001';
$localSys['buildDate']='2006-06-23 09:00:00';
$localSys['buildNotes']='[We are now at beta :-] Locked the first alpha version into build 1. Two modes present only';

$configPathReplace='full.php';
require(str_replace($configPathReplace,'',__FILE__).'config.php');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Full-size view of folder: <?php echo $folder?></title>
<link href="Library/simple_100.css" rel="stylesheet" type="text/css" />
<link href="Library/contextmenu_v400.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body{
	margin:0px;
	padding:0px;
	}
#fileTab{
	display:none;
	}
#fullFileWrap{
	/*margin:25px 8px 15px 4px;*/
	padding-top:25px;
	}
.freeFloatBox{
	float:left;
	border:1px dotted #999;
	padding:15px;
	margin:0px 0px 12px 12px;
	text-align:center;
	}
#ctrlAddNewFile{
	cursor:pointer;
	float:left;
	padding:3px 15px 0px 10px;
	}
</style>

<script id="jsglobal" type="text/javascript" src="Library/js/global_04_i1.js"></script>
<script id="jscommon" type="text/javascript" src="Library/js/common_04_i1.js"></script>
<script id="jsforms" type="text/javascript" src="Library/js/forms_04_i1.js"></script>
<script id="jsloader" type="text/javascript" src="Library/js/loader_04_i1.js"></script>
<script id="jsdataobjects" type="text/javascript" src="Library/js/dataobjects_04_i1.js"></script>
<script id="jscontextmenus" type="text/javascript" src="Library/js/contextmenus_04_i1.js"></script>
<script id="jsgeneral" src="Library/general_v100.js"></script>
<?php require($FEX_ROOT.'/components/comp_003_ccpdata_v100.php');?>
<script language="javascript" type="text/javascript">
var feVersion='<?php echo $feVersion?>';
var thispage = 'index.php';
var thisfolder = '';
var ctime='<?php echo $ctime=time();?>';
var folder ='<?php echo $folder?>';
var cb = '<?php echo $cb?>';
var cbTarget='<?php echo $cbTarget?>';
var cbTargetNode='<?php echo $cbTargetNode?>';
var cbFunction='<?php echo $cbFunction?>';
var cbOverrideMulti=<?php echo isset($cbMultiple) && $cbMultiple=='0'?'true':'false'?>;
var view='<?php echo $view?>';
var HTTPRootFolderName='<?php echo $HTTPRootFolderName?>';
var currentFolder='<?php echo $currentFolder?>';
var uid='<?php echo $uid?>';
var folders=new Array();
var testModeC=<?php echo $testModeC?$testModeC:0?>;

function fullPageManagement(n){
	//clumsy but avoids us having to pass cmBoundToObject
	pendingFileObjectLoads=[];
	imWidgetCalc();
	window.open('file_manager_01_exe.php?uid=refreshComponent&mode=refreshComponent&component=fullFileWrap&folder='+folder,'w5');
}
</script>

</head>

<body>
<form id="form1" name="form1" target="w2" method="post" action="file_manager_01_exe.php" enctype="multipart/form-data">
	<input name="cbPresent" type="hidden" id="cbPresent" value="1" />
	<input name="cbFunction" type="hidden" id="cbFunction" value="fullPageManagement" />
	<input name="cbParam" type="hidden" id="cbParam" value="fixed:uploadFile" />
	<input name="cbLocation" type="hidden" id="cbLocation" value="wp" />
	<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />

	<div id="menubar">
		<div style="float:left;padding:3px 15px 0px 10px;">[U] [F] thequotablequill</div>
		<div id="ctrlAddNewFile" onclick="hm_cxlseq=2;showmenuie5(event);"><span>Add new file</span></div>
		&nbsp;&nbsp;
		box width: 
		<input name="boxed[0]" type="text" id="boxed[0]" value="<?php echo $boxed[0] ? $boxed[0] : '*'?>" size="6" />
		box height: 
		<input name="boxed[1]" type="text" id="boxed[1]" value="<?php echo $boxed[1] ? $boxed[1] : '*'?>" size="6" />
		box method:  
		<select name="boxMethod" id="boxMethod">
		  <option value="<?php echo BOX_FOUR_WALL?>">4 wall box</option>
		  <option value="<?php echo BOX_TWO_WALL?>">2 wall box</option>
		</select>
		<div style="clear:both;font-size:2px;">&nbsp;</div>
	</div>
	<div id="fullFileWrap">
		<?php
		require($FEX_ROOT.'/components/comp_004_node_fullfolder_v100.php');
		?>
	</div>
	<?php
	$fOWhichTab=='fileTabNew';
	$fOAssignToRegex='ctrlAddNewFile';
	$fOMenuAlignment='mouse';
	$fOdefaultFolder=$folder;
	$fOBoxWidth='350';
	$fOBoxHeight='350';
	require('../../devteam/php/components/imagemanagerwidget_01.php');
	?>
</form>
<div id="js_show" onclick="g('js_tester').style.display=(g('js_tester').style.display=='block'?'none':'block');"><img src="/images/i/fex104/spacer.gif" width="5" height="5" /></div>
<div id="js_tester">
	<form name="js_tester_form" action="" method="post">
		<textarea class="tw" name="test" cols="65" rows="3" id="test"></textarea><br />
		<input type="button" name="Submit" value="Test" onclick="jsEval(g('test').value);">
		&nbsp;<a href="#" onclick="g('ctrlSection').style.display=op[g('ctrlSection').style.display];return false">Iframes</a><br />
		<textarea class="tw" name="result" cols="65" rows="3" id="result"></textarea>
	</form>
</div>
<div id="ctrlSection" style="display:<?php echo $testModeC?'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
	<iframe name="w5" src="/Library/js/blank.htm"></iframe>
</div>

</body>
</html>
