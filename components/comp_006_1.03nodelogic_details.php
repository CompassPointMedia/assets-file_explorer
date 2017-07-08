<?php
/*
2009-09-12
----------
The purpose of this is now to present a highlightable table row with great eye candy, which behaves as a bonafide sortable component, and which can filter based on something like *.jpg


2009-02-08
-----------------------------------------------------
* compiled thumbnails and fullfolder views into one object before it got ugly


2008-10-30
-----------------------------------------------------
Original thumbnail (95x95) view.  Currently this object:
	places non-folder objects in session (building _SESSION.file_explorer.uid.index array
	builds the session FOLDER array for this uid
	wraps the object in a div node_[0-9]+
	obscures objects that have been cut
	adds highlight selecting
	displays folder or thumbnail image
	displays the name or the form to change the name

required variables
	* this component presumes we are looping through foreach($files as $n=>$v).
	* $i is the _SESSION node and must be incremented outside this component; 
	* $uid for the window must be set
	* $imageRefreshRand will force JS to get a new it of the image
*/

//currently, build session based on live HTML output - FILTERING AND I INDEXING IS *ABOVE* THIS FILE
$_SESSION['file_explorer'][$uid]['index'][strtolower($v['name'])]=$i;
if($v['folder'])$_SESSION['file_explorer'][$uid]['folders'][$v['name']]=$i;

flush();
//base can deal with security for some virtual files etc - base is the directory where the asset is "really" located
$base=($v['HTTPnode'] ? rtrim($v['HTTPnode'],'/').'/' : $HTTPRootFolderName.'/'.($folder?$folder.'/':'')).($view=='thumbnails' || $view=='details'?'.thumbs.dbr/{mini-'.$miniViewWidth.'x'.$miniViewHeight.'}/':'').$v['name'];
//------------------ code block 49039 ----------------------
//handle extension showing
preg_match('/(.+)\.([a-z0-9-]+)$/i',$v['name'],$a);
if(!$v['folder'] && $hideKnownExtensions && $knownFileExtensions[strtolower($a[2])][0]){
	$removeExtension=true;
	//show just as it is (upper or lowercase)
	$extension=$a[2];
	$name=$a[1];
}else{
	$removeExtension=false;
	unset($extension);
	$name=$v['name'];
}
//------------------ end code block 49039 ------------------

//obscure the object if it is cut
unset($cutClass);
if(count($a=$feObject['history']['ccp'])){
	if(!$a[count($a)]['completed'] && count($a[count($a)]['objects'])){
		foreach($a[count($a)]['objects'] as $fullpath=>$cut){
			if(strtolower(preg_replace('/\/\*$/','',$fullpath))==strtolower(($v['node'] ? $v['node'] : $node).'/'.$v['name']) && $cut=='cut'){
				$cutClass=' cut';
				break;
			}
		}
	}
}
//main object div tag with attributes
$extAttrib=(!$v['folder'] ? 'extension="'.($v['actual_ext'] ? $v['actual_ext'] : $v['ext']).'"' : '');

//---------------- 2009-03-17: handle custom javascript ------------------------
if(isset($customOnclick)){
	$onclick=str_replace('{i}',$i,$customOnclick);
	$onclick=str_replace('{folder}',$v['folder'],$onclick);
	$onclick=($customOnclick ? ' onclick="'.$onclick.'"' : '');
}else{
	$onclick=' onclick="hi2('.$i.',1,0,event)"';
}
if(isset($customOndblclick)){
	$ondblclick=str_replace('{i}',$i,$customOndblclick);
	$ondblclick=str_replace('{folder}',$v['folder'],$ondblclick);
	$ondblclick=($customOndblclick ? ' ondblclick="'.$ondblclick.'"' : '');
}else{
	$ondblclick=' ondblclick="hi2('.$i.',1,0,event);edc('.$i.','.($v['folder'] ? '1' : '4').');"';	
}
if(isset($customOncontextmenu)){
	$oncontextmenu=str_replace('{i}',$i,$customOncontextmenu);
	$oncontextmenu=str_replace('{folder}',$v['folder'],$oncontextmenu);
	$oncontextmenu=($customOncontextmenu ? ' oncontextmenu="'.$oncontextmenu.'"' : '');
}else{
	$oncontextmenu=' oncontextmenu="hi2('.$i.',1,1,event);"';	
}

if(false){ ?><table><?php }

