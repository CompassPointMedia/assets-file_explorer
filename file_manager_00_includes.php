<?php 
/* image upload manager v0.9 -- see readme.txt file */
//this is the list of include files, write any custom functions needed on this page
//must have included and set up image_manager_01_config.php first

//-------------------- begin required files -----------------------------
#from RelateBase Library
if(!$FUNCTION_ROOT)$FUNCTION_ROOT=$_SERVER['DOCUMENT_ROOT'].'/functions';

//which one to check?
require($FUNCTION_ROOT.'/function_misc_functions_v001.php');

if(!function_exists('array_file_list_i1'))
	require($FUNCTION_ROOT.'/function_array_file_list_i1_v100.php');
if(!function_exists('bitwise_op'))
	require($FUNCTION_ROOT.'/function_bitwise_op_v100.php');
if(!function_exists('callback'))
	require($FUNCTION_ROOT.'/function_callback_v101.php');
if(!function_exists('create_thumbnail'))
	require($FUNCTION_ROOT.'/function_create_thumbnail_v200.php');
if(!function_exists('get_file_assets'))
	require($FUNCTION_ROOT.'/function_get_file_assets_v100.php');
if(!function_exists('iframe_shutdown'))
	require($FUNCTION_ROOT.'/function_iframe_shutdown_v100.php');
if(!function_exists('prn'))
	require($FUNCTION_ROOT.'/function_prn.php');
if(!function_exists('q'))
	require($FUNCTION_ROOT.'/function_q_v120.php');
if(!function_exists('xml_read_tags'))
	require($FUNCTION_ROOT.'/function_xml_read_tags_v135.php');
if(!function_exists('attach_download'))
	require($FUNCTION_ROOT.'/function_attach_download_v100.php');
if(!function_exists('subkey_sort'))
	require($FUNCTION_ROOT.'/function_array_subkey_sort_v203.php');

#from local functions folder
#2009-01-22 removed	if(!function_exists('fs_dir_size'))
#2009-01-22 removed		require('functions/function_fs_dir_size_v100.php');


