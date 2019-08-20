<?php 
/* folder navigator v1.1.10 -- see readme.txt file */
/*
todo
	2012-04-17:
		so a feature of my FEX which windows would not have would be showing the FILES on left nav.  Clicking (single) would then pull up an editor or an external program in the right pane.  NICE IDEA.
		the "loaded" part represents a load of a specific path /images/landscaping/sub1/sub2/.  also a layout format.  each folder should save the state of openness, as well as the pictures.  but loading represents loading an essential list or collection and should be atomic with the rest of FEX
	2012-04-16:
		option to load folder in parent window
		is the original present?
		option to view thumbs [^] or option = list, etc etc.
		date range
		is this picture used anywhere in juliet? what pages? :0
		small icons for verticality
*/
$bufferDocument=false;
$relatebase=false;// this is a standalone object, set to true to integrate with a login system

$localSys['scriptID']='folder navigator';
$localSys['build']='';
$localSys['buildDate']='';
$localSys['buildNotes']='eventually this will be the left nav on FEX and will load the assets in the main window - WITH STATS!';


$configPathReplace='leftnav_register.php';
require(str_replace($configPathReplace,'',__FILE__).'config.php');

require($_SERVER['DOCUMENT_ROOT'].'/config.php');
exit;

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
<title>Folder Navigator - FEX - Ver.<?php echo $feVersion?></title>
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


li{
	line-height:210%;
	}
.folder{
	font-size:104%;
	border:1px solid #ccc;
	cursor:pointer;
	padding:1px 10px;
	}
.data{
	margin-left:22px;
	line-height:100%;
	}
#shower ul{
	margin-left:22px;
	}
ul{
	list-style:inside circle;
	clear:both;
	}
.below{
	border-left:1px dotted #666;
	margin-left:25px;
	margin-bottom:10px;
	margin-top:2px;
	}
.op{
	line-height:100%;
	clear:both;
	margin-left:22px;
	margin-top:5px;
	
	}
.showHide{
	cursor:pointer;
	background-color:wheat;
	padding:2px;
	float:left;
	}

</style>

<script id="jsglobal" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script id="jscommon" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script id="jsforms" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script id="jsloader" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script id="jsdataobjects" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script id="jscontextmenus" type="text/javascript" src="/Library/js/contextmenus_04_i1.js"></script>
<script language="javascript" type="text/javascript">var disposition='<?php echo $disposition;?>'; </script>
<script id="jsgeneral" src="Library/general_v100.js"></script>

<script language="javascript" type="text/javascript">
var feVersion='<?php echo $feVersion?>';
var thispage = 'index.php';
var thisfolder = '';
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

