<?php 
/* image upload manager v0.9 -- see readme.txt file */
//standard error reporting/display coding
function set_test_env(){
    error_reporting(E_ALL | E_STRICT);
    $AppEnv = getenv('AppEnv');
    if($AppEnv == 'production'){
        ini_set('display_errors',false);
    }else if($AppEnv == 'vagrant') {
        ini_set('display_errors',false);
    }else{
        // for now
        ini_set('display_errors',true);
    }
}
set_test_env();

//2017-07-04 - this is the simplest possible globalizer; _GET vars have precedence
//extractor
if(!function_exists('addslashes_deep')){
    function addslashes_deep($value){
        $value = is_array($value) ?
            array_map('addslashes_deep', $value) :
            addslashes($value);
        return $value;
    }
}
$extract = ['_POST'=>1, '_GET'=>1];
foreach($extract as $_GROUP => $clean){
    if(empty($GLOBALS[$_GROUP])) continue;
    if($clean){
        foreach($GLOBALS[$_GROUP] as $n => $v){
            $GLOBALS[$_GROUP][$n] = addslashes_deep($v);
        }
    }
    extract($GLOBALS[$_GROUP]);
}

$bufferDocument=false;
$relatebase=false;// this is a standalone object, set to true to integrate with a login system

$localSys['scriptID']='file_manager index page';
$localSys['build']='002';
$localSys['buildDate']='2009-02-08 09:00:00';
$localSys['buildNotes']='organizing this page for better rename capability and better use of menu items';

if(!$_REQUEST['uid']){
	// --------- all calls to FE require a unique identifier (uid) in the query string
	$uid=substr(md5(time().rand(1,25)),0,12);
	$qs=preg_replace('/&*uid=/i','',$QUERY_STRING);
	$qs.=($qs ? '&' : '').'uid='.$uid;
	$location='index.php?'.$qs;
	?><script language="javascript" type="text/javascript">
	window.location='<?php echo $location?>';
	</script><?php
	exit;
}


$configPathReplace='index.php';
require(str_replace($configPathReplace,'',__FILE__).'config.php');

if($createFolder && $folder){
	// we create just the last folder, so everything up to there must exist
	$f=explode('/',ltrim(rtrim($folder,'/'),'/'));
	$new=$f[count($f)-1];
	unset($f[count($f)-1]);
	$path=$systemRoot.'/'.implode('/',$f);
	if(!is_dir($path))exit("Unable to create folder $new, because the path $path is not valid");
	if(!is_dir($path.'/'.$new)){
		if(!mkdir($path.'/'.$new)){
			exit('<h2>Unable to create folder '.$new.'</h2>');
		}
	}
}else if($folder && !is_dir($systemRoot.'/'.ltrim(rtrim($folder,'/'),'/'))){
	// --------- they are calling a folder which does not exist
	$nonExistentDir=true;
	$target='index.php?';
	foreach($_GET as $n=>$v){
		if(strtolower($n)=='folder')continue;
		$a[]=$n.'='.urlencode($v);
	}
	$target.='?'.implode('&',$a);
	?><script language="javascript" type="text/javascript">
	alert('The folder <?php echo $folder?> does not exist; redirecting to root folder in 3 seconds');
	setTimeout('window.location=\'<?php echo $target?>\'',3000);
	</script><?php
	exit;
}

//clear session mirror of visible objects
$feObject=&$_SESSION['file_explorer'][$uid];
unset($_SESSION['file_explorer'][$uid]['folders'], $_SESSION['file_explorer'][$uid]['index']);
$feObject['extra']=1;

//see if this folder is boxed
if($boxDimensions=is_boxed($systemRoot.($folder?'/'.$folder:''),false)){
	extract($boxDimensions);
	if(!preg_match('/^[0-9]+$/', str_replace('*',16,$boundingBoxWidth)) || str_replace('*',16,$boundingBoxWidth)<16 || !preg_match('/^[0-9]+$/', str_replace('*',16,$boundingBoxHeight)) || str_replace('*',16,$boundingBoxHeight)<16){
		//mail developer - this should be bulletproof
		$boundingBoxError=true;
		unset($boundingBoxWidth, $boundingBoxHeight, $boxMethod);
	}
}
if($passedBoundingBoxWidth || $passedBoundingBoxHeight){
	if(!$passedBoxMethod)$passedBoxMethod=BOX_FOUR_WALL;
	if(!preg_match('/^[0-9]+$/', str_replace('*',16,$passedBoundingBoxWidth)) || str_replace('*',16,$passedBoundingBoxWidth)<16 || !preg_match('/^[0-9]+$/', str_replace('*',16,$passedBoundingBoxHeight)) || str_replace('*',16,$passedBoundingBoxHeight)<16){
		$passedBoundingBoxError=true;
		error_alert('here');
		unset($passedBoundingBoxWidth, $passedBoundingBoxHeight, $passedBoxMethod);
	}
}
//2012-03-20: if we are selecting a file for CMSB, redirect to last-used folder
if($disposition=='selector' && !$overrideSelectorRedirect && $_COOKIE['lastnode'] && $_COOKIE['lastnode']!=$node){
	$g=$_GET;
	$g['folder']=str_replace('/images/','',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_COOKIE['lastnode']));
	foreach($g as $n=>$v)$str.=$n.'='.urlencode($v).'&';
	$str=rtrim($str,'&');
	header('Location: index.php?'.$str);
	exit;
}

