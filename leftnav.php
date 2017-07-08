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

$configPathReplace='leftnav.php';
require(str_replace($configPathReplace,'',__FILE__).'config.php');
require($_SERVER['DOCUMENT_ROOT'].'/config.php');

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

<link href="/Library/css/cssreset01.css" rel="stylesheet" type="text/css" />
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

.objWrap{
	float:left;
	width:225px;
	border-right:1px dotted #ccc;
	margin-right:7px;
	}
.imgClassList{
	padding:2px; 
	border:1px solid #ccc; 
	margin:5px 5px 0px -1px;
	}
</style>

<script type="text/javascript" src="/Library/js/jquery.js"></script>
<script type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script type="text/javascript" src="/Library/js/contextmenus_04_i1.js"></script>
<script language="javascript" type="text/javascript">var disposition='<?php echo $disposition;?>'; </script>
<script src="Library/general_v100.js"></script>

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

var fileCount=<?php echo $fileCount ? $fileCount : '0';?>;
var folderCount=<?php echo $folderCount ? $folderCount : '0';?>;
var boundingBoxWidth=<?php echo $boundingBoxWidth ? $boundingBoxWidth : "''"?>;
var boundingBoxHeight=<?php echo $boundingBoxHeight ? $boundingBoxHeight : "''"?>;
var boxMethod=<?php echo $boxMethod ? $boxMethod : ($boundingBoxWidth || $boundingBoxHeight ? BOX_FOUR_WALL : '0')?>;

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
var saymode='test';
function say(text){
	if(saymode!=='test')return;
	if(typeof this.i=='undefined')this.i=0;
	this.i++;
	$('#console').prepend('<div>'+new Date()+' ['+i+'] '+text+'</div>');
}
</script>
<script language="javascript" type="text/javascript">
var lookup_method='multiple'; //single|multiple, default is multiple
var autofills={
	imagetags:{
		mode:'getImageTagsByLetters',		/* where the query is located */
		label:'Name',						/* field of return */
		addNewTag:function(o){
			//----------- new coding -----------	
			$.post(lookup_externalFile + '?suppressPrintEnv=1&mode=listAdder&submode=imageTags&Name='+escape(o.value)+'&bindTo='+o.id, function(data){
				_dm_=data;
			});
	
			$(document).ajaxStop(function() {
				//this is asynchronous
				var Tags_ID=$.parseJSON(_dm_).Tags_ID;
				var Label=o.value;

				//------------ following code taken from lookup_setValue() ------------
				say('setvalue indian');
				if($(o).next().val().indexOf('|'+Tags_ID+'|')>-1){
					//what? this should not happen
				}else{
					if(lookup_method=='multiple'){
						//add the element visually - note the double key employed for multiple autofill fields
						var str='<div id="c'+('_'+o.id.replace(/[^0-9]/g,''))+'_'+Tags_ID+'" class="cancellableItem">';
						str+=Label+'<div class="cancel" onclick="lookupCancel(this)">x</div>';
						str+='</div>';
						$(o).prev().html($(o).prev().html()+str);
					}
					$(o).next().val(
						(lookup_method=='multiple' ? $(o).next().val() : '|') +Tags_ID+'|'
					);
				}
				//clear the input and focus again on it
				if(lookup_method=='multiple'){
					$(o).val('');
					$(o).focus();
				}else{
					//fill the input box with the full element name - not necessary when adding new since that IS the value
					return false;
				}
				//------------ end code ------------
				$(this).unbind('ajaxStop');
			});
		},
		preBuildFunction:function(optionRootElement,inputObj){
			//called when an existing tag is selected from list - a "hot" binding
			var Label=inputObj.innerHTML;
			Label=Label.replace('<span class="highlighted">','').replace('</span>','');
			$.post(lookup_externalFile + '?suppressPrintEnv=1&mode=listAdder&submode=imageTags&Tags_ID='+inputObj.id+'&bindTo='+optionRootElement.id, function(data) {
				_dm_=data;
			});
			$(document).ajaxStop(function() {
				//this is asynchronous
				var OK=$.parseJSON(_dm_).OK;
				if(!OK)alert('Error in binding this object to the tag');
				$(this).unbind('ajaxStop');
			});
		}
	}
}
var lookup_externalFile='file_manager_01_exe.php';
var optionDivInnerHTML='lookup_simple';
function lookup_simple(it,letters){
	/* rewrite this */
	var str;
	for(var i in it)it[i]=$.trim(it[i]);
	eval('var reg=/(^| )('+letters+')/gi;');
	str=it['Name'].replace(reg,'<span class="highlighted">$2</span>');
	return str;
}