var fileCount=<?php echo $fileCount ? $fileCount : '0';?>;
var folderCount=<?php echo $folderCount ? $folderCount : '0';?>;
var boundingBoxWidth=<?php echo $boundingBoxWidth ? $boundingBoxWidth : "''"?>;
var boundingBoxHeight=<?php echo $boundingBoxHeight ? $boundingBoxHeight : "''"?>;
var boxMethod=<?php echo $boxMethod ? $boxMethod : ($boundingBoxWidth || $boundingBoxHeight ? BOX_FOUR_WALL : '0')?>;
</script>
</head>
<body>
<script language="javascript" type="text/javascript">
function expand(o){
	var id=o.id.replace('f_','');
	var d=g('b_'+id).style.display;
	g('b_'+id).style.display=(d=='none'?'block':'none');
}
function expand2(o){
	var id=o.id.replace('sh_','');
	var d=g('op_'+id).style.display;
	g('op_'+id).style.display=(d=='none'?'block':'none');
	g('sh_'+id).innerHTML=(d=='none'?'hide..':'show..');
}
</script>
<div id="mainWrap" style="width:1000px;padding:5px 10px 5px 15px;">
<div id="shower">
<?php
function folders($f,$options=array()){
	global $_folders_;
	extract($options);
	$options['level']++;
	if($fp=opendir($_SERVER['DOCUMENT_ROOT'].'/'.$f)){
		while(false!==($file=readdir($fp))){
			if(preg_match('/^\./',$file))continue;
			if(is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$f.'/'.$file)){
				$folders[$file]=$file;
			}else{
				$files[$file]=$file;
			}
		}
		if($folders || $files){ ?><ul id="fl_<?php echo $_folders_;?>" class="level<?php echo $options['level'];?>"><?php	}
		if($folders)
		foreach($folders as $folder){
			$_folders_++;
			?><li>
			<span id="f_<?php echo $_folders_;?>" class="folder" onclick="expand(this);"><?php echo $folder;?></span>
			<div id="b_<?php echo $_folders_;?>" class="below" <?php echo $_folders_>0?'style="display:none;"':''?>>
				<div id="d_<?php echo $_folders_;?>" class="data">
				<?php
				$minSize=array();
				$maxSize=array();
				$minTime=array();
				$maxTime=array();
				$imgs=array();
				$size=array();
				$output=array();
				$maxA=$minA=$minDims=$maxDims=$pdfs='';
				if($fp2=opendir($_SERVER['DOCUMENT_ROOT'].'/'.$f.'/'.$folder)){
					while(false!==($file2=readdir($fp2))){
						if(preg_match('/^\./',$file2))continue;
						if(is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$f.'/'.$folder.'/'.$file2))continue;

						//build data on contents of this folder
						if($s=stat($_SERVER['DOCUMENT_ROOT'].'/'.$f.'/'.$folder.'/'.$file2)){
							$output[]=($f.'/'.$folder.'/'.$file2);
							$size['all']+=$s['size'];
							if(!$minSize['all'] || $s['size']<$minSize['all'])$minSize['all']=$s['size'];
							$maxSize['all']=max($maxSize['all'],$s['size']);
							if(!$minTime['all'] || $s['mtime']<$minTime['all'])$minTime['all']=$s['mtime'];
							$maxTime['all']=max($maxTime['all'],$s['mtime']);
							if($g=getimagesize($_SERVER['DOCUMENT_ROOT'].'/'.$f.'/'.$folder.'/'.$file2)){
								//orientation
								$w=$g[0]; $h=$g[1];
								$r=$w/$h;
								$or=($r > 1.05 ? 'landscape' : ($r < .95 ? 'portrait' : 'square'));
								$imgs[$or]['count']++;
								$a=$w*$h;
								if(!$minA || $a<$minA){
									$minA=$a;
									$minDims=$w.'x'.$h;
								}
								if($a>$maxA){
									$maxA=$a;
									$maxDims=$w.'x'.$h;
								}
								$size['img']+=$s['size'];
								if(!$minSize['img'] || $s['size']<$minSize['img'])$minSize['img']=$s['size'];
								$maxSize['img']=max($maxSize['img'],$s['size']);
								if(!$minTime['img'] || $s['mtime']<$minTime['img'])$minTime['img']=$s['mtime'];
								$maxTime['img']=max($maxTime['img'],$s['mtime']);
							}
							if(preg_match('/\.pdf$/i',$file))$pdfs++;
						}
					}
				}
				if($imgs){
					echo ($count=$imgs['portrait']['count']+$imgs['landscape']['count']+$imgs['square']['count']).' pictures<br />';
					echo 'Sizes: '.$minDims.($maxDims>$minDims ? ' to '.$maxDims : '').'<br />';
					if($n=$imgs['portrait']['count']){
						echo round($n/$count)*100 .'% portrait &nbsp; ';
					}
					if($n=$imgs['landscape']['count']){
						echo round($n/$count)*100 .'% landscape &nbsp; ';
					}
					if($n=$imgs['square']['count']){
						echo round($n/$count)*100 .'% square* &nbsp; ';
					}
					echo '<br />';
				}
				if($pdfs)echo $pdfs.' PDFs<br />';
				if(!empty($output)){
					?><div id="sh_<?php echo $_folders_;?>" class="showHide" onclick="expand2(this)">show..</div><?php
				}
				?>
				</div>
				<?php
				if(!empty($output)){
					echo "\n";
					?><div id="op_<?php echo $_folders_;?>" class="op" <?php echo 'style="display:none;"';?>><?php
					foreach($output as $v){

						if(preg_match('/\.(jpg|jpeg|png|gif|svg)$/i',$v)){
							$Tree_ID=tree_build_path($v);
							
							$href='/images/reader.php?Tree_ID='.$Tree_ID.'&Key='.md5($Tree_ID.$GLOBALS['MASTER_PASSWORD']).'&disposition=48x48&boxMethod=2';
							global $univHref;
							if(!$univHref){
								$univHref=$href;
							}
	
							?><a href="/<?php echo $v;?>" onclick="return ow(this.href,'l1_images','700,700');" title="View picture"><img src="<?php echo $univHref;?>" width="48" height="48" style="float:left;padding:2px; border:1px solid #ccc; margin:0px 5px 5px 0px;" /></a><?php
							echo "\n";
						}else{
							?><a href="/<?php echo $v;?>" onclick="return ow(this.href,'l1_images','700,700');" title="View file"><?php echo $v;?></a><?php
							echo "\n";
						}
					}
					?>
					<div style="clear:both;"> </div>
					</div><?php
				} 
				
				folders($f.'/'.$folder); 

				if(false && $files){
					?><ul><?php
					foreach($files as $n=>$v){
						?><li>
						<span class="file"><?php echo $n;?></span>
						</li><?php
					}					
					?></ul><?php
				}

				?>
			</div>
			</li><?php
		}
		if($folders || $files){ ?></ul><?php }
	}
}
folders('images');
?>
</div>
</div>




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