if(isset($customDocType)){
	echo $customDocType;
}else{ 
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><?php
}
ob_start();
?><html xmlns="http://www.w3.org/1999/xhtml"><?php
$out=ob_get_contents();
ob_end_clean();
echo isset($customDocType) ? str_replace('xmlns="http://www.w3.org/1999/xhtml"', $customDocTypeFlag, $out) : $out;
?>
<head>
<title><?php echo $titleName ? $titleName : 'RelateBase';?> FEX - Ver.<?php echo $feVersion?></title>
<meta name="Description" content="Server File System Management" />
<meta name="Keywords" content="Compass Point Media, Advanced Graphics and Database development/integration" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<link href="Library/simple_100.css" rel="stylesheet" type="text/css" />
<link href="Library/contextmenu_v400.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<?php if($disposition=='selector'){ ?>
#FileSystemFocus_1_1{
	height:70%;
	}
<?php } ?>
</style>

<script id="jsglobal" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script id="jscommon" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script id="jsforms" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script id="jsloader" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script id="jsdataobjects" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script id="jscontextmenus" type="text/javascript" src="/Library/js/contextmenus_04_i1.js"></script>
<script language="javascript" type="text/javascript">var disposition='<?php echo $disposition;?>'; </script>
<script id="jsgeneral" src="Library/general_v100.js"></script>
<?php require($FEX_ROOT.'/components/comp_003_ccpdata_v100.php');?>
<script language="javascript" type="text/javascript">
var feVersion='<?php echo $feVersion?>';
var thispage = 'index.php';
var thisfolder = '';
var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime=time();?>';
var cb = '<?php echo $cb?>';
var cbTarget='<?php echo $cbTarget?>';
var cbTargetExt='<?php echo $cbTargetExt?>';
var cbTargetNode='<?php echo $cbTargetNode?>';
var cbFunction='<?php echo $cbFunction?>';
var cbOverrideMulti=<?php echo isset($cbMultiple) && $cbMultiple=='0'?'true':'false'?>;
var view='<?php echo $view?>';
var HTTPRootFolderName='<?php echo $HTTPRootFolderName?>';
var currentFolder='<?php echo $currentFolder?>';
var uid='<?php echo $uid?>';
var folders=new Array();
var testModeC=<?php echo strlen($testModeC) ? $testModeC : 0?>;

<?php
if(count($folders)){
	$i=0;
	foreach($folders as $n=>$v){
		//We can safely increment i and be sure the node numbers will be the same 
		//as the session nodes which will later be assigned
		$i++;
		?>folders[<?php echo $i?>]=<?php echo $i?><?php echo ";\n";
	}
}