?><tr id="node_<?php echo $i?>" <?php if($v['folder']){ ?>folder="1"<?php } ?><?php if($v['HTTPnode']){ ?> HTTPnode="<?php echo h($v['HTTPnode']);?>"<?php } ?> class="<?php echo $cutClass?>" <?php echo $onclick . $ondblclick . $oncontextmenu?> <?php echo $extAttrib?>><?php

	//we reserve the left column for future operations
	?><td>&nbsp;</td>
	<td class="icon"><?php
	ob_start();
	if($v['folder']){
		?><img src="/images/i/fex104/bootleg_ms_folder.gif" width="38" height="37" class="fexImg" alt="picture" /><?php
	}else if(preg_match('/jpg|gif|png/i',$v['ext'])){
		if(file_exists(($v['node'] ? $v['node'] : $node).'/.thumbs.dbr/{mini-'.$miniViewWidth.'x'.$miniViewHeight.'}/'.$v['name'])){
			?><div title="<?php echo $v['width']?> x <?php echo $v['height']?> pixels; size: <?php echo filesize_text($v['size']);?>; type: <?php echo 'image/'.strtolower($v['ext'])?>" class="thumbnailsBg" style="background-image:url('<?php echo str_replace(' ','%20',$base) . ($imageRefreshRand ? '?r='.$imageRefreshRand : '')?>');">&nbsp;</div><?php
		}else{
			echo 'no thumb';
		}
	}else if($x=$fexSettings['fileTypeIcons'][strtolower($v['ext'])]){
		if(file_exists($y=$_SERVER['DOCUMENT_ROOT'].'/images/i/fex104/'.$x)){
			$dims=getimagesize($y);
			?><img src="/images/i/fex104/<?php echo $x?>" width="<?php echo $dims[0]?>" height="<?php echo $dims[1]?>" alt="<?php echo $v['ext']?>" /><?php
		}
	}else{
		?>&nbsp;<?php
	}
	$output=ob_get_contents();
	ob_end_clean();
	if($hideIcons){
		?>&nbsp;<?php
	}else{
		echo $output;
	}
	?></td>
	<?php
	
	//show cols
	for($j=0; $j<count($viewParams[$view]['selectedColumns']); $j++){
		$col=$viewParams[$view]['selectedColumns'][$j];
		$out=$title=$class=$width='';
		$customColumn=false;
		switch(true){
			case $col=='Name':
				ob_start();
				?><span id="name_<?php echo $i?>" cm_bubblethrough="1" class="nameBox" size="<?php
				if(!$v['folder'])echo round($v['size'],3);
				?>" title="<?php echo h($name);?>"><?php
				if($displayNameAsInput){
					//this form allows for onBlur submission
					require($FEX_ROOT.'/components/comp_002_objectNameForm_v100.php');
				}else{
					//output name
					echo h($name);
				}
				?></span><?php
				$out=ob_get_contents();
				ob_end_clean();
			break;
			case $col=='Dimensions':
				$class='dims';
				$width='50';
				if($v['width'] && $v['height']){
					$out= $v['width'] . ' x ' . $v['height'];
				}else{
					$out= '&nbsp;';
				}
			break;
			case $col=='Modified':
				if($v['mtime']){
					$out= date('m/d/Y g:i A',strtotime($v['mtime']));
				}else{
					$out= '&nbsp;';
				}
			break;
			case $col=='Size':
				$class='size tar';
				$width='50';
				if($v['folder']){
					$out= exec("du -hs \"$node/$name\"");
					$out=explode("\t",$out);
					$out=$out[0];
					$out=preg_replace('/([0-9])([a-z])/i','$1 $2',$out);
					//also get a summary
					$title=exec("du \"$node/$name\"");
					$title=explode("\n",$title);
					if(count($title)){
						foreach($title as $n=>$v){
							$title[$n]=preg_replace('/^[0-9]+\s+/','',$v);
						}
					}
					$title=implode("\n",$title);
				}else if(isset($v['size'])){
					$out= number_format(round($v['size'],2),2).'K';
				}else{
					$out= '&nbsp;';
				}
			break;
			case $col=='Type':
				if($v['folder']){
					$out='Folder';
				}else if($v['ext']){
					$out=$v['ext'];
				}
			break;
			case preg_match('/^function:[^:]*:/',$col):
				$customColumn=true;
				ob_start();
				eval(preg_replace('/^function:[^:]*:/','',$col));
				$out=ob_get_contents();
				ob_end_clean();
			break;
			default:
				$out= '&nbsp;';
		}
		//note 3 nbsp's for last column for scrollbar underlay
		if($customColumn){
			//responsible for its own td(s)
			echo $out;
		}else{
			?><td class="<?php echo $class?>" title="<?php echo h($title);?>" <?php if($width){ ?>width="<?php echo $width?>"<?php } ?>><?php echo $out?><?php echo $j+1==count($viewParams[$view]['selectedColumns'])?'&nbsp;&nbsp;&nbsp;':''?></td><?php
		}
	}