</script>
<script type="text/javascript" src="/Library/js/lookup-1.0.js"></script>
<script language="javascript" type="text/javascript">
//rewrites
function lookupCancel(o){
	//removes element from visual list and from key receipt field
	var o=$(o).parent()[0];
	var a=o.id.split('_');
	$.post(lookup_externalFile + '?suppressPrintEnv=1&mode=listAdder&submode=imageTags&subsubmode=deleteTag&Tags_ID='+a[2]+'&bindTo='+a[1], function(data) {
		_dm_=data;
	});
	$(document).ajaxStop(function() {
		if(!$.parseJSON(_dm_).OK)alert('Error in deleting this tag from the object');
		$(this).unbind('ajaxStop');
	});
	$('#val'+a[1]).val( $('#val'+a[1]).val().replace('|'+a[2]+'|','|') );
	o.style.display='none';
	$('#val'+a[1]).focus();
}
</script>
</head>
<body>
<a href="leftnav.php?uid=<?php echo md5(time().rand(1,1000));?>">random</a><br />
<form id="form1" name="form1" method="post" action="file_manager_01_exe.php" target="w2">
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
			//if(!in_array($folder,array('175x175',)))continue;
			
			$display='none';
			
			$_folders_++;
			?><li>
			<span id="f_<?php echo $_folders_;?>" class="folder" onclick="expand(this);"><?php echo $folder;?></span>
			<div id="b_<?php echo $_folders_;?>" class="below" <?php echo $_folders_>0?'style="display:'.$display.';"':''?>>
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
						?><div class="objWrap"><?php
						if(preg_match('/\.(jpg|jpeg|png|gif|svg)$/i',$v)){
							$Tree_ID=tree_build_path($v);
							$href='/images/reader.php?Tree_ID='.$Tree_ID.'&Key='.md5($Tree_ID.$GLOBALS['MASTER_PASSWORD']).'&disposition=64x64&boxMethod=2';
							$title=q("SELECT Title FROM relatebase_ObjectsTree WHERE ObjectName=':jointitle' AND Tree_ID='$Tree_ID'", O_VALUE);
	
							?><a href="/<?php echo $v;?>" onclick="return ow(this.href,'l1_images','700,700');" title="View picture"><img src="<?php echo $href;?>" width="64" height="64" class="imgClassList" /></a><?php
							echo "\n";
						}else{
							?><a href="/<?php echo $v;?>" onclick="return ow(this.href,'l1_images','700,700');" title="View file"><?php echo $v;?></a><?php
							echo "\n";
						}
						/*
						get this working on new ids
						
						*/
						?> 
						<span><?php
						/* show all existing tags */
						if($a=q("SELECT t.ID, t.Name FROM gen_tags t, relatebase_ObjectsTree ot WHERE t.ID=ot.Objects_ID AND ot.ObjectName='gen_tags' AND ot.Tree_ID='$Tree_ID'", O_COL_ASSOC))
						foreach($a as $o=>$w){
							?><div id="c_<?php echo $Tree_ID?>_<?php echo $o;?>" class="cancellableItem"><?php echo $w;?><div class="cancel" onclick="lookupCancel(this)">x</div></div><?php
						}
						?> </span>
						<input name="value[<?php echo $Tree_ID;?>][<?php echo md5(rand(1,1000000));?>]" type="text" id="node<?php echo $Tree_ID;?>" value="<?php echo h($title);?>" class="autofill imagetags" />
						<input name="id[<?php echo $Tree_ID;?>]" type="hidden" id="val<?php echo $Tree_ID;?>" value="|<?php
						echo @implode('',q("SELECT CONCAT(ot.Objects_ID,'|') FROM relatebase_ObjectsTree ot WHERE ot.Tree_ID='$Tree_ID' AND ot.ObjectName='gen_tags'", O_COL));
						?>" />
						</div><?php
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

<input type="submit" name="Submit" value="Submit" />
</form>





<input name="mode" type="hidden" id="mode" value="tag" />
<input name="uid" type="hidden" id="uid" value="<?php echo $uid;?>" />
<div id="ctrlSection" style="display:<?php echo $testModeC?'block':'none'?>">
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
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
<div id="console"> </div>
</body>
</html><?php
//this function can vary and may flush the document 
function_exists('page_end') ? page_end() : mail($developerEmail,'page end function not declared', 'File: '.__FILE__.', line: '.__LINE__,'From: '.$hdrBugs01);
?>