foreach($folderViews as $n=>$v){
	
}
?>
var fileCount=<?php echo $fileCount ? $fileCount : '0';?>;
var folderCount=<?php echo $folderCount ? $folderCount : '0';?>;
var boundingBoxWidth=<?php echo $boundingBoxWidth ? $boundingBoxWidth : "''"?>;
var boundingBoxHeight=<?php echo $boundingBoxHeight ? $boundingBoxHeight : "''"?>;
var boxMethod=<?php echo $boxMethod ? $boxMethod : ($boundingBoxWidth || $boundingBoxHeight ? BOX_FOUR_WALL : '0')?>;
</script>
</head>
<body>
<div id="contentWrap">
<div id="verBlock" onClick="var l=window.location+'';window.location=l.replace(/#$/,'');">File Explorer Beta V<?php echo $feVersion?></div>
<div id="topRegion">
	<form action="file_manager_01_exe.php" method="post" enctype="multipart/form-data" id="RootToolsForm" target="w2" onSubmit="return beginSubmit(precallfunction);">
	<input name="currentLocation" type="hidden" id="currentLocation" value="<?php echo h($rootFolderName . ($folder ? '/' : '') . $folder)?>" />
	<input name="mode" type="hidden" id="mode" value="uploadFile" />
	<input name="view" type="hidden" id="view" value="<?php echo $view?$view:'thumbnails'?>" />
	<input name="folder" type="hidden" id="folder" value="<?php echo h($folder)?>" />
	<input name="maxNode" type="hidden" id="maxNode" value="<?php echo count($files)?>" />
	<input name="uid" type="hidden" id="uid" value="<?php echo $uid?>" />
	<input name="dels" type="hidden" id="dels" value="" />
	<input name="sels" type="hidden" id="sels" value="" />
	<input name="selNodes" type="hidden" id="selNodes" value="" />
	<!-- used to determine whether we paste into a folder or into "here"; over the folder = ccp05a, over the field = ccp05c and at top = ccp05b (interpret as the field) -->
	<input name="pasteOption" type="hidden" id="pasteOption" value="" />
	<div id="RootTools">
		<div class="primaryData">
			<span id="upFolderAnchor" onClick="if(g('folder').value)oF(0,2);"><img id="upFolder" align="absbottom" src="/images/i/fex104/i-upfolder.png" alt="upfolder" width="39" height="32" class="<?php echo !$folder?'ghost':''?>" /></span>
			<span id="fullPath"><?php
			//show visible path for navigation
			fe_visiblefolderpath($rootFolderName, $folder);
			?></span><span id="visibleSelectionWidget">&nbsp;</span>	</div>
		<div class="primaryToolbar">
			<div id="historyWrap">
				<div style="float:left;"><a class="toolbar" title="Go back in the history"><img align="absbottom" id="i-history-back" src="/images/i/fex104/i-history-back.png" alt="back" width="32" height="32" /></a></div>
				<div style="float:left;"><a class="toolbar" title="Go forward in the history"><img align="absbottom" id="i-history-fwd" src="/images/i/fex104/i-history-fwd.png" alt="fwd" width="32" height="32" /></a></div>
				
				<div style="float:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
				
				<div id="ctrlAddNewFile" title="Upload a new file from your computer" style="float:left;" onClick="hm_cxlseq=2;showmenuie5(event);" class="imgIcon">
					<img align="absbottom" src="/images/i/fex104/i-newfile.png" alt="new" width="28" height="32" />
				</div>
				<div class="imgIcon" title="Create a new folder" onClick="obj_newfolder();"><img id="iNewFolder" align="absbottom" src="/images/i/fex104/i-folder_add.png" alt="newfolder" width="32" height="32" /></div>
				<div class="imgIcon" title="Delete selected files and folders" onClick="if(this.firstChild.className=='noGhost')obj_send(null,'deleteObjects');"><img id="iDelete" align="absbottom" src="/images/i/fex104/i-delete.png" alt="delete" width="32" height="32" /></div>
				
				<div style="float:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
				
				<div class="imgIcon" title="Cut the selected items from this folder (hold down shift key to cut multiple times)" onClick="if(this.firstChild.className=='noGhost')obj_send(null, event.shiftKey?'ccp_cutmore':'ccp_cut');"><img id="iCut" align="absbottom" src="/images/i/fex104/i-ccp1-cut.png" alt="cut" width="32" height="32" /></div>
				<div class="imgIcon" title="Copy the selected items to the clipboard (hold down shift key to copy multiple times)" onClick="if(this.firstChild.className=='noGhost')obj_send(null, event.shiftKey?'ccp_copymore':'ccp_copy');"><img id="iCopy" align="absbottom" src="/images/i/fex104/i-ccp1-copy.png" alt="copy" width="29" height="32" /></div>
				<div class="imgIcon" title="Paste the contents of the clipboard here" onClick=""><img id="iPaste" align="absbottom" src="/images/i/fex104/i-ccp1-paste.png" alt="paste" width="29" height="32" /></div>
				
				<div style="float:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

				<div id="viewGadget">
					<div id="viewButton" onMouseOver="vgS=true;" onMouseOut="vgS=false;" onClick="return viewGadget()" title="Switch folder view (currently <?php echo $config['view']?>"><img id="viewGadgetIcon" alt="view icon" src="/images/i/fex104/<?php echo $folderViews[strtolower($view)][0]?>" align="absbottom" width="32" /></div>
					
					<!--
					<div style="float:left;position:relative;width:28px;border:1px solid #333;">
					<img id="viewIcon" src="/images/i/fex104/<?php echo $folderViews[$config['view']][0]?>" />
					</div>
					-->
								
					<div id="viewMain" onMouseOver="vgS=true;" onMouseOut="vgS=false;"><?php
					foreach($folderViews as $n=>$v){
						?><div id="viewFolderOption-<?php echo $n?>" onMouseOver="hlView(this);" onMouseOut="hlView(this,0)" onClick="switchView('<?php echo $n?>');hideviewMain(true);" class="vg viewOptionsOff"><img alt="<?php echo $v[0]?>" src="/images/i/fex104/<?php echo $v[0]?>" align="absbottom" />&nbsp;<?php echo $v[1]?></div><?php
					}
					?>
					</div>
				</div>

				<div style="float:left;">&nbsp;&nbsp;</div>
				<div class="imgIcon" style="float:left;" onClick="ooF(g('folder').value,6);" title="Refresh the view of this folder"><img src="/images/i/fex104/i-refresh.png" width="32" alt="refresh" align="absbottom" /></div>
				<div style="float:left;">&nbsp;&nbsp;</div>
				<div style="float:left;"><img id="boxFolder" src="/images/i/fex104/i-box-<?php echo $boxMethod ? $boxMethod : '0'?>.png" alt="box" width="32" height="32" style="display:none;" /> <span id="boxDims"><?php
				if($boundingBoxWidth || $boundingBoxHeight){
					echo 'Boxed at '.$boundingBoxWidth .' by '.$boundingBoxHeight.', '.$boxMethod.'-wall';
					?>[<a href="#" onClick="return clearBox();" title="Clear bounding box for this folder">x</a>]<?php
				}
				?></span>
				&nbsp;&nbsp;
				</div>
				<div class="imgIcon" style="float:left;"><a href="http://dev.compasspointmedia.com/mediawiki-1.13.2/index.php?title=File_Explorer_Help" title="Get help using File Explorer!" target="FEXHelp"><img src="/images/i/fex104/i-help1.png" width="32" alt="help" align="absbottom" /></a></div>
				
				<div class="imgIcon" style="float:left;"><a href="leftnav.php?uid=<?php echo $uid?>" title="View all folders and navigate them" onClick="return ow(this.href,'l1_leftnav','700,700');">Nav</a></div>
				
				<div style="clear:both;font-size:1px;">&nbsp;</div>
			</div>
		</div>
		<?php require($FEX_ROOT.'/components/comp_005_newfilewidget.php');?>
	</div>
	</form>
