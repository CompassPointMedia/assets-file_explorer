<?php
/* version 1.0.3; on upload, bounding box logic */
//can pass (none), *, or an int value
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




//---------------------------- From 2008-10-28: Handle boxing ---------------------------------
if(!isset($boxed))$boxed=is_boxed($node);
if(((strlen($boundingBoxWidth) && $boundingBoxWidth!=='*') || 
    (strlen($boundingBoxHeight) && $boundingBoxHeight!=='*')) && 
	$a=getimagesize($_FILES['uploadFile1']['tmp_name'])){
	$imagewidth=$a[0];
	$imageheight=$a[1];
	if(($boundingBoxWidth < $imagewidth && $boundingBoxWidth !== '*') || ($boundingBoxHeight < $imageheight && $boundingBoxHeight!=='*')){
		//box the image as a copy, leaving the uploaded temp file as-is. NOTE, later in dev we'll move this larger file to an "originals" folder OR a master folder with some settings so that we can consider this resize a step, and then revert back to original
		if($boxMethod==BOX_FOUR_WALL){
			$isBoxed = create_thumbnail($_FILES['uploadFile1']['tmp_name'], $boundingBoxWidth.','.$boundingBoxHeight, '', $_FILES['uploadFile1']['tmp_name'].'.resize');
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
			$b2w=create_thumbnail($_FILES['uploadFile1']['tmp_name'], 1, $crop, 'returnresource');
			$isBoxed = create_thumbnail($b2w, $boundingBoxWidth.','.$boundingBoxHeight, '',  $_FILES['uploadFile1']['tmp_name'].'.resize');
		}
	}
}
//-------------------------------- end handle boxing -------------------------------------


//----------------------------- move file and original -----------------------------------
if($isBoxed){
	if(!rename($_FILES['uploadFile1']['tmp_name'].'.resize', $node.'/'.$_FILES['uploadFile1']['name'])){
		error_alert('Error saving uploaded boxed file');
	}
	if($storeOriginalPicture && 'original is significantly bigger'){
		//save the original as stock
		rename($_FILES['uploadFile1']['tmp_name'], $node.'/.thumbs.dbr/'.$_FILES['uploadFile1']['name'].'.orig');
	}
}else{
	if(!move_uploaded_file($_FILES['uploadFile1']['tmp_name'],$node.'/'.$_FILES['uploadFile1']['name'])){
		if(is_writable($node)){
			mail($developerEmail,'error in file '. __FILE__.', line '.__LINE__, get_globals(), $fromHdrBugs);
			error_alert('Unknown error saving uploaded file, developer notified');
		}else{
			error_alert('The folder is not writable; please have an administrator set proper permissions');
		}
	}
}
//-----------------------------------------------------------------------------------------

?>