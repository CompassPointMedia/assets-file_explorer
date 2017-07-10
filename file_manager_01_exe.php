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

if(strlen($sessionid)) session_id($sessionid);
session_start();
$sessionid ? '' : $sessionid = session_id();
$bufferDocument=false;
$relatebase=false;// this is a standalone object, set to true to integrate with a login system

$localSys['scriptID']='file_manager exe page';
$localSys['build']='100';
$localSys['buildDate']='2008-11-01 08:00:00';
$localSys['buildNotes']='1.0.1 - major code cleanup';



if(false && !$uid && !$__REQUEST__['uid'] && !($PHP_SELF=='/admin/file_explorer/file_manager_01_exe.php' && strlen($_REQUEST['uid']))){
	/** each index.php window needs a unique id (UID) **/
	$uid=substr(md5(time().rand(1,25)),0,12);
	ob_start();
	print_r($GLOBALS);
	$err=ob_get_contents();
	ob_end_clean();
	mail($developerEmail, 'No FEX uid value; error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	
	?><script language="javascript" type="text/javascript">
	if(confirm('The command you just called failed to include a unique identifier string (uid). Refresh this window now?')){
		window.parent.location='index.php?uid=<?php echo $uid?>&view=<?php echo $view?>';
	}
	</script><?php
	exit;
}

$configPathReplace='file_manager_01_exe.php';
require(str_replace($configPathReplace,'',__FILE__).'config.php');

$feObject=&$_SESSION['file_explorer'][$uid];


if($mode=='ctrlFailure'){
	echo 'mailing ctrlFailure email to '.$developerEmail;
	prn($QUERY_STRING);
	mail($developerEmail,'Page not set up properly for beginSubmit() function, file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	exit;
}

//added 2011-06-30, in pre-compiling output in Juliet, this in some cases outputted exit code AND esp. this puts an ob_start() in the system which is not balanced; I'm hacking this b/c/ I really don't understand the ob_start('store_html_output') call and need to
if(!$suppressExePageShutdown){
	$assumeErrorState=true;
	register_shutdown_function('iframe_shutdown');
	ob_start('store_html_output');
}

if($mode!=='dlZip' && !$suppressPrintEnv){
	prn($QUERY_STRING);
	prn($_POST);
}

if(count($limitLoadableFileExtensions)){
	foreach($loadableFileExtensions as $v){
		if(!in_array(strtolower($v),$limitLoadableFileExtensions))unset($loadableFileExtensions[array_search($v,$loadableFileExtensions)]);
	}
}
switch(true){
	case $mode=='loadFolder':
		if(!is_dir($node)){
			if($method!=GETFOLDER_TYPEDIN){
				//alert me of an error
				mail($developerEmail, 'Error on line '.__LINE__.' of file '.__FILE__, 'In File Explorer version '.$feVersion.', a folder was selected by method='.$method.' and was not found; this is an aberror unless the folder was deleted by some other means', $fromHdrBugs);
				$msg='This is an abnormal error and dev staff have been notified; try refreshing the page the trying again.';
			}else{
				$msg='Make sure you entered a valid path.';
			}
			error_alert('Cannot find folder or file. '. $msg);
		}
		//clear session mirror of visible objects
		unset($_SESSION['file_explorer'][$uid]['index'], $_SESSION['file_explorer'][$uid]['folders']);
		
		//set a cookie of the node requested
		setcookie('lastnode',$node,time()+(60*24*3600));
		
		if($view=='details'){
			$refreshComponentOnly=true;
			require($FEX_ROOT.'/components/comp_007_filetable_v100.php');
		}else{
			require($FEX_ROOT.'/components/file_manager_02_filelist.php');
		}
		?>
		<div id="fullPath"><?php
		//show visible path for navigation
		fe_visiblefolderpath($rootFolderName, $folder);
		?></div>
		<script language="javascript" type="text/javascript">
		/** 2006-06-17: still need to do the following things:
		select any folder tree on left side if present
		**/
		window.parent.g('FileSystemFocus_1_1').innerHTML=document.getElementById('FileSystemFocus_1_1').innerHTML;
		if(window.parent.cb)window.parent.g('cbSelector').value='';
		window.parent.g('folder').value='<?php echo $folder?>';
		window.parent.currentFolder='<?php echo $currentFolder?>';
		window.parent.g('upFolder').src='i/i-upfolder.png';
		window.parent.g('upFolder').className='<?php echo $folder=='' ? 'ghost':'noghost'?>';
		window.parent.g('maxNode').value=<?php echo count($files)?>;
		
		//2008-11-01 this is now a hidden field and may be removed
		window.parent.g('currentLocation').value='<?php echo $rootFolderName . ($folder ? '/' : '') . $folder; ?>';
		window.parent.g('fullPath').innerHTML=document.getElementById('fullPath').innerHTML;
		window.parent.fileCount=<?php echo ($fileCount?$fileCount:'0')?>;
		window.parent.folderCount=<?php echo ($folderCount?$folderCount:'0')?>;
		window.parent.contextUpdater();
		
		//2009-03-13: clear selector fields if present
		if(window.parent.disposition=='selector'){
			window.parent.g('cbSelector').value='';
			window.parent.g('cbSelectorExt').value='';
		}
		
		/* ------- NOTE: see long range todo list: we have no way of initially selecting some files/folders -------- */
		window.parent.folders=new Array();
		<?php
		if(count($files)){
			$i=0;
			foreach($files as $n=>$v){
				/***
				We can safely increment i and be sure the node numbers will be the same as the session nodes which will later be assigned
				***/
				$i++;
				if(!$v['folder'])continue;
				?>window.parent.folders[<?php echo $i?>]=<?php echo $i.";\n"; 
			}
		}
		unset($a);
		$a=is_boxed($systemRoot.($folder ? '/'.$folder : ''));
		?>
		window.parent.g('boxFolder').className='<?php echo ($a ? '' : 'ghost');?>';
		window.parent.g('boxFolder').setAttribute('title','<?php
		if($a){
			echo 'This folder is boxed'.($a[0]!=='*'?', '.$a[0].' pixels wide':'').($a[1]!=='*'?', '.$a[1].' pixels high':'');
		}else{
			echo 'This folder is not boxed.  Click here to control the dimensions of files loaded to this folder';
		}
		?>');
		window.parent.g('boxDims').innerHTML='<?php
		if($a){
			?>Boxed at <?php echo $a['boundingBoxWidth']. ' by '.$a['boundingBoxHeight']?>, <?php echo $a['boxMethod']?>-wall [<a href="#" onclick="return clearBox();" title="Clear bounding box for this folder">x</a>]<?php
		}else echo '';
		?>';
		window.parent.hl_grp=new Array();
		//we presume we want to set the bounding box
		window.parent.g('passedBoundingBoxWidth').value='';
		window.parent.g('passedBoundingBoxHeight').value='';
		window.parent.g('passedBoxMethod').selectedIndex=0;
		</script><?php
		//success
		$assumeErrorState=false;
	break;
	case $mode=='uploadFile':
	case $mode=='uploadFileAPI':
		//settings and error checking
		if(rand(1,10)==5){
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals('this is not very good security and needs to be fixed, use of passedNode'),$fromHdrBugs);
		}
		if($passedNode)$node=$passedNode;

		if(!isset($runJavascript))$runJavascript=true;
		if(!is_uploaded_file($_FILES['uploadFile1']['tmp_name']))error_alert('Error loading file');

		/* version 1.0.3; on upload, bounding box logic */
		//can pass (none), *, or an int value
		$boxed=is_boxed($systemRoot.($folder ? '/'.$folder : ''));
		$passedBoundingBoxWidth=preg_replace('/[^0-9*]*/','',$passedBoundingBoxWidth);
		$passedBoundingBoxHeight=preg_replace('/[^0-9*]*/','',$passedBoundingBoxHeight);
		
		if($passedBoundingBoxWidth || $passedBoundingBoxHeight){
			if(($passedBoundingBoxWidth || $passedBoundingBoxHeight) && !$passedBoxMethod){
				error_alert('You must specify a bounding box method, or leave the bounding box fields blank');
			}
			//clean data
			if($passedBoundingBoxWidth && !$passedBoundingBoxHeight){
				$passedBoundingBoxHeight='*';
			}else if($passedBoundingBoxHeight && !$passedBoundingBoxWidth){
				$passedBoundingBoxWidth='*';
			}
			$boundingBoxWidth=$passedBoundingBoxWidth;
			$boundingBoxHeight=$passedBoundingBoxHeight;
			$boxMethod=$passedBoxMethod;
		}else if($boxed['boundingBoxWidth'] || $boxed['boundingBoxHeight']){
			//need a way to bail if the data is not correct (remote chance)
			$boundingBoxWidth=$boxed['boundingBoxWidth'];
			$boundingBoxHeight=$boxed['boundingBoxHeight'];
			$boxMethod=($boxed['boxMethod']==BOX_TWO_WALL || $boxed['boxMethod']==BOX_FOUR_WALL ? $boxed['boxMethod'] : BOX_FOUR_WALL);
		}else{
			//no bounding
		}
		//--------------------------- From 2008-10-28: Handle boxing (edited 2009-01-22) ---------------------------
		if(((strlen($boundingBoxWidth) && $boundingBoxWidth!=='*') || 
			(strlen($boundingBoxHeight) && $boundingBoxHeight!=='*')) && 
			$a=getimagesize($_FILES['uploadFile1']['tmp_name'])){
			$imagewidth=$a[0];
			$imageheight=$a[1];
			if(($boundingBoxWidth < $imagewidth && $boundingBoxWidth !== '*') || ($boundingBoxHeight < $imageheight && $boundingBoxHeight!=='*')){
				//box the image as a copy, leaving the uploaded temp file as-is. NOTE, later in dev we'll move this larger file to an "originals" folder OR a master folder with some settings so that we can consider this resize a step, and then revert back to original
				if($boxMethod==BOX_FOUR_WALL){
					if($FEXDebug)prn('creating resized image copy at '.$boundingBoxWidth.'x'.$boundingBoxHeight.', with method '.$boxMethod);
					$isBoxed = create_thumbnail(
						$_FILES['uploadFile1']['tmp_name'], 
						($boundingBoxWidth=='*' ? 100000 : $boundingBoxWidth).','.($boundingBoxHeight=='*' ? 100000 : $boundingBoxHeight),
						'',
						$_FILES['uploadFile1']['tmp_name'].'.resize'
					);
				}else if($boxMethod==BOX_TWO_WALL){
					unset($crop);
					$widthOver = $imagewidth/$boundingBoxWidth;
					$heightOver = $imageheight/$boundingBoxHeight;
					switch(true){
						case $widthOver>1.00 && $heightOver>1.00:
							//image overlaps the box completely - shrink by smallest ratio
							$shrinkratio=($widthOver > $heightOver ? 1/$heightOver : 1/$widthOver);
							if($widthOver==$heightOver){
								//image is aspect ratio same as box, no cropping will be needed
		
							}else if($widthOver>$heightOver){
								//crop the width
								$wprime=round($boundingBoxWidth/$shrinkratio);
								$cropLeft=round(($imagewidth-$wprime)/2);
								$crop=array(
									$cropLeft, /* start x */
									0, /* start y */
									$cropLeft + $wprime, /* end x */
									$imageheight /* end y */
								);
							}else{
								//crop the height
								$hprime=round($boundingBoxHeight/$shrinkratio);
								$cropLeft=round(($imageheight-$hprime)/2);
								$crop=array(
									0, /* start x */
									$cropLeft, /* start y */
									$imagewidth, /* end x */
									$cropLeft + $hprime /* end y */
								);
							}
						break;
						case $widthOver>1.00:
							//center and snip the sides of the overflow width
							$crop=array(
								$left=round(($imagewidth - $boundingBoxWidth)/2), /* start x */
								0, /* start y */
								$left+$boundingBoxWidth, /* end x */
								$imageheight /* end y */
							);
						break;
						case $heightOver>1.00:
							//center and snip the sides of the overflow height
							$crop=array(
								0, /* start y */
								$left=round(($imageheight - $boundingBoxHeight)/2), /* start x */
								$imagewidth, /* end x */
								$left+$boundingBoxHeight /* end y */
							);
						break;
						default:
							//image fits in the box, no need for any boxing
					}
					if($FEXDebug)prn('creating resized image copy at '.$boundingBoxWidth.'x'.$boundingBoxHeight.', with method '.$boxMethod);

					$b2w=create_thumbnail($_FILES['uploadFile1']['tmp_name'], 1, $crop, 'returnresource');
					$isBoxed = create_thumbnail($b2w, $boundingBoxWidth.','.$boundingBoxHeight, '',  $_FILES['uploadFile1']['tmp_name'].'.resize');
				}
			}
		}
		//-------------------------------- end handle boxing -------------------------------------


		//-------- 2009-04-13: moved this above the move_uploaded_file() - believe it belongs here ---------
		if($nameAs){
			$targetFileName=stripslashes($nameAs);
		}else{
			$targetFileName=stripslashes($_FILES['uploadFile1']['name']);
		}
		if( strlen($files[strtolower($targetFileName)]['name']) && file_exists($node.'/'.$files[strtolower($targetFileName)]['name']) ){
			//we presume they want to delete this file
			unlink($node . '/'.$files[strtolower($targetFileName)]['name']);
			$replacingFile=true;
		}

		//----------------------------- move file and original -----------------------------------
		if($isBoxed){
			if(!rename($_FILES['uploadFile1']['tmp_name'].'.resize', $node.'/'.$targetFileName)){
				if(!$fexSettings['suppressNonFatalAlerts'])error_alert('Error saving uploaded boxed file',true);
			}
			if($storeOriginalPicture && 'original is significantly bigger'){
				//save the original as stock
				rename($_FILES['uploadFile1']['tmp_name'], $node.'/.thumbs.dbr/'.$targetFileName.'.orig');
			}
		}else{
			if(!move_uploaded_file($_FILES['uploadFile1']['tmp_name'],$node.'/'.$targetFileName)){
				if(is_writable($node)){
					mail($developerEmail,'error in file '. __FILE__.', line '.__LINE__, get_globals(), $fromHdrBugs);
					error_alert('Unknown error saving uploaded file, developer notified');
				}else{
					error_alert('The folder is not writable; please have an administrator set proper permissions');
				}
			}
		}
		
		//-----------------------------------------------------------------------------------------
		if($boxFromNowOn && $boundingBoxWidth && $boundingBoxHeight && $set=is_boxed($systemRoot.($folder ? '/'.$folder : ''),'set')){
			if($mode!=='uploadFileAPI'){
				?><script language="javascript" type="text/javascript">
				window.parent.g('boxFromNowOn').checked=false;
				window.parent.g('boxDims').innerHTML='Boxed at <?php echo $set['boundingBoxWidth']. ' by '.$set['boundingBoxHeight']?>, <?php echo $set['boxMethod']?>-wall [<a href="#" onclick="return clearBox();" title="Clear bounding box for this folder">x</a>]';
				</script><?php
			}
		}
		if(!is_dir($node.'/.thumbs.dbr') && !mkdir($node.'/.thumbs.dbr')){
			mail($developerEmail,'Unable to create system folder .thumbs.dbr in '.$node,get_globals(),$fromHdrBugs);
			error_alert('Unable to create system folder .thumbs.dbr; you will not be able to see a thumbnail of a picture or some settings.  Developer staff have been notified.',true);
		}else{
			//move over pre-version 1.0 thumbs.dbr to .thumbs.dbr
			if(is_dir($node.'/thumbs.dbr')){
				eval( 'echo `cp --preserve -r "'.$node.'/thumbs.dbr/*" "'.$node.'/.thumbs.dbr"`;' );
				eval( 'echo `rm -r -f "'.$node.'/thumbs.dbr"`;' );
				mail($developerEmail, 'Files moved from thumbs.dbr to v1.00 .thumbs.dbr',$node,$fromHdrBugs);
			}
			if(file_exists($node.'/.file_explorer.stats.dbr') && !file_exists($node.'/.thumbs.dbr/.file_explorer.stats.dbr')){
				eval( 'echo `mv "'.$node.'/.file_explorer.stats.dbr" "'.$node.'/.thumbs.dbr/.file_explorer.stats.dbr"`;' );
				mail($developerEmail, 'Files moved from thumbs.dbr to v1.00 .thumbs.dbr',$node,$fromHdrBugs);
			}
			@unlink($node.'/.file_explorer.stats.dbr');			
		}
		//our normal array for the components is $v, so we declare it fully
		$v['folder']=false;
		$v['name']=$targetFileName;
		$v['actual_ext']=(strrpos($targetFileName,'.') ? substr($targetFileName,strrpos($targetFileName,'.')+1,255) : '');
		$v['ext']=strtolower($v['actual_ext']);
		$v['size']=round(filesize($node.'/'.$targetFileName)/1024,3);
		if($n=getimagesize($node.'/'.$targetFileName)){
			$v['width']=$n[0];
			$v['height']=$n[1];
		}
		if($replacingFile ){
			//delete old thumbnail
			if($a=getimagesize($node.'/'.$targetFileName)){
				$unlink=unlink($node.'/.thumbs.dbr/'.$files[strtolower($targetFileName)]['name']);
				//create the replacement thumbnail
				if(!create_thumbnail($node.'/'.$targetFileName, $thumbnailViewWidth.','.$thumbnailViewHeight, '', $node.'/.thumbs.dbr/'.$targetFileName)
				){
					error_alert('Unable to create thumbnail of this picture', true);
				}
				//this allows the image to be refreshed - not used yet
				$imgRefreshRand=rand(0,10000);
			}

			//get $i the node that needs to be refreshed
			if(!($i=$_SESSION['file_explorer'][$uid]['index'][strtolower($targetFileName)])){
				error_alert('Unable to find node to replace existing file thumbnail; please choose View > Refresh',true);
			}
		}else{
			//set vars for code block
			$i=$maxNode;
			$i++;
			if(!$v['folder'] && ($v['ext']=='jpg' || $v['ext']=='gif' || $v['ext']=='png')){
				//make a thumbnail - we could compare the touch time of the image vs. the touch time of the thumb to see if recompiling the thumb is necessary
				if(file_exists($node.'/.thumbs.dbr/'.$v['name']))unlink($node.'/.thumbs.dbr/'.$v['name']);
				$haveThumb=create_thumbnail($node.'/'.$v['name'], $thumbnailViewWidth.','.$thumbnailViewHeight, '', $node.'/.thumbs.dbr/'.$v['name']);
			}
		}
		//display the new uploaded file HTML
		?><div id="replaceHTML"><?php
		switch(true){
			case $view=='thumbnails' || $view=='fullfolder':
				//added  2009-02-08
				require($FEX_ROOT.'/components/comp_006_1.03nodelogic.php');
			break;
			default:
				//not developed
		}
		?></div><?php
		if($runJavascript){
			if($APICall){
				?><script language="javascript" type="text/javascript">
				window.parent.g('uploadFileWrap').innerHTML='<input type="file" name="uploadFile1" id="uploadFile1" onChange="uploadFile(this.value)" />';
				</script><?php
			}else{
				?><script language="javascript" type="text/javascript">
				//reset the uploader
				window.parent.g('uploadFileWrap').innerHTML='<input type="file" name="uploadFile1" id="uploadFile1" onChange="uploadFile(this.value)" />';

				<?php if($replacingFile){ ?>

				//first section
				window.parent.g('node_<?php echo $i?>').innerHTML=document.getElementById('replaceHTML').firstChild.innerHTML;

				<?php }else{ ?>

				//to do: set the object at the head of the file - may cause cascading problems
				window.parent.g('FileSystemFocus_1_1').innerHTML+=document.getElementById('replaceHTML').innerHTML;
				window.parent.g('maxNode').value=<?php echo $maxNode+1?>;
				window.parent.fileCount++;
				window.parent.contextUpdater();
				
				<?php } ?>
				</script><?php
			}
		}
		//callback for various API call pages
		$options=array();
		if(isset($cbLocation))$options['cbLocation']=$cbLocation;
		if($cbPresent && !$APICallSuppressCallback)callback($options);
	break;
	case $mode=='deleteObjects':
		/**
		NOTE: 2006-06-26: currently can only delete files, not folders
		**/
		$dels=explode('/',preg_replace('/\/$/','',trim($dels)));
		$selNodes=explode(',',preg_replace('/[,]$/','',trim($selNodes)));
		unset($dirNodes);
		foreach($dels as $idx=>$del){
			$del=preg_replace('/\.lck(\.lck)$/i','$1',$del);
			echo $idx . ":" . $del . "<br />";
			if(strlen($files[strtolower($del)]['name']) && is_dir($node.'/'.$files[strtolower($del)]['name'])){
				//for now
				$str='rm -r -f "'.$node.'/'.$files[strtolower($del)]['name'].'"';
				echo( 'echo `'. $str . '`;');
				eval( 'echo `'. $str . '`;');
				//remove any folder information from .thumbs.dbr
				$removeInnerHTMLFolders[]=$selNodes[$idx];
			}else if(strlen($files[strtolower($del)]['name']) && 
				file_exists($node.'/'.$files[strtolower($del)]['name'])
			){
				echo 'deleting '.$del.'<br />';
				$delFile=unlink($node.'/'.$files[strtolower($del)]['name']);
				//if it also exists, delete it
				$delThumb=unlink($node.'/.thumbs.dbr/'.$files[strtolower($del)]['name']);
				$removeInnerHTML[]=$selNodes[$idx];
			}else{
				continue;
			}
			
			
			unset($_SESSION['file_explorer'][$uid]['index'][strtolower($del)]);
			//folder was already deleted; this is safe because a folder and file cannot have the same name
			unset($_SESSION['file_explorer'][$uid]['folders'][strtolower($del)]);
			unset($files[strtolower($del)]);
		}
		if($removeInnerHTML){
			prn($removeInnerHTML);
			?><script language="javascript" type="text/javascript"><?php
			foreach($removeInnerHTML as $v){
				//clear out the hl_grp array element
				$filesDeleted++;
				?>
				try{
					window.parent.unset(<?php echo $v?>,'hl_grp');
				}
				catch(e){ }
				try{ 
					window.parent.g('node_<?php echo $v?>').style.display='none';
					window.parent.g('node_<?php echo $v?>').setAttribute('id',null);
					window.parent.g('node_<?php echo $v?>').outerHTML='';
				}
				catch(e){ }<?php
			}
			?>
			//update file count
			window.parent.fileCount=<?php echo ($fileCount-$filesDeleted > 0 ? $fileCount - $filesDeleted :'0')?>;
			window.parent.contextUpdater();
			</script><?php
		}
		if($removeInnerHTMLFolders){
			prn($removeInnerHTMLFolders);
			?><script language="javascript" type="text/javascript"><?php
			foreach($removeInnerHTMLFolders as $v){
				//clear out the hl_grp array element
				$foldersDeleted++;
				?>
				try{
					window.parent.unset(<?php echo $v?>,'hl_grp'); 
				}catch(e){ }
				try{
					window.parent.unset(<?php echo $v?>,'folders');
				}catch(e){ }
				try{
					window.parent.g('node_<?php echo $v?>').style.display='none';
					window.parent.g('node_<?php echo $v?>').setAttribute('id',null);
					//window.parent.g('node_<?php echo $v?>').outerHTML=''; = was not working
				}catch(e){ }
				<?php
			}
			?>
			//update folder count
			window.parent.folderCount=<?php echo ($folderCount-$foldersDeleted > 0 ? $folderCount - $foldersDeleted :'0')?>;
			window.parent.contextUpdater();
			</script><?php
		}
		//final cleanup
		?><script language="javascript" type="text/javascript">
		window.parent.g('dels').value='';
		window.parent.g('sels').value='';
		window.parent.g('selNodes').value='';
		</script><?php
	break;
	case $mode=='dlZip':
		/****
		see todo on this for helpful attributes of the zip file such as a readme file
		
		****/
		function add_dir_to_zip($zipfile, $originalNode, $folder){
			if(!($fp=@opendir($originalNode.'/'.$folder))) return; //abnormal
			$zipfile->add_dir($folder.'/');
			while(false!==($f=readdir($fp))){
				if($f=='.' || $f=='..') continue;
				if(is_dir($originalNode.'/'.$folder.'/'.$f)){
					if(strtolower($f)=='.thumbs.dbr') continue;
					add_dir_to_zip($zipfile, $originalNode, $folder.'/'.$f);
				}else{
					ob_start();
					@readfile($originalNode.'/'.$folder.'/'.$f);
					$filedata=ob_get_contents();
					ob_end_clean();
					$zipfile->add_file($filedata, $folder.'/'.$f);
				}
			}
		}
		
		
		require($FEX_ROOT.'/components/zipfile.inc.php');
		if($sels=explode('/',preg_replace('/\/*$/','',strtolower($sels)))){
			$zipfile= new zipfile();
			foreach($sels as $idx){
				$thisFile=$files[strtolower($idx)]['name'];
				//pass over unrecognized or system files or folders
				/**
				2006-06-27: this needs to be improved, for now we just skip .thumbs.dbr
				**/
				if(!trim($thisFile) || strtolower($thisFile)=='.thumbs.dbr') continue;
				if(is_dir($node.'/'.$thisFile) && trim($thisFile)){
					$originalNode=$node;
					$folder=$thisFile;
					add_dir_to_zip($zipfile, $originalNode, $folder);
				}else if(file_exists($node.'/'.$thisFile)){
					ob_start();
					@readfile($node.'/'.$thisFile);
					$filedata=ob_get_contents();
					ob_end_clean();
					$zipfile->add_file($filedata, $thisFile);
				}
			}
		}
		header("Content-type: application/octet-stream");
		header("Content-disposition: attachment; filename=ZipFile_".date('Ymd_His').'_'.count($sels).".zip");
		echo $zipfile->file();
		$assumeErrorState=false;
		exit;
	break;
	case $mode=='newFolder':
		//we do this according to view 

		//get the node of the new folder
		#this will become an autoincrement function eventually
		$untitledFolderName='untitled folder';
		$maxInc='';
		$nodeMax='';
		foreach($_SESSION['file_explorer'][$uid]['index'] as $n=>$v){
			if($v>$nodeMax)$nodeMax=$v;
			if(preg_match('/^'.$untitledFolderName.'(.*)/i',$n, $a)){
				if(preg_match('/^[0-9]*$/',$a[1])){
					$thismax=trim($a[1])+1; //integer
					if($thismax>$maxInc)$maxInc=$thismax;
				}
			}
		}
		$newFolderName=$untitledFolderName.$maxInc;
		//create the folder
		if(mkdir($node.'/'.$newFolderName)){
			unset($v);
			$v['name']=$newFolderName;
			$v['folder']=1;
			$i=$nodeMax+1;
			$displayNameAsInput=true;
			//set the folder in session
			$_SESSION['file_explorer'][$uid]['index'][strtolower($newFolderName)]=$i;
			prn($_SESSION['file_explorer'][$uid]);
			$_SESSION['file_explorer'][$uid]['folders'][strtolower($newFolderName)]=max($_SESSION['file_explorer'][$uid]['folders'])+1;
			
			//update counts
			?><script language="javascript" type="text/javascript">
			window.parent.folderCount++;
			window.parent.contextUpdater();
			</script><?php
			
			?><div id="newFolder"><?php
			require($FEX_ROOT.'/components/comp_006_1.03nodelogic.php');
			?></div>
			<script language="javascript" type="text/javascript">
			window.parent.g('FileSystemFocus_1_1').innerHTML+=document.getElementById('newFolder').innerHTML;
			window.parent.folders[<?php echo $i?>]=<?php echo $i?>;
			window.parent.hi2(<?php echo $i?>,1,0 /*,event*/);
			window.parent.g('iname_<?php echo $i?>').focus();
			window.parent.g('iname_<?php echo $i?>').select();
			</script>
			<?php
		}else{
			error_alert('Unable to create folder '. $node.'/'.$newFolderName.'.  Check permissions with the site administrator');
		}
		
		$assumeErrorState=false;
		exit;
	break;
	case $mode=='nameobject':
		//error checking
		//error_alert($_SESSION['file_explorer'][$uid]['index'][strtolower($origObjName)] . ':' . $idx);
		//verify session synch
		if($_SESSION['file_explorer'][$uid]['index'][strtolower($origObjName).(strlen($origObjExt) ? '.'.strtolower($origObjExt) : '')]!=$idx){
			//note 0400918
			mail($developerEmail,'error line '.__LINE__.', file '.__FILE__,get_globals(),$fromHdrBugs);
			?><script language="javascript" type="text/javascript">
			if(confirm('You may have lost your session or another abnormal error; this file or folder name was not changed.  This is normally resolved by refreshing the page; would you like to reload the page now?')){
				window.parent.location='index.php?uid=<?php echo $uid?>&hideKnownExtensions=<?php echo $hideKnownExtensions?>&view=<?php echo $view?>&folder='+window.parent.g('folder').value;
			}else{
				window.parent.g('name_<?php echo $idx?>').innerHTML=window.parent.g('origObjName').value;
			}
			</script><?php
			$assumeErrorState=false;
			exit;
		}
		if(!$origObjName) error_alert('Abnormal error; variable origObjName not passed in query, notify developer');
		if(!$formNode) error_alert('Abnormal error; variable formNode not passed in query, notify developer');

		if(isset($origObjExt)){
			if(!in_array(strtolower($origObjExt),$loadableFileExtensions)){
				mail($developerEmail,'error line '.__LINE__.', file '.__FILE__,get_globals(),$fromHdrBugs);
				error_alert('Abnormal error; passed file extension is not valid.  Developer has been notified');
			}
			//no change in ext case
			$objName.='.'.$origObjExt;
			$origObjName.='.'.$origObjExt;
		}else{
			//verify extension is OK
			if(!is_dir($formNode . '/'.stripslashes($origObjName)) && preg_match('/\.([^.]+)$/',$objName,$a)){
				if(strlen($a[1])<5 && !in_array(strtolower($a[1]),$loadableFileExtensions)){
					mail($developerEmail,'error line '.__LINE__.', file '.__FILE__,get_globals(),$fromHdrBugs);
					error_alert('You cannot upload a file with extension '.strtolower($a[1]).'.  Contact developer for assistance');
				}
			}
		}
		for($i=0; $i<=strlen(stripslashes($objName)); $i++){
			if(in_array(substr(stripslashes($objName),$i,1),$illegalFilenameCharacters)){
				unset($illegalFilenameCharacters[0],$illegalFilenameCharacters[1]);
				$err='Your file name cannot contain the following characters: '.implode(' ',$illegalFilenameCharacters).' or a tab character';
				break;
			}
		}
		if(!strlen(stripslashes($objName)) || $err){
			?><script language="javascript" type="text/javascript">
			window.parent.focus();
			window.parent.alert('<?php echo $err ? str_replace("'","\'",$err) : 'You cannot enter a blank name for a file';?>');
			window.parent.g('iname_<?php echo $idx?>').focus();
			window.parent.g('iname_<?php echo $idx?>').select();
			</script><?php
			$assumeErrorState=false;
			exit;
		}
		foreach($files as $n=>$v){
			if($v['name']==stripslashes($objName)){
				?><script language="javascript" type="text/javascript">
				window.parent.focus();
				window.parent.alert('That name is already used');
				window.parent.g('iname_<?php echo $idx?>').focus();
				window.parent.g('iname_<?php echo $idx?>').select();
				</script><?php
				$assumeErrorState=false;
				exit;
			}
		}
		ob_start();
		rename(stripslashes($formNode).'/'.stripslashes($origObjName), stripslashes($formNode).'/'.stripslashes($objName));
		$out=ob_get_contents();
		ob_end_clean();
		prn($out);
		if(!$out){
			unset($_SESSION['file_explorer'][$uid]['index'][strtolower(stripslashes($origObjName))]);
			$_SESSION['file_explorer'][$uid]['index'][strtolower(stripslashes($objName))]=$idx;
			foreach($_SESSION['file_explorer'][$uid]['folders'] as $n=>$v){
				if(strtolower($n)==strtolower(stripslashes($origObjName))){
					if($v!=$idx) mail($developerEmail,'error line '.__LINE__.', file '.__FILE__,get_globals(),$fromHdrBugs);
					unset($_SESSION['file_explorer'][$uid]['folders'][$n]);
					$_SESSION['file_explorer'][$uid]['folders'][stripslashes($objName)]=$idx;
					break;
				}
			}
			
			//rename the thumbnail
			if(file_exists(stripslashes($formNode).'/.thumbs.dbr/'.stripslashes($origObjName))){
				echo rename(stripslashes($formNode).'/.thumbs.dbr/'.stripslashes($origObjName), stripslashes($formNode).'/.thumbs.dbr/'.stripslashes($objName));
			}
			?>
			<div id="replaceHTML" title="<?php echo h(stripslashes($origObjExt ? $_POST['objName']/* as passed */ : $objName /* as modified w/ext */))?>"><?php echo h(stripslashes($origObjExt ? $_POST['objName']/* as passed */ : $objName /* as modified w/ext */))?></div>
			<script language="javascript" type="text/javascript">
			window.parent.g('name_<?php echo $idx?>').setAttribute('title',document.getElementById('replaceHTML').getAttribute('title'));;
			window.parent.g('name_<?php echo $idx?>').innerHTML=document.getElementById('replaceHTML').innerHTML;
			</script>
			<?php
		}else{
			echo $out;
			?><span id="replaceHTML"><?php echo stripslashes($origObjName)?></span>
			<script defer="defer" language="javascript">
			window.parent.g('iname_<?php echo $idx?>').value='<?php echo stripslashes($origObjName)?>';
			alert('Unable to rename the file or folder, either 1.the name was not unique, 2. the object was already renamed or 3. due to a permissions issue - <?php echo addslashes(str_replace("\n",' - ',$out))?>');
			window.parent.g('iname_<?php echo $idx?>').focus();
			window.parent.g('iname_<?php echo $idx?>').select();
			</script><?php
		}
		$assumeErrorState=false;
	break;
	case $mode=='preprename': 
		$i=$idx;
		unset($v);
		foreach($_SESSION['file_explorer'][$uid]['index'] as $o=>$w){
			if($w==$i){
				if($v=$files[strtolower($o)]){
					//OK
					break;
				}
			}
		}
		if(!$v['name']){
			mail($developerEmail,'FEX error line '.__LINE__.', file '.__FILE__,get_globals(),$fromHdrBugs);
			?><script language="javascript" type="text/javascript">
			if(confirm('Unable to find that file or folder; it may have been deleted or renamed by another user.  Would you like to reload this folder?')){
				alert('sorry, this is undeveloped but the developer has been mailed - thank you');
			}
			</script><?php
			$assumeErrorState=false;
			exit;
		}
		?><div id="replaceHTML"><?php
		require($FEX_ROOT.'/components/comp_002_objectNameForm_v100.php');
		?></div>
		<script language="javascript" type="text/javascript">
		window.parent.hi2(<?php echo $i?>,1,0 /*,event*/);
		window.parent.g('name_<?php echo $i?>').innerHTML=document.getElementById('replaceHTML').innerHTML;
		window.parent.g('iname_<?php echo $i?>').focus();
		window.parent.g('iname_<?php echo $i?>').select();
		</script>
		<?php
		$assumeErrorState=false;
	break;
	case $mode=='ccp_copy':
	case $mode=='ccp_copymore':
	case $mode=='ccp_cut':
	case $mode=='ccp_cutmore':
		$a=$_SESSION['file_explorer'][$uid]['history']['ccp'];
		$idx=count($a);
		//add next history step
		if(!$idx)$idx=1;
		if($a[$idx]['completed']) $idx++;
		if(!$a[$idx]['startTime']) $a[$idx]['startTime']=time();
		$sels=explode('/',$sels);
		if($mode=='ccp_copy' || $mode=='ccp_cut'){
			//clear objects storage, start again
			$a[$idx]['objects']=array();
			$a[$idx]['startTime']=time();
			foreach($sels as $file){
				if(!trim($file))continue;
				$path=$documentRoot.'/'.$currentLocation . '/'.$file;
				$a[$idx]['objects'][$path.(is_dir($path)?'/*':'')] = ($mode=='ccp_copy' ? 'copy' : 'cut');
			}
			$copyMode=$a[$idx]['copyMode']=($mode=='ccp_copy' ? CCP_COPY : CCP_CUT);
		}else{
			foreach($sels as $file){
				if(!trim($file))continue;
				$path=$documentRoot.'/'.$currentLocation . '/'.$file;
				$a[$idx]['objects'][$path.(is_dir($path)?'/*':'')] = ($mode=='ccp_copymore' ? 'copy' : 'cut');
			}
			$copyMode=$a[$idx]['copyMode']=bitwise_op($a[$idx]['copyMode'], ($mode=='ccp_copymore' ? CCP_COPYMORE : CCP_CUTMORE), 'write');
		}
		prn($a);
		$_SESSION['file_explorer'][$uid]['history']['ccp'][$idx]=$a[$idx];
		//notify page objects that a paste option is available
		?><script language="javascript" type="text/javascript">
		window.parent.copyStatus=true;
		window.parent.copyMode=<?php echo $copyMode?>;
		window.parent.copyCount='<?php echo count($a[$idx]['objects'])?>';
		</script><?php
		//now ghost the cut objects
		?><script language="javascript" type="text/javascript"><?php
		if(count($a[$idx]['objects']) && count($feObject['index'])){
			foreach($feObject['index'] as $file=>$idx2){
				$actual = strtolower($documentRoot.'/'.$currentLocation . '/'.$file);
				echo '//'.$actual . "\n";
				$iscut=false;
				foreach($a[$idx]['objects'] as $path=>$cut){
					echo '//--'.$path . "\n";
					if(strtolower(preg_replace('/\/\*$/','',$path))==$actual && $cut=='cut'){
						//ghost the folder
						$iscut=true;
						?>
						try{
						<?php if($view=='details'){ ?>
						var cn=window.parent.g('node_<?php echo $idx2; ?>').className;
						window.parent.g('node_<?php echo $idx2; ?>').className=cn.replace(/\s*cut/,'')+' cut';
						<?php }else{ ?>
						window.parent.g('node_<?php echo $idx2; ?>').className="<?php echo $view=='fullfolder'?'vwF':'vwT'?> cut";
						<?php } ?>
						}catch(e){ }
						<?php
					}
				}
				if(!$iscut && ($mode=='ccp_cut' || $mode=='ccp_copy')){
					?>
					try{
					<?php if($view=='details'){ ?>
					var cn=window.parent.g('node_<?php echo $idx2; ?>').className;
					window.parent.g('node_<?php echo $idx2; ?>').className=cn.replace(/\s*cut/,'');
					<?php }else{ ?>
					window.parent.g('node_<?php echo $idx2; ?>').className="<?php echo $view=='fullfolder'?'vwF':'vwT'?>";
					<?php } ?>
					}catch(e){ }
					<?php
				}
			}
		}
		?></script><?php
	break;
	case $mode=='ccp_paste':
		//this is version 0.1 - rely on system, nothing else done
		$history=$feObject['history']['ccp'];
		if(!count($history) || $history[count($history)]['completed']){
			//there was nothing to paste
			mail($developerEmail,'Error file '. __FILE__.', line '.__LINE__, get_globals(),$fromHdrBugs);
		}else{
			?><div id="replaceHTML"><?php
			$history=$history[count($history)];
			krsort($history);
			$here=$documentRoot . '/' . $currentLocation;
			if($pasteOption=='ccp05a'){
				//isoloate to last folder
				$folder=explode('/',rtrim($sels,'/'));
				$folder=$folder[count($folder)-1];
				if(is_dir($here.'/'.$folder)){
					$norefresh=true;
					$here.='/'.$folder; //pasted into subfolder
				}
			}
			foreach($history['objects'] as $fullpath=>$action){
				if(preg_match('/\/\*$/',$fullpath)){
					echo 'Folder:<br />';
					//folder - it will move if unix command is valid
					$command=($action=='cut'?'mv ':'cp --preserve -r');
					prn( '`'.$command.' "'. preg_replace('/\/\*$/','',$fullpath).'" "'.$here.'"`;');
					ob_start();
					eval( 'echo `'.$command.' "'. preg_replace('/\/\*$/','',$fullpath).'" "'.$here.'"`;');
					$err=ob_get_contents();
					ob_end_clean();
					if($err){
						prn($err);
					}
				}else{
					echo 'File:<br />';
					$command=($action=='cut'?'mv ':'cp --preserve');
					$a=explode('/',$fullpath);
					$file=array_pop($a);
					//file - get it and .thumbs.dbr thumbnail
					prn('echo `'.$command.' "'.$fullpath.'" "'.$here.'/'.$file.'"`;');
					prn('echo `'.$command. ' "'.implode('/',$a).'/.thumbs.dbr/'.$file.'" "'.$here.'/.thumbs.dbr/'.$file.'"`;');
					ob_start();
					eval('echo `'.$command.' "'.$fullpath.'" "'.$here.'/'.$file.'"`;');
					if(!is_dir($here.'/.thumbs.dbr') && !mkdir($here.'/.thumbs.dbr'))error_alert('Unable to create system folder .thumbs.dbr');
					//2008-05-06: adjust box sizing
					$isBoxed=false;
					mail($developerEmail,'here',$here.':'.get_globals(),$fromHdrBugs);
					if(!isset($boxed))$boxed=is_boxed($here);
					if($boxed && $a=getimagesize($here.'/'.$file)){
						if(($boxed[0] < $a[0] && $boxed[0] !== '*') || ($boxed[1] < $a[1] && $boxed[1]!=='*')){
							$isBoxed = create_thumbnail($here.'/'.$file, $boxed[0].','.$boxed[1], '', $here.'/'.$file);
						}
					}
					//copy the thumbnail
					eval('echo `'.$command. ' "'.implode('/',$a).'/.thumbs.dbr/'.$file.'" "'.$here.'/.thumbs.dbr/'.$file.'"`;');
					//copy any .orig files
					eval('echo `'.$command. ' "'.implode('/',$a).'/.thumbs.dbr/'.$file.'.orig" "'.$here.'/.thumbs.dbr/'.$file.'.orig"`;');
					$err=ob_get_contents();
					ob_end_clean();
					if($err){
						prn($err);
					}
				}
			}
			//close out the session object
			$feObject['history']['ccp'][count($feObject['history']['ccp'])]['completed']=time();
			$feObject['history']['ccp'][count($feObject['history']['ccp'])]['pasteLocation']=$here;
			?></div><?php
		}
		?><script language="javascript" type="text/javascript">
		window.parent.copyStatus=false;
		window.parent.copyMode=0;
		window.parent.copyCount=0;
		if(<?php echo !$norefresh?'true':'false'?>)window.parent.obj_refresh();
		</script><?php
	break;
	case $mode=='clearboxedfolder':
	case $mode=='boxedfolder':
		$statsdbr=@implode("\n",file($systemRoot.'/.thumbs.dbr/.file_explorer.stats.dbr'));
		$boxed=xml_read_tags($statsdbr, 'boxedfolder', $attrib='', $return=XML_RET_INNER);
		$newBoxed=array();
		$boxed=trim($boxed);
		$a=explode("\n",$boxed);
		$newBoxed=array();
		foreach($a as $v){
			if(!trim($v))continue;
			$b=explode("\t",$v);
			if(strtolower($b[0])==strtolower($systemRoot .($folder?'/':'').$folder)){
				$haveRow=true;
				if($mode=='clearboxedfolder'){
					continue;
				}else{
					$newBoxed[]=$systemRoot.($folder?'/':'').$folder."\t".
					($width=='' ? '*': $width)."\t".($height=='' ? '*': $height);
				}
			}else{
				$newBoxed[]=$v;
			}
		}
		if(!$haveRow && $mode!=='clearboxedfolder')
		$newBoxed[]=$systemRoot.($folder?'/':'').$folder."\t".($width=='' ? '*': $width)."\t".($height=='' ? '*': $height);
		prn($newBoxed);
		

		//matrix, we have the file 1|0, have the block 1|0
		if($statsdbr){
			if($boxed){
				//replace region
				$start=strpos($statsdbr,'<boxedfolder');
				$length=strpos($statsdbr,'</boxedfolder>')+14 - $start;
				$str='<boxedfolder version="1.0">'."\n" . implode("\n",$newBoxed)."\n".'</boxedfolder>';
				$statsdbr=substr_replace($statsdbr,$str,$start,$length);
				$assumeErrorState=false;
				prn($statsdbr);
				$fp=fopen($systemRoot.'/.thumbs.dbr/.file_explorer.stats.dbr','w');
				fwrite($fp,$statsdbr,strlen($statsdbr));			
			}else if(strlen($newBoxed)){
				//insert region at the end
				$str="\n";
				$str.='<boxedfolder version="1.0">'."\n" . implode("\n",$newBoxed)."\n".'</boxedfolder>';
				prn($str);
				$fp=fopen($systemRoot.'/.thumbs.dbr/.file_explorer.stats.dbr','a');
				fwrite($fp,$str,strlen($str));				
			}
		}else if(strlen($newBoxed)){
			//create the file
			$fp=fopen($systemRoot.'/.thumbs.dbr/.file_explorer.stats.dbr','w');
			$str='#file created '.date('m/d/Y H:i:s').' by file explorer version '.$feVer.', line '.__LINE__.' of file_manager_01.exe'."\n";
			$str.='<boxedfolder version="1.0">'."\n" . implode("\n",$newBoxed).'</boxedfolder>';
			fwrite($fp,$str,strlen($str));
		}
		?><script language="javascript" type="text/javascript">
		if(window.parent.opener.g('folder').value=='<?php echo $folder?>'){
			window.parent.opener.g('boxFolder').src='<?php echo $mode=='clearboxedfolder' ? 'i/box-ghost.gif' : 'i/box.gif';?>';
		}
		var s=window.parent.location;
		if(!s.match('testModeC=1'))window.parent.close();
		</script><?php
	break;
	case $mode=='refreshComponent':
		$components['fullFileWrap']=array($FEX_ROOT.'/components/comp_006_1.03nodelogic.php');
		?><div id="newContent"><?php
		require( $components[$component][0] );
		?></div>
		<script language="javascript" type="text/javascript">
		window.parent.g('<?php echo $component?>').innerHTML=document.getElementById('newContent').innerHTML;
		</script><?php
	break;
	case $mode=='emailEmergency':
		mail($developerEmail,'FEX error or notice: '.$msg,get_globals(),$fromHdrBugs);
	break;
	case $mode=='downloadToComputer':
		$filename=stripslashes($filename);
		$idx=$feObject['index'][strtolower($filename)];
		if($idx!=$node){
			mail($developerEmail,'node-filename mismatch file_manager_01_exe.php',get_globals(),$fromHdrBugs);
		}
		if(!($filename=$files[strtolower($filename)]['name'])){
			//notice only, this is not necessarily an error
			mail($developerEmail,'filename not found file_manager_01_exe.php line '.__LINE__,get_globals(),$fromHdrBugs);
			error_alert('Unable to find file; it may have been deleted by another user');
		}
		$assumeErrorState=false;
		$suppressNormalIframeShutdownJS=true;

		header("Content-Type: application/octet-stream");
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		echo readfile($node.'/'.$filename);
		exit;

		//attach_download($node.'/'.$filename,'',$filename);
	break;
	case $mode=='clearBox':
		if(is_boxed($node,'clear')){
			?><script language="javascript" type="text/javascript">
			window.parent.g('boxDims').innerHTML='';
			</script><?php
		}
	break;
	case $mode=='createCopyResized':
		//this is an API call and does not return HTML output
		/*
		what file (must be an image/jpg/png/gif), where, what resize, what information do we store about the event; return true and information, or false and notice/warnings
		example:
		--------
		$uid=true;
		$suppressPrintEnv=1;
		$mode='createCopyResized';
		$passedBoundingBoxWidth=250;
		$passedBoundingBoxWidth=175;
		$sourceNode='../../client/protected'; //reckoned from the file making this call
		$sourceFile='sample.jpg';
		$targetNode=''; //if left blank will be same as sourceNode
		$targetFile='sample(resized to 250x175).jpg';
		require($FEX_ROOT.'/file_manager_01_exe.php');
		//do something with/after return values
		..
		
		*/
		unset($FEX[$mode],$eq,$isBoxed); //initial conditions
		for($i=1;$i<=1;$i++){ //-------- begin break loop ----------
			if(!$targetNode){
				$targetNode=$sourceNode;
				error_alert($targetNode);
				$eq++;
			}
			if(!is_dir($targetNode) && !mkdir($targetNode)){
				mail($developerEmail, 'Unable to create targetNode directory in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			}
			if(!$targetFile){
				$targetFile=$sourceFile;
				$eq++;
			}
			$files=get_file_assets($targetNode);
			switch(true){
				case $eq==2:
					$FEX[$mode]['errors'][]='you cannot copy a file to itself!';
					break(2);
				case !strlen($sourceFile):
					$FEX[$mode]['errors'][]='the source file variable is not present or is blank ($sourceFile)';
					break(2);
				case !file_exists($sourceNode.'/'.$sourceFile):
					$FEX[$mode]['errors'][]='the source file does not exist';
					break(2);
				case !($gis=getimagesize($sourceNode.'/'.$sourceFile)) || !preg_match('/\.(gif|jpg|png)$/i',substr($sourceFile,-4)):
					$FEX[$mode]['errors'][]='the source file is not a resizable image (gif, png, jpg)';
					break(2);
				case $files[strtolower($targetFile)]:
					if(!$overwriteExistingTarget){
						$FEX[$mode]['notices'][]='an existing file was present and was set to be overwritten';
					}else{
						$FEX[$mode]['errors'][]='the target file already exists; use $overwriteExistingTarget=true to overwrite';
						break(2);
					}
				break;
			}
		
			if($passedBoundingBoxWidth || $passedBoundingBoxHeight){
				if(($passedBoundingBoxWidth || $passedBoundingBoxHeight) && !$passedBoxMethod){
					$passedBoxMethod=BOX_TWO_WALL;
					$FEX[$mode]['notices'][]='You must specify a bounding box method.  BOX_TWO_WALL was selected as the default';
				}
				//clean data
				if($passedBoundingBoxWidth && !$passedBoundingBoxHeight)$passedBoundingBoxHeight='*';
				if($passedBoundingBoxHeight && !$passedBoundingBoxWidth)$passedBoundingBoxWidth='*';

				$boundingBoxWidth=$passedBoundingBoxWidth;
				$boundingBoxHeight=$passedBoundingBoxHeight;
				$boxMethod=$passedBoxMethod;
			}else if($useExistingFolderBoxing){
				$boxed=is_boxed($sourceNode.'/'.$sourceFile);
				if($boxed['boundingBoxWidth'] || $boxed['boundingBoxHeight']){
					//need a way to bail if the data is not correct (remote chance)
					$boundingBoxWidth=$boxed['boundingBoxWidth'];
					$boundingBoxHeight=$boxed['boundingBoxHeight'];
					$boxMethod=($boxed['boxMethod']==BOX_TWO_WALL || $boxed['boxMethod']==BOX_FOUR_WALL ? $boxed['boxMethod'] : BOX_FOUR_WALL);
				}else{
					$FEX[$mode]['errors'][]='No bounding box information for this folder';
					break;
				}
			}else{
				//no bounding information
				$FEX[$mode]['errors'][]=
				'You did not pass bounding box information.  Either pass $asdf and $asdf, or pass $asdf to use the boxing of the target location';
				break;
			}
			//--------------------------- From 2008-10-28: Handle boxing (edited 2009-01-22) ---------------------------
			if(((strlen($boundingBoxWidth) && $boundingBoxWidth!=='*') || 
				(strlen($boundingBoxHeight) && $boundingBoxHeight!=='*'))
			   ){
				$imagewidth=$gis[0];
				$imageheight=$gis[1];
				if(($boundingBoxWidth < $imagewidth && $boundingBoxWidth !== '*') || ($boundingBoxHeight < $imageheight && $boundingBoxHeight!=='*')){
					//box the image as a copy, leaving the uploaded temp file as-is. NOTE, later in dev we'll move this larger file to an "originals" folder OR a master folder with some settings so that we can consider this resize a step, and then revert back to original
					if($boxMethod==BOX_FOUR_WALL){
						if($FEXDebug)$FEX[$mode]['notices'][]='creating resized image copy at '.$boundingBoxWidth.'x'.$boundingBoxHeight.', with method '.$boxMethod;
						$isBoxed = create_thumbnail(
							$sourceNode . '/' . $sourceFile, 
							($boundingBoxWidth=='*' ? 100000 : $boundingBoxWidth).','.($boundingBoxHeight=='*' ? 100000 : $boundingBoxHeight),
							'',
							$targetNode . '/' . $targetFile
						);
					}else if($boxMethod==BOX_TWO_WALL){
						unset($crop);
						$widthOver = $imagewidth/$boundingBoxWidth;
						$heightOver = $imageheight/$boundingBoxHeight;
						switch(true){
							case $widthOver>1.00 && $heightOver>1.00:
								//image overlaps the box completely - shrink by smallest ratio
								$shrinkratio=($widthOver > $heightOver ? 1/$heightOver : 1/$widthOver);
								if($widthOver==$heightOver){
									//image is aspect ratio same as box, no cropping will be needed
			
								}else if($widthOver>$heightOver){
									//crop the width
									$wprime=round($boundingBoxWidth/$shrinkratio);
									$cropLeft=round(($imagewidth-$wprime)/2);
									$crop=array(
										$cropLeft, /* start x */
										0, /* start y */
										$cropLeft + $wprime, /* end x */
										$imageheight /* end y */
									);
								}else{
									//crop the height
									$hprime=round($boundingBoxHeight/$shrinkratio);
									$cropLeft=round(($imageheight-$hprime)/2);
									$crop=array(
										0, /* start x */
										$cropLeft, /* start y */
										$imagewidth, /* end x */
										$cropLeft + $hprime /* end y */
									);
								}
							break;
							case $widthOver>1.00:
								//center and snip the sides of the overflow width
								$crop=array(
									$left=round(($imagewidth - $boundingBoxWidth)/2), /* start x */
									0, /* start y */
									$left+$boundingBoxWidth, /* end x */
									$imageheight /* end y */
								);
							break;
							case $heightOver>1.00:
								//center and snip the sides of the overflow height
								$crop=array(
									0, /* start y */
									$left=round(($imageheight - $boundingBoxHeight)/2), /* start x */
									$imagewidth, /* end x */
									$left+$boundingBoxHeight /* end y */
								);
							break;
							default:
								//image fits in the box, no need for any boxing
						}
						if($FEXDebug)$FEX[$mode]['notices'][]=('creating resized image copy at '.$boundingBoxWidth.'x'.$boundingBoxHeight.', with method '.$boxMethod);
	
						$b2w=create_thumbnail($sourceNode.'/'.$sourceFile, 1, $crop, 'returnresource');
						$isBoxed = create_thumbnail($b2w, $boundingBoxWidth.','.$boundingBoxHeight, '', $targetNode.'/'.$targetFile);
					}
				}
			}
			//-------------------------------- end handle boxing -------------------------------------
			if(!$isBoxed)$FEX[$mode]['errors']='the thumbnail was apparently unable to be created';
		} //---------------------------- end break loop   ----------
		if($FEX[$mode]['errors']){
			//handle
		}else if($FEX[$mode]['notices']){
			//handle
		}
	break;
	case $mode=='tag':
		foreach($id as $n=>$Tree_ID){
			if(!$chge[$n])continue;
			q("DELETE FROM relatebase_ObjectsTree WHERE ObjectName=':jointitle' AND Tree_ID='$Tree_ID'");
			q("INSERT INTO relatebase_ObjectsTree SET ObjectName=':jointitle', Tree_ID='$Tree_ID', CreateDate=NOW(), Creator='system', Title='".$tag[$n]."'");
			$updated++;
		}
		error_alert('Updated '.$updated);
	break;
	case $mode=='listAdder':
		if($submode=='imageTags'){
			if($subsubmode=='deleteTag'){
				$result=q("DELETE FROM relatebase_ObjectsTree WHERE
				ObjectName='gen_tags' AND
				Objects_ID='".preg_replace('/[^0-9]/','',$Tags_ID)."' AND
				Tree_ID='".preg_replace('/[^0-9]+/','',$bindTo)."'");
			}else{
				if($Tags_ID){
					//OK
				}else{
					$Tags_ID=q("INSERT INTO gen_tags SET Name='$Name'", O_INSERTID);
				}
				if($bindTo){
					$result=q("INSERT INTO relatebase_ObjectsTree SET
					ObjectName='gen_tags',
					Objects_ID='".preg_replace('/[^0-9]/','',$Tags_ID)."',
					Tree_ID='".preg_replace('/[^0-9]+/','',$bindTo)."',
					CreateDate=NOW()");
				}
			}
			echo json_encode(array(
				'OK'=>($result ? true : false),
				'Tags_ID'=>$Tags_ID,
			));
		}
		$assumeErrorState=false;
		$suppressNormalIframeShutdownJS=true;
		exit;
	break;
	case $mode=='listBuilder':
		$letters = preg_replace("/[^a-z0-9 ]/si","",$letters);
		if($submode=='getImageTagsByLetters'){
			if($res=q("SELECT ID, Name FROM gen_tags WHERE Name LIKE '".$letters."%' ORDER BY Name", O_ARRAY_ASSOC)){
				echo json_encode($res);
			}
		}
		$assumeErrorState=false;
		$suppressNormalIframeShutdownJS=true;
		exit;
	break;
	default:
		error_alert('No mode passed');	
}
$assumeErrorState=false;