</div>
<div id="filesFoldersStats1" onClick="showSession();"><span id="filesStats1"><?php
echo $fileCount . ' file'.($fileCount!==1 ? 's' : '').', ';
?></span>&nbsp;<span id="foldersStats1"><?php
echo $folderCount . ' folder'.($folderCount!==1 ? 's' : '');
?></span>
<span id="folderSize"></span></div>
<?php 
if($view=='details'){
	require($FEX_ROOT.'/components/comp_007_filetable_v100.php');
}else{
	require($FEX_ROOT.'/components/file_manager_02_filelist.php');
}
?>

<?php
//file selector at bottom
if($disposition=='selector'){
	?><div id="bottomBar">
		<form action="file_manager_01_exe.php" method="get" id="selectorForm" target="w3" onSubmit="return beginSubmit(selectorprecallfunction);">
			<div style="float:right;">
				<input type="button" name="button" value="<?php echo $cb=='saveas'?' Save ':'  OK  '?>" style="width:75px" onClick="return cbResponse();" /><br />
				<input type="button" name="button" value="Cancel" style="width:75px;" onClick="window.close();" />
			</div>
			File Name: <input name="cbSelector" type="text" id="cbSelector" value="<?php echo $cbSelector?>" size="55" /><br />
			Files of Type: 
			<select name="cbFileType" id="cbFileType">
			<option value="">--</option>
			</select>
			<input name="mode" type="hidden" id="mode-selectorForm" value="selectorInput" />
			<input name="cbSelectorExt" type="hidden" id="cbSelectorExt" />
		</form>
	</div>
	<?php
}
?>

<div id="ctrlSection" style="display:<?php echo $testModeC?'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
	<iframe name="w5" src="/Library/js/blank.htm"></iframe>
</div>
<div id="js_show" onClick="g('js_tester').style.display=(g('js_tester').style.display=='block'?'none':'block');"><img src="/images/i/fex104/spacer.gif" width="5" height="5" /></div>
<div id="js_tester" <?php if($testing)echo 'style="display:block;"'; ?>>
	<form name="js_tester_form" action="" method="post">
		<textarea class="tw" name="test" cols="65" rows="3" id="test"></textarea><br />
		<input type="button" name="Submit" value="Test" onClick="jsEval(g('test').value);">
		&nbsp;<a href="#" onClick="g('ctrlSection').style.display=op[g('ctrlSection').style.display];return false">Iframes</a><br />
		<textarea class="tw" name="result" cols="65" rows="3" id="result"></textarea>
  </form>
</div>
</body>
</div>
</html><?php
//this function can vary and may flush the document 
function_exists('page_end') ? page_end() : mail($developerEmail,'page end function not declared', 'File: '.__FILE__.', line: '.__LINE__,'From: '.$hdrBugs01);
?>