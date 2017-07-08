<div id="FileSystemFocus_1_1"><?php
if($files){
	$i=0;
	foreach($files as $idx=>$v){
		set_time_limit(15);
		$i++;
		if($isImage=(!$v['folder'] && preg_match('/jpg|gif|png/i',$v['ext']))){
			//make a thumbnail
			$haveThumb=false;
			if(file_exists($node.'/.thumbs.dbr/'.$v['name'])){
				//compare the touch time of the image vs. the touch time of the thumb
				if("image has changed"){
					//recompile thumb image
				}else{
					$haveThumb=true;
				}
			}else{
				//try to create it
				$haveThumb= create_thumbnail($node.'/'.$v['name'], $thumbnailViewWidth.','.$thumbnailViewHeight, '', $node.'/.thumbs.dbr/'.$v['name']);
			}
		}
		switch(true){
			case $view=='details':
				require($FEX_ROOT.'/components/comp_006_1.03nodelogic_details.php');
			break;
			case $view=='thumbnails' || $view=='fullfolder':
				require($FEX_ROOT.'/components/comp_006_1.03nodelogic.php');
			break;
			default:
				//not developed
		}
	}
}
?></div><?php
//repeat the switch-case for the context menus
switch(true){
	case $view=='thumbnails': 
	case $view=='fullfolder':
	?>
	<div id="thumbOptionsA" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onclick="executemenuie5(event)" precalculated="preContext('thumbOptionsA')">
		<div id="optOpenFile" class="menuitems" command="obj_open('file');" status="Open the selected file or folder">Open</div>
		<div id="optOpenFolder" class="menuitems" command="ooF('',1);" status="Open the selected file or folder">Open</div>
		<div id="optPictureViewer" class="menuitems" command="obj_open('picture');" status="Open the selected file or folder">Open</div>
		<div id="optPictureEditor" class="menuitems" command="obj_edit('picture');" status="Open the selected file or folder">Picture Editor</div>
		<div id="optDownloadToComputer" class="menuitems" command="obj_download();" status="Save (download) the selected files">Save to computer</div>
		<div id="optDownloadZip" class="menuitems" command="obj_send(event,'dlZip');" status="Save (download) a zip file to your computer">Download zip file</div>
		<div id="optRotateLeft" class="menuitems" command="obj_send(event,'rotateLeft');" status="Rotate this picture to the left 90 degrees">Rotate left 90&deg;</div>
		<div id="optRotateRight" class="menuitems" command="obj_send(event,'rotateRight');" status="Rotate this picture to the right 90 degrees">Rotate right 90&deg;</div>
		<div id="optOpenWith" class="menuitems" command="" status="Select a program to open this file or folder with">Open with..</div>
		<hr class="mhr" size="1" />
		<div id="ccp01a" class="menuitems" command="obj_send(event,'ccp_cut');" status="Cut the selected items">Cut</div>
		<div id="ccp02a" class="menuitems" command="obj_send(event,'ccp_copy');" status="Copy the selected items">Copy</div>
		<?php
		if($testing){ //2009-02-14: cut and copy more not a priority right now
		?><div id="ccp03a" class="menuitems" command="obj_send(event,'ccp_cutmore')" status="Cut the selected items">Cut More</div>
		<div id="ccp04a" class="menuitems" command="obj_send(event,'ccp_copymore')" status="Copy the selected items">Copy More</div>
		<?php
		}
		?><div id="ccp05a" class="menuitems" command="obj_send(event,'ccp_paste')" status="Paste the contents of the clipboard here">Paste</div>
		<hr class="mhr" size="1" />
		<div id="optSubmitCallback" class="menuitems" style="display:none;" command="cbResponse();" status="(this is hidden)">Submit Callback</div>
		<?php 
		if(false){
			//email this (these) files
			//FTP this file
		}
		
		?><div id="optObjRename" class="menuitems" command="obj_preprename()" status="Rename this file or folder">Rename</div>
		<div id="optObjDelete" class="menuitems" command="obj_send(event,'deleteObjects')" status="Paste the contents of the clipboard here">Delete</div>
		<hr class="mhr" size="1" />
		<div id="optObjNewfolder" class="menuitems" command="obj_newfolder()" status="Create a new folder in this folder">New Folder</div>
		<div id="optObjProperties" class="menuitems" command="obj_properties()" status="Properties">Properties</div>
		<hr class="mhr" size="1" />
		<div id="optObjHelp" class="menuitems" command="gethelp_1()" status="Help for this page">Help..</div>
	</div>

		<div id="FSFocusOptionsA" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onclick="executemenuie5(event)" precalculated="preContext('FSFocusOptionsA')">
			<div id="optObjNewFolder-2" class="menuitems" command="obj_newfolder()" status="Create a new folder in this folder">New folder</div>
			<div id="optObjRefresh-2" class="menuitems" command="obj_refresh()" status="Refresh this folder's contents">Refresh</div>
			<hr class="mhr" size="1" />
			<div id="ccp05c" class="menuitems" command="obj_send(event,'ccp_paste')" status="Paste the contents of the clipboard here">Paste</div>
		</div>
		<script language="javascript" type="text/javascript">
		//assign context menu to ids
		try{
		AssignMenu('^node_', 'thumbOptionsA');
		AssignMenu('FileSystemFocus_1_1', 'FSFocusOptionsA');
		}catch(e){  }
		</script><?php
	break;
}
?>