?></tr><?php


if(false){ ?></table><?php }

if(false){
	?><div id="node_<?php echo $i?>" <?php if($v['folder']){ ?>folder="1"<?php } ?><?php if($v['HTTPnode']){ ?> HTTPnode="<?php echo h($v['HTTPnode']);?>"<?php } ?> class="vw<?php echo $view=='thumbnails'?'T':'F';?><?php echo $cutClass?>" <?php echo $onclick . $ondblclick . $oncontextmenu?> <?php echo $extAttrib?>><?php
	
	//2009-01-07: stats inset - currently only for images hieght and width
	if($view=='fullfolder'){
		//for now
		if(preg_match('/\.(png|gif|jpg)$/i',$v['name'])){
			$area=$v['width']*$v['height'];
			if($fexSettings['redarea']['maxarea'] && $area>$fexSettings['redarea']['maxarea']){
				$dim=floor(sqrt($fexSettings['redarea']['maxarea']));
				$sizeattribute='DARKRED';
				$whstring='width="'.($fexSettings['redarea']['shrink'] * $v['width']).'"';
			}else if($fexSettings['yellowarea']['maxarea'] && $area>$fexSettings['yellowarea']['maxarea']){
				$dim=floor(sqrt($fexSettings['yellowarea']['maxarea']));
				$sizeattribute='GOLD';
				$whstring='width="'.($fexSettings['yellowarea']['shrink'] * $v['width']).'"';
			}else{
				$sizeattribute='DARKGREEN';
				$whstring='width="'.$v['width'].'" height="'.$v['height'].'"';
			}
			?><div title="<?php if($sizeattribute!='DARKGREEN')echo 'The total pixel area of this image exceeds '.$dim .'x'.$dim?>" class="imgStatsBalloon" style="background-color:<?php echo $sizeattribute?>;"><?php echo $v['width'] . 'x'.$v['height']?></div><?php
		}
	}
	
	?><div class="<?php echo $view . 'Box'?>"><?php
	ob_start();
	if($v['folder']){
		?><img src="/images/i/fex104/bootleg_ms_folder.gif" width="38" height="37" class="fexImg" alt="picture" /><?php
	}else if(preg_match('/jpg|gif|png/i',$v['ext'])){
		if($view=='thumbnails'){
			if(file_exists(($v['node'] ? $v['node'] : $node).'/.thumbs.dbr/{mini-'.$miniViewWidth.'x'.$miniViewHeight.'}/'.$v['name'])){
				?><div title="<?php echo $v['width']?> x <?php echo $v['height']?> pixels; size: <?php echo filesize_text($v['size']);?>; type: <?php echo 'image/'.strtolower($v['ext'])?>" class="thumbnailsBg" style="background-image:url('<?php echo str_replace(' ','%20',$base) . ($imageRefreshRand ? '?r='.$imageRefreshRand : '')?>');">&nbsp;</div><?php
			}else{
				echo 'no thumb';
			}
		}else{
			//fullfolder
			if(file_exists(($v['node'] ? $v['node'] : $node).'/'.$v['name'])){
				?><img title="<?php echo $v['width']?> x <?php echo $v['height']?> pixels; size: <?php echo filesize_text($v['size']);?>; type: <?php echo 'image/'.strtolower($v['ext'])?>" src="<?php echo $base . ($imageRefreshRand ? '?r='.$imageRefreshRand : '')?>" alt="picture" <?php echo $whstring?> /><?php
			}else{
				echo 'no thumb';
			}
		}
	}else if($x=$fexSettings['fileTypeIcons'][strtolower($v['ext'])]){
		if(file_exists($y=$_SERVER['DOCUMENT_ROOT'].'/images/i/fex104/'.$x)){
			$dims=getimagesize($y);
			?><img src="/images/i/fex104/<?php echo $x?>" width="<?php echo $dims[0]?>" height="<?php echo $dims[1]?>" alt="<?php echo $v['ext']?>" /><?php
		}
	}else{
		?>&nbsp;<?php
	}
	$output=ob_get_contents();
	ob_end_clean();
	echo $output;
	
	?></div>
	<div><span id="name_<?php echo $i?>" cm_bubblethrough="1" class="nameBox" size="<?php
	if(!$v['folder'])echo round($v['size'],3);
	?>" title="<?php echo h($name);?>"><?php
	if($displayNameAsInput){
		//this form allows for onBlur submission
		require($FEX_ROOT.'/components/comp_002_objectNameForm_v100.php');
	}else{
		//output name
		echo h($name);
	}
	?></span></div></div><?php
}
?>