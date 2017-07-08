<?php
/* 
2008-11-07: New file widget
currently this is buried in the RootTools toolbar; I don't have any plans to put it elsewhere. It carries the following fields

uploadFile1 (file object)
boundingBoxWidth
boundingBoxHeight
boxType
openImageForEditing - open the uploaded image for editing when complete
boxFromNowOn - set this in the .file_explorer.stats.dbr system file
storeOriginalPicture - store the stock picture in .thumbs.dbr folder if var boxReductionThreshold is crossed (normally a decimal, i.e. percentage loss in dimensions such as .10, or an integer, i.e. KB of data loss)
extractZipFile - extract the compressed file with the folder as the root extraction location; merge created folders' contents with existing folders if present

As a subcomponent of RootTools, the fields are subject to a robot toolBarRefresh() which PRESUMES the vars are present.  When a new folder is loaded, a var loadFolderContext is set to true, and toolBarRefresh() will detect and do the following:
	minimize the fileObjectMod1's options
	reset bounding box parameters

A folder can be boxed permanently through the box icon.  This si stored in .file_explorer.stats.dbr in the XML tag <boxedfolder></boxedfolder>  This is picked up on the receiving end and is NOT turtled onto the boundingBoxWidth|Height fields; instead these fields are reserved for overrides.  When a manual entry or override is present, these ARE passed through the boundingBoxWidth|Height fields, and an icon for boxing appears in the newfile widget. Also, the language for the boxFromNowOn field text reads "Update this bounding box for this folder" vs. "Make these bounding box dimensions permanent"

Manually entered and querystring-passed box dimensions are removed and the fields reset when the folder is changed



	
*/

?>
<style type="text/css">
/*tabs*/
#fileObjectMod1{
	opacity:.92;
	background-color:#fff;
	border:1px solid #888;
	padding:15px;

	position:absolute;
	display:none;
	text-align:left;
	overflow:hidden;
	}
#fileBodyNew{
	position:relative;
	}
#fileBodyNewPending{
	position:absolute;
	top:-8px;
	/* left:5px; top:35px; */
	display:none;
	}
.t1{

	opacity:.92;
	background-color:#FFF;
	border:1px solid #555;
	float:left;
	margin:2px 3px 2px 7px;
	padding:0px 5px 2px 5px;
	cursor:pointer;
	}
.desc{
	opacity:.92;
	background-color:#FFF;
	border-bottom:none; /* 1px solid #fff */
	padding:0px 5px 6px 5px;
	margin:0px 3px 4px 7px;
	}
</style>
<div id="fileObjectMod1" onclick="override_hidemenuie5=true;" onMouseOver="override_hidemenuie5=true;" onMouseOut="override_hidemenuie5=false;" precalculated="imWidgetCalc()" style="width:175px;height:60px;">
  <div id="fileBodyNew">
		<div id="fileBodyNewPending">
		<img src="/images/i/fex104/loading2___.gif" alt="loading">
		</div>
		Upload a new file..<br />
		<?php
		if($browser=='IE'){
			echo '<div id="uploadFileWrap"><input type="file" name="uploadFile1" id="uploadFile1" onChange="uploadFile(this.value)" /></div>';
			?><?php
		}else{
			?><div style="float:left;width:75px;height:24px;"><div id="uploadFileWrap" style=""><input type="file" name="uploadFile1" id="uploadFile1" onChange="uploadFile(this.value)" /></div></div><?php
		}
		?><div id="uploadStatus1" style="float:left;">&nbsp;</div>
		<input name="uploadFile1Path" type="hidden" id="uploadFile1Path" />
		<br style="clear:both;" />
		[<a id="fileUploadOptions" href="#" onclick="optionsNewExpand(); return false;">options</a>]
		<div style="clear:both;width:275px;margin-top:20px;background-image:url(i/bg-horizyellowgrad.jpg);background-repeat:repeat-y;padding:10px;">
			<strong>For Images:</strong><br />
			Bounding box width: 
			<input name="passedBoundingBoxWidth" type="text" id="passedBoundingBoxWidth" value="<?php echo !$passedBoundingBoxWidth ? '(none)' : ($passedBoundingBoxWidth=='*' ? '(no limit)' : $passedBoundingBoxWidth);?>" size="5" maxlength="6" onFocus="if(this.value=='(none)' || this.value=='(no limit)')this.value='';this.className='noghost';" class="<?php echo !$passedBoundingBoxWidth || $passedBoundingBoxWidth=='*' ? 'ghost' : '';?>" />
	<br />
			Bounding box height: 
			<input name="passedBoundingBoxHeight" type="text" id="passedBoundingBoxHeight" value="<?php echo !$passedBoundingBoxHeight ? '(none)' : ($passedBoundingBoxHeight=='*' ? '(no limit)' : $passedBoundingBoxHeight);?>" size="5" maxlength="6" onFocus="if(this.value=='(none)' || this.value=='(no limit)')this.value='';this.className='noghost';" class="<?php echo !$passedBoundingBoxHeight || $passedBoundingBoxHeight=='*' ? 'ghost' : '';?>" />
			<br />
			Shrink method: 
			<select name="passedBoxMethod" id="passedBoxMethod">
			  <option value="">none</option>
			  <option value="4" <?php echo $passedBoxMethod==BOX_FOUR_WALL?'selected':''?>>4 Wall shrink</option>
			  <option value="2" <?php echo $passedBoxMethod==BOX_TWO_WALL?'selected':''?>>2 Wall shrink (crop)</option>
			</select> 
			[<a href="javascript:alert('A 4-wall shrink will shrink the image to fit into the bounding box you specify above.  A 2-wall shrink will shrink the picture until TWO walls fit inside the bounding box, and then crop the resulting centered image on the other two walls.  With a 2-wall shrink you will not have any whitespace in the box, but may lose some of the image; with a 4-wall shrink you may have whitespace, but nothing will be cropped; also the image may scale much smaller if it is either very wide or very tall');">help</a>]<br />
			<label><input type="checkbox" name="openImageForEditing" value="1" onclick="if(this.checked){ alert('This is not developed yet'); this.checked=false; }" />
			Open the image for editing when loaded</label> 
			<br />
			<label>
			<input name="boxFromNowOn" type="checkbox" id="boxFromNowOn" value="1" <?php echo ($a=is_boxed($systemRoot.($folder?'/':'').$folder) || $passedBoxFromNowOn)?'checked':''?> /> 
			<span id="doFromNowOnText"><?php
			if($boundingBoxWidth && $passedBoundingBoxWidth){
				?>Update this bounding box for this folder<?php
			}else{
				?>Make these bounding dimensions permanent<?php
			}
			?></span></label>
			<br />
			<label>
			<input name="storeOriginalPicture" type="checkbox" id="storeOriginalPicture" value="1" <?php echo $storeOriginal || !isset($storeOriginal) ? 'checked':''?> /> 
			Store original picture if significantly larger</label>
			<br />
			<strong>TAR/Zip Files:</strong><br />
			<label>
			<input name="extractZipFile" type="checkbox" id="extractZipFile" value="1" onclick="if(this.checked){ alert('This is not developed yet'); this.checked=false; }" /> 
			Extract  file upon uploading</label>
			<br />
		</div>
	</div>
</div>
<script type="text/javascript" language="javascript">
var menuSetBlock=false;
var pendingFileObjectLoads=[];

var optionsNewExpand_inprocess=false;
function optionsNewExpand(internal){
	/*
	2008-11-01: basic timelapse function structure
	*/
	//filter out calls in mid-process
	if(optionsNewExpand_inprocess && !internal) return false;
	
	optionsNewExpand_inprocess=true;
	var optionsNewExpandIncrement=10;
	
	//do something
	if(typeof optionsNewExpand_expanding=='undefined')optionsNewExpand_expanding=optionsNewExpandIncrement;
	var w=g('fileObjectMod1').style.width.replace('px','');
	w=parseInt(w);
	var h=g('fileObjectMod1').style.height.replace('px','');
	h=parseInt(h);
	w+=optionsNewExpand_expanding;
	h+=optionsNewExpand_expanding;
	//internal to this iteration of the function
	var process_complete=false;
	if(h>295 || h<60){
		process_complete=true;
		optionsNewExpand_expanding*=-1;
		optionsNewExpand_inprocess=false;
		g('fileUploadOptions').innerHTML=(optionsNewExpand_expanding== (optionsNewExpandIncrement * -1) ? 'close' : 'options');
		return false;
	}
	g('fileObjectMod1').style.width=w+'px';
	g('fileObjectMod1').style.height=h+'px';
	
	if(process_complete){
		//allow function call again
		optionsNewExpand_inprocess=false;
		return;
	}else{
		setTimeout('optionsNewExpand(true);',5);
	}
	return false;
}

var timelapse_inprocess=false;
function timelapse(internal){
	/*
	2008-11-01: basic timelapse function structure
	*/
	//filter out calls in mid-process
	if(timelapse_inprocess && !internal) return false;
	
	timelapse_inprocess=true;
	
	//do something
	
	if(process_complete){
		//allow function call again
		timelapse_inprocess=false;
		return;
	}else{
		setTimeout('timelapse(true);',10);
	}
}

