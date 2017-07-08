<?php 
/* image upload manager v1.0 -- see readme.txt file */
if(strlen($sessionid)) session_id($sessionid);
if(!session_id()){
	session_start();
}
$sessionid ? '' : $sessionid = session_id();

if(file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/config.file_explorer.php'))require($_SERVER['DOCUMENT_ROOT'].'/admin/config.file_explorer.php');
require(str_replace('config.php','file_manager_00_includes.php',__FILE__));
$qx['defCnxMethod']=C_MASTER;

/** version of file explorer **/
$feVersion='1.1.1';
if($bufferDocument) ob_start();

//passed as $method var to file_manager_01_exe.php
define('GETFOLDER_DBLCLICK',1);
define('GETFOLDER_UPFOLDER',2);
define('GETFOLDER_TYPEDIN',3);

define('CCP_COPY',1);
define('CCP_CUT',2);
define('CCP_COPYMORE',4);
define('CCP_CUTMORE',8);

define('BOX_NO_BOX',0);
define('BOX_TWO_WALL',2);
define('BOX_FOUR_WALL',4);
//this is an array of file or folder names which will be hidden
define('OBJ_HIDDEN', 1);
define('OBJ_GHOSTED', 32);
define('OBJ_CUT',128);
define('OBJ_NORMAL', 256);
//note keys must be all lowercase
$hiddenObjects=array(
	'.htaccess'=>OBJ_HIDDEN, 
	'error_log'=>OBJ_HIDDEN,
	'.thumbs.dbr'=>OBJ_HIDDEN, 
	'.file_explorer.stats.dbr'=>OBJ_HIDDEN, 
	'reader.php'=>OBJ_HIDDEN,
	'index.php'=>OBJ_HIDDEN,
	'recursor.php'=>OBJ_HIDDEN,
	'skunkreader.php'=>OBJ_HIDDEN,
	'skunkreader2.php'=>OBJ_HIDDEN,
);


/*
2007-12-10: note that from 0.9.36 I am adding the ability to use a symbolic link
*/
if(false && "Use this when hard coded - on user's website"){
	//this is the folder above which you cannot go, and which all folders files must be inside; permissions must be set appropriately for all things in this folder - for now, THE IMAGE MANAGER MUST BE ON THE SAME SITE!
	$documentRoot='/home/cpm000/public_html';
	$systemRoot='/home/cpm000/public_html/images';
	//this must be the same as the last folder in systemRoot above
	$rootFolderName='images'; 
	//may be same as previous but if more above add that e.g. /~cpm000/images - a leading slash REQUIRED, but no ending slash please
	$HTTPRootFolderName='/~cpm000/images';
	//this is a relative path from the above, blank normally
	$defaultStartFolder='';
}else{
	if(preg_match('/^\/~([0-9a-z]+)\//',$_SERVER['PHP_SELF'],$a)){
		$x=str_replace($a[0],'',$_SERVER['PHP_SELF']);
		$systemRoot=str_replace($x,'',$_SERVER['PATH_TRANSLATED']);
		$documentRoot=rtrim($systemRoot,'/');
		$systemRoot.='images';
		$HTTPRootFolderName='/~'.$a[1].'/images';
	}else{
		$documentRoot=$_SERVER['DOCUMENT_ROOT'];
		$FEX_ROOT=$_SERVER['DOCUMENT_ROOT'].'/admin/file_explorer';
		$systemRoot=$documentRoot.'/images';
		$HTTPRootFolderName='/images';
	}
	if($systemRootDevReplace)$systemRoot=str_replace('/dev/','/public_html/',$systemRoot);
	$feRoot=str_replace('/images','/admin',$systemRoot);
	if(file_exists($feRoot.'/file_explorer_config.php'))require($feRoot.'/file_explorer_config.php');
	$rootFolderName='images'; 
}


//this is the "box" that thumbs will be created in in .thumbs.dbr folder
$thumbnailViewWidth=95;
$thumbnailViewHeight=95;
$miniViewWidth=48;
$miniViewHeight=48;
$defaultStartView='thumbnails'; //others listed here when developed

/*
//get the file if it exists, else create it
if(file_exists($dbrfile)){
	$fp=fopen($dbrfile,'w');
	$statsdbr=@fread($fp,filesize($dbrfile));
}else{
	$fp=fopen($dbrfile,'w');
}
if($set){
	//write and reset
}else{
	//read and return
}
*/

$testModeA=false; //shows iframes in ctrl section

$developerEmail='sam-git@compasspointmedia.com';
$fromHdrBugs='From: file_explorer@relatebase.com';


$fexSettings['yellowarea']=array(
	'maxarea'=>250*250,
	'shrink'=>.2
);
$fexSettings['redarea']=array(
	'maxarea'=>400*400,
	'shrink'=>.1
);
$fexSettings['fileTypeIcons']=array(
	'doc'=>'i-worddoc.png',
	'docx'=>'i-worddoc.png',
	'dot'=>'i-worddoc.png',
	'dotx'=>'i-worddoc.png',
	'xls'=>'i-exceldoc.png',
	'xlt'=>'i-exceldoc.png',
	'pdf'=>'i-pdfdoc.png',
	
	'html'=>'i-firefoxdoc.png',
	'htm'=>'i-firefoxdoc.png',
	
	'txt'=>'i-textdoc.png',
	'csv'=>'i-textdoc.png',
	'iif'=>'i-textdoc.png',
	'psd'=>'i-textdoc.png',
	'ai'=>'i-textdoc.png'
);
if(!isset($fexSettings['suppressNonFatalAlerts']))$fexSettings['suppressNonFatalAlerts']=false;

//current file; works in Linux or Windows
$a=preg_split('/(\/|\\\)/',$_SERVER['SCRIPT_FILENAME']);
$thisFile=$a[count($a)-1];

//timestamps; these two are identical numerically
$timeStamp=date('Y-m-d H:i:s');
$dateStamp=date('YmdHis', strtotime($timeStamp));

//browser detect
if(preg_match('/^Mozilla\/4/i',$HTTP_USER_AGENT)){
	$browser='IE';
}else if(preg_match('/^Mozilla\/5/i',$HTTP_USER_AGENT)){
	$browser='Moz';
}else if(!stristr($HTTP_USER_AGENT,'Gigabot') && !stristr($HTTP_USER_AGENT,'msnbot')){
	ob_start();
	print_r($GLOBALS);
	$out=ob_get_contents();
	ob_end_clean();
	//mail($developerEmail,'Unknown browser type, possible security violation past password protection!',$HTTP_USER_AGENT."\n".$out,$fromHdrBugs);
	$browser='Moz'; #assume
}


$knownFileExtensions = array(
	/* image files */
	'gif' => array( true ),
	'jpg' => array( true ),
	'jpe' => array( true ),
	'jpeg'=> array( true ),
	'png' => array( true ),
	'tiff'=> array( true ),
	'psd' => array( true ),
	'ai'  => array( true ),
	
	/* audio/video files */
	'wav'  => array( true ),
	'mpg'  => array( true ),
	'mpeg' => array( true ),
	'avi'  => array( true ),
	'wmv'  => array( true ),
	'mov'  => array( true ),

	/* web files */
	'html'=> array( true ),
	'htm' => array( true ),
	'php' => array( true ),
	'php3'=> array( true ),
	'php4'=> array( true ),
	'asp' => array( true ),
	'aspx'=> array( true ),
	'dwt' => array( true ),
	'lbi' => array( true ),

	/* ascii files */
	'txt' => array( true ),
	'iif' => array( true ),
	'css' => array( true ),
	'js'  => array( true ),
	'htaccess' => array( true ),

	/* more or less proprietary files */
	'pdf' => array( true ),
	'xls' => array( true ),
	'xlt' => array( true ),
	'doc' => array( true ),
	'docx'=> array( true ),
	'dot' => array( true ),
	'zip' => array( true ),
	'exe' => array( true )
);
$loadableFileExtensions=array(
	'gif','jpg','jpe','jpeg','png','tiff','psd','ai',
	
	'txt','iif','css', /* js files not allowed through fex, I may relax this later */
	
	'doc','docx','dot','xls','pdf',
	
	'htm','html','dwt','lbi' /* php not allowed */
);
$illegalFilenameCharacters=array("\n","\t",'/','\\','*','?',':',"'",'"');

//a request to hide known extensions will do so or unset same both here and in session, or the default can do this as well
$fexSettings['hideKnownExtensions']=1;
if(isset($hideKnownExtensions)){
	//presumed from either get or post
	$hideKnownExtensions=$_SESSION['file_explorer'][$uid]['settings']['hideKnownExtensions']=$hideKnownExtensions;
}else if(isset($fexSettings['hideKnownExtensions'])){
	$hideKnownExtensions=$_SESSION['file_explorer'][$uid]['settings']['hideKnownExtensions']=$fexSettings['hideKnownExtensions'];
}else{
	$hideKnownExtensions=$_SESSION['file_explorer'][$uid]['settings']['hideKnownExtensions'];
}

if(!isset($titleName) && preg_match('/cpm[0-9]{3}/',__FILE__,$a)){
	$titleName=$a[0];
}

/*
2008-10-30:
Here we are getting the visible items and setting them in session again - the folders are set here, and the files are set when the object is presented.  This is not the best arrangement. What I need to do is pull the filters for the files HERE, and then I don't need to set session in HTML output.

*/
//manual entry
$folderViews=array(
	'thumbnails'=>array('view-thumbnails.png', 'Thumbnail'),
	'fullfolder'=>array('view-fullfolder.png', 'Full Size'),
	'details'=>array('view-details.png', 'Details')
);
if($method==GETFOLDER_TYPEDIN) $folder=preg_replace('/^'.$rootFolderName.'\/*/i','',$folder);
if($folder){
	$node=$systemRoot.'/'.$folder;
}else{
	$node=$systemRoot;
}

/*
view gadget options added 2009-01-07 - references folderViews array in config.php
todo:
allow for max size/indicator when images are too big
have the down arrow and make clicking image = toggle through
slider for vista-like action 
*/
$config=get_folder_config($folder);

if($view=strtolower($_REQUEST['view'])){
	//OK
}else if($view=$config['view']){
	//OK
	if(!$folderViews[$view])$view=$defaultStartView;
}else{
	$view=$defaultStartView;
}
if(is_array($files) && $FEXOverrideFileArray){
	//mail($developerEmail, 'NOTICE in FEX, files already declared, is this causing a problem, file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
}else{
	if(is_dir($node)){
		$a=explode('/',$folder);
		$currentFolder=(!count($a) ? $rootFolderName : $a[count($a)-1]);
		if($files=get_file_assets($node,'normal')){
			unset($folders,$filesonly);
			$j=0;
			foreach($files as $n=>$v){
				if(isset($hiddenObjects[$n]) && $hiddenObjects[$n]<=OBJ_HIDDEN) continue;
				if($v['folder']==1){
					$j++;
					//index is lcase
					if($setSessionUIDVars['folders'])$_SESSION['file_explorer'][$uid]['folders'][$n]=$j;
					$folders[$n]=$v;
				}else{
					$filesonly[$n]=$v;
				}
			}
			//a-z folders, then a-z files
			if($sort){
				$folders=subkey_sort($folders,$sort);
				$filesonly=subkey_sort($filesonly,$sort);
			}else{
				@ksort($folders);
				@ksort($filesonly);
			}
			$folderCount=count($folders);
			$fileCount=count($filesonly);
			@$files=array_merge($folders ? $folders : array(),$filesonly ? $filesonly : array());
		}
		
		if(!is_dir($node.'/.thumbs.dbr') && !mkdir($node.'/.thumbs.dbr')){
			mail($developerEmail,'Unable to create system folder .thumbs.dbr; error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		}else{
			//move over pre-version 1.0 thumbs.dbr to .thumbs.dbr
			if(is_dir($node.'/thumbs.dbr')){
				eval( 'echo `cp --preserve -r "'.$node.'/thumbs.dbr/*" "'.$node.'/.thumbs.dbr"`;' );
				eval( 'echo `rm -r -f "'.$node.'/thumbs.dbr"`;' );
				mail($developerEmail, 'Files moved from thumbs.dbr to v1.00 .thumbs.dbr',$node,$fromHdrBugs);
			}
		}
		if(file_exists($node.'/.file_explorer.stats.dbr') && !file_exists($node.'/.thumbs.dbr/.file_explorer.stats.dbr')){
			eval( 'echo `mv "'.$node.'/.file_explorer.stats.dbr" "'.$node.'/.thumbs.dbr/.file_explorer.stats.dbr"`;' );
			mail($developerEmail, '.file_explorer.stats.dbr moved into .thumbs.dbr',$node,$fromHdrBugs);
		}
		@unlink($node.'/.file_explorer.stats.dbr');			
	}
}