//-------------------- end required files -----------------------------
if(!function_exists('h')){
	function h($n){
		return htmlentities($n);
	}
}
if(!function_exists('page_end')){
	function page_end(){
		//not developed
	}
}
if(!function_exists('get_globals')){
	function get_globals(){
		ob_start();
		//snapshot of globals
		$a=$GLOBALS;
		//unset redundant nodes
		unset($a['HTTP_SERVER_VARS'], $a['HTTP_ENV_VARS'], $a['HTTP_GET_VARS'], $a['HTTP_COOKIE_VARS'], $a['HTTP_SESSION_VARS'], $a['HTTP_POST_FILES'], $a['GLOBALS']);
		print_r($a);
		unset($a);
		$out=ob_get_contents();
		ob_end_clean();
		return $out;
	}
}
if(!function_exists('get_folder_config')){
	function get_folder_config($folder){
		/*
		config params:
		view - thumbnails | fullfolder as of 1/7/2009
		fullfoldersizelimit=n - max size before we constrain the image
		
		*/
		global $systemRoot;
		$dbrfile=$systemRoot.'/'.trim($folder,'/').'/.thumbs.dbr/.file_explorer.stats.dbr';
		if(file_exists($dbrfile) && (@$fp=fopen($dbrfile,'r'))){
			if($statsdbr=@fread($fp,filesize($dbrfile))){
				if($config=trim( xml_read_tags($statsdbr, 'folderconfig', $attrib='', $return=XML_RET_INNER) )){
					$b=explode("\n",trim($config));
					foreach($b as $v){
						if(!trim($v))continue;
						$c=explode('=',$v);
						$d[strtolower($c[0])]=(count($c)==1 ? true : $c[1]);
					}
					return $d;
				}
			}
		}else{
			
		}
	}
}
if(!function_exists('is_boxed')){
	function is_boxed($folder,$set=''){
		global $developerEmail, $fromHdrBugs;
		$dbrfile=rtrim($folder,'/').'/.thumbs.dbr/.file_explorer.stats.dbr';
		if(file_exists($dbrfile) && (@$fp=fopen($dbrfile,'r'))){
			$statsdbr=@fread($fp,filesize($dbrfile));
			if(!$set && $boxed=trim( xml_read_tags($statsdbr, 'boxedfolder', $attrib='', $return=XML_RET_INNER) )){
				//if present this means a valid bounding box exists.. in any rare case the 3rd parameter is not present we set a 4-wall shrink box
				$b=explode("\t",$boxed);
				//currently * means no constraint
				$width=trim($b[0]);
				$height=trim($b[1]);
				$boxMethod=(trim($b[2]) ? trim($b[2]) : BOX_FOUR_WALL);
				return array(
					$width,
					$height,
					$boxMethod,
					'boundingBoxWidth'=>$width,
					'boundingBoxHeight'=>$height,
					'boxMethod'=>$boxMethod
				);
			}
		}else{
			//attempt to create it
			if($fp=@fopen($dbrfile,'w')){
				$msg='<comment>Created by function is_boxed() '.date('m/d/Y \a\t g:iA').'</comment>';
				fwrite($fp,$msg,strlen($msg));
			}else{
				mail($developerEmail,'unable to create .file_explorer.stats.dbr',$dbrfile . ':' . $fp . ':' . get_globals(),$fromHdrBugs);
				return false;
			}
		}
		if($set=='clear'){
			$file=file_get_contents($dbrfile);
			$file=trim(preg_replace('/<boxedfolder[^>]*>(.*)<\/boxedfolder>/i','',$file));
			$fp=fopen($dbrfile,'w');
			if(fwrite($fp,$file,strlen($file))){
				return true;
			}
		}else if($set=='set'){
			global $passedBoundingBoxWidth, $passedBoundingBoxHeight, $passedBoxMethod;
			$fp=fopen($dbrfile,'r+');
			$file=file_get_contents($dbrfile);
			$xml='<boxedfolder version="1.03">'.$passedBoundingBoxWidth . "\t". $passedBoundingBoxHeight."\t".$passedBoxMethod.'</boxedfolder>';
			if(preg_match('/<boxedfolder[^>]*>(.*)<\/boxedfolder>/i',$file,$match)){
				$file=str_replace($match[0],$xml,$file);
			}else{
				$file=trim($file).(trim($file) ? "\n" : '').$xml;
			}
			if(fwrite($fp,$file,strlen($file))){
				return array(
					$passedBoundingBoxWidth,
					$passedBoundingBoxHeight,
					$passedBoxMethod,
					'boundingBoxWidth'=>$passedBoundingBoxWidth,
					'boundingBoxHeight'=>$passedBoundingBoxHeight,
					'boxMethod'=>$passedBoxMethod
				);
			}else{
				mail($developerEmail,'error writing <boxedfolder> attribute to .file_explorer.stats.dbr',$dbrfile . ':' . $fp . ':' . get_globals(),$fromHdrBugs);
			}
		}
	}
}
if(!function_exists('is_image')){
	function is_image($img,$range='web'){
		$range=strtolower($range);
		$a=explode('.',$img);
		if(count($a)>2 && preg_match('/^(gif|jpg|jpeg|png)$/i',$a[count($a)-1])){
			return true;
		}
		return false;
	}
}
if(!function_exists('fe_visiblefolderpath')){
	function fe_visiblefolderpath($rootFolderName, $folder){
		//show visible folder path with nav
		$path=explode('/', $rootFolderName . ($folder ? '/' : '') . $folder);
		if(count($path)==1){
			?><span id="currentFolderLabel"><?php echo $path[0];?></span><?php
		}else{
			foreach($path as $n=>$v){
				if($n<count($path)-1){
					if($n!=0)$string=($string?'/':'') . $v;
					?><a title="Open this folder" href="javascript:oF(-1,4,'<?php echo str_replace("'", "\'",$string);?>');"><?php echo $v?></a><span class="dividerslash">/</span><?php
				}else{
					?><span id="currentFolderLabel"><?php echo $v?></span><?php
				}
			}
		}
	}
}
if(!function_exists('filesize_text')){
	function filesize_text($k){
		$b=$k*255;
		switch(true){
			case $b<101: 			return floor($b).' bytes';
			case $b<(256*15):		return round(($b/255),2).'Kb';
			default:				return round($b/255).'Kb';
		}
	}
}