function uploadFile(n){
	if(n=='')return;
	g('uploadFile1Path').value=n;

	var buffer=g('mode').value;
	g('mode').value='uploadFile';
	var buffer2=overrideFailTimeout;
	overrideFailTimeout=true;
	
	//error checking and timeout as appropriate, submit file
	if(!uploadFileCheck())return;
	if(!beginSubmit())return;
	g('RootToolsForm').submit();
	g('uploadFile1').disabled=true;

	//reset values
	g('mode').value=buffer;
	overrideFailTimeout=buffer2;
}
function uploadFileCheck(){
	var w=g('passedBoundingBoxWidth').value.replace('(no limit)',1000000);
	var h=g('passedBoundingBoxHeight').value.replace('(no limit)',1000000);
	w=w.replace('(none)','');
	h=h.replace('(none)','');
	var m=g('passedBoxMethod').value;
	if(w || h || m){
		w=(w=='*' ? w : parseInt(w));
		h=(h=='*' ? h : parseInt(h));
		m=parseInt(m);
	}
	if((w || h || m ) && ((isNaN(w) && w!=='*') || (isNaN(h) && h!=='*') || isNaN(m) || w<16 || h<16 || !m)){
		alert('Your bounding box for this picture is not valid - you must specify a bounding box width and height, and a "shrink method".  If you do not wish to set a bounding box, leave all three fields blank');
		g('uploadFileWrap').innerHTML='<input type="file" name="uploadFile1" id="uploadFile1" onChange="uploadFile(this.value)" />';
		g('uploadFileWrap').disabled=false;
		//expand on options
		if(!optionsNewExpand_inprocess && g('fileUploadOptions').innerHTML=='options') optionsNewExpand();
		w<16 || (isNaN(w) && w!=='*') ? g('passedBoundingBoxWidth').select() : ( h<16 || (isNaN(h) && h!=='*') ? g('passedBoundingBoxHeight').select() : g('passedBoxMethod').focus() );
		return false;
	}else{
		g('uploadStatus1').innerHTML='&nbsp; <img align="absbottom" src="/images/i/fex104/processing1.gif" title="upload processing" />';
		return true;
	}
}
function imWidgetCalc(){
	/*
	ATTRIBUTES USED FOR THIS FUNCTION
	---------------------------------
	size=35.44 (in KB), dims=350,450 (width,height - this represents ACTUAL dimensions, not the thumbnail rep)
	description (of object)
	filename, filepath, filedomain
	expectedfiletypes=jpg,gif,png || doc,xls || [recognizedGroupType:] || [recognizedExclusionList:]
	isimage=1 means this is an image (vs. a video link, Word doc, etc.) - if null set to 1
	nofile=1 means this image is a placeholder, no actual file is present
	
	plus the standard src, alt, width, height, and style
	
	*/
	if(!menuSetBlock){
		//done once
		menuSetBlock=true;
		g('fileObjectMod1').style.display='block';
	}
	return true; //developing
	var i=g(cmBoundToElement).firstChild;
	//set relationship to parent object
	g('fOBoundToElement').value=cmBoundToElement;
	var isfile=!parseInt(i.getAttribute('nofile'));

	//set the appropriate tab
	tabs(isfile && !pendingFileObjectLoads[cmBoundToElement] && !fOSetFileTabNew? 'fileTabCurrent' : 'fileTabNew');
	
	//retain loading if necessary
	g('fileBodyNewPending').style.display=(pendingFileObjectLoads[cmBoundToElement] ? 'block':'none');
	
	//pull the image
	g('fOthumb').src=g(cmBoundToElement).firstChild.src;
	g('fOthumb').width=g(cmBoundToElement).firstChild.width;
	g('fOthumb').height=g(cmBoundToElement).firstChild.height;
	g('fOthumbdesc').setAttribute('title',i.getAttribute('description'));
	
	var isimage=i.getAttribute('isimage');
	if(isimage==null)isimage=1;
	g('fOfilename').innerHTML=(isfile ? i.getAttribute('filename') : '(none; <a href="#" onclick="tabs(\'fileTabNew\');return false;">select a file</a>)');
	g('fOtype').innerHTML=(isfile ? i.getAttribute('type') : '(N/A)');
	g('fOsize').innerHTML=(isfile ? i.getAttribute('size') : 'OKB');
	if(isfile && isimage && i.getAttribute('dims')){
		dims=i.getAttribute('dims').split(',');
		g('fOwidth').innerHTML=dims[0];
		g('fOheight').innerHTML=dims[1];
		g('fOimg').style.display='block';
	}else g('fOimg').style.display='none';

}
g('fileObjectMod1').style.visibility='visible';
AssignMenu('ctrlAddNewFile', 'fileObjectMod1');
menuAlign['^ctrlAddNewFile']='mouse';
</script>
