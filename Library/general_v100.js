var submitFailURL='file_manager_01_exe.php';
var precallfunction='returntrue()';

/* ----- extension and context menu coding started 2009-02-14 ---------- */
var docExtensionMapping = {
	'jpg' : (disposition=='selector' ? 'selectedimage' : 'webimages'), /* images we can view through a browser */
	'jpe' : (disposition=='selector' ? 'selectedimage' : 'webimages'),
	'jpeg': (disposition=='selector' ? 'selectedimage' : 'webimages'),
	'png' : (disposition=='selector' ? 'selectedimage' : 'webimages'),
	'gif' : (disposition=='selector' ? 'selectedimage' : 'webimages'),
	'svg' : (disposition=='selector' ? 'selectedimage' : 'webimages'),
	
	'pdf' : 'openable_inbrowser', /* I can call a page and view these in both IE and Moz typically */
	'htm' : 'openable_inbrowser',
	
	'doc' : '{node:generic_or_unknown}', /* downloadable_application eventually; I must normally download these though with IE I can see them in-browser also */
	'xls' : '{node:generic_or_unknown}', /* downloadable_application */

	'{node:folder}'	: '{node:folder}',
	'{node:generic_or_unknown}'	: '{node:generic_or_unknown}'
};
var optionRegistry={
	'optPictureViewer' : {
		'id'	: 100,
		'label' : 'View',
		'jsfunction' : 'application_pictureviewer'
	}
};
/*
this registers a menu with all the parameters I'm /still/ putting in as attributes

*/
var menuRegistry={
	'thumbOptionsA' : { 
		'optOpenFile'	: { 'develop' : true },
		'optOpenFolder'	: { 'develop' : true },
		'optPictureViewer'	: { 'develop' : true },
		'optPictureEditor'	: { 'develop' : true },
		'optDownloadZip'	: { 'develop' : true },
		'optRotateLeft'		: { 'develop' : true },
		'optRotateRight'	: { 'develop' : true },
		'optDownloadToComputer'	: { 'develop' : true },
		'optOpenWith'	: { 'develop' : true },
		'ccp01a'	: { 'develop' : true },
		'ccp02a'	: { 'develop' : true },
		'ccp03a'	: { 'develop' : true },
		'ccp04a'	: { 'develop' : true },
		'ccp05a'	: { 'develop' : true },
		'optObjRename'	: { 'develop' : true },
		'optObjDelete'	: { 'develop' : true },
		'optObjNewfolder'	: { 'develop' : true },
		'optObjHelp'	: { 'develop' : true },
		'optObjProperties'	: { 'develop' : true },
		'optSubmitCallback'	: { 'develop' : true }
	}
};
var menuDisposition = {
	'{node:folder}'	: {
		'defaultOption' : 'optOpenFolder',
		'hideOptions' : { 
			'optOpenWith':true, 
			'optRotateLeft'		: { 'develop' : true },
			'optRotateRight'	: { 'develop' : true },
			'optDownloadToComputer':true, 
			'optOpenFile':true, 
			'optPictureViewer':true, 
			'optPictureEditor':true, 
			'ccp03a':true, 
			'ccp04a':true
		}
	},
	'{node:generic_or_unknown}'	: {
		'defaultOption' : 'optDownloadToComputer',
		'hideOptions' : { 'optOpenFile':true, 'optOpenFolder':true, 'optPictureViewer':true, 'optPictureEditor':true, 'ccp03a':true, 'ccp04a':true } 
	},
	'webimages'	: {
		'menuid' : 'thumbOptionsA', /* as of 1.0.3 this is undeveloped - this menu is hard-coded to appear related to the object */
		'defaultOption' : 'optPictureViewer',
		'hideOptions' : { 'optOpenFile':true, 'optOpenFolder':true, 'ccp03a':true, 'ccp04a':true } 
	},
	'selectedimage'	: {
		'menuid' : 'thumbOptionsA', /* as of 1.0.3 this is undeveloped - this menu is hard-coded to appear related to the object */
		'defaultOption' : 'optSubmitCallback',
		'hideOptions' : { 'optOpenFile':false, 'optOpenFolder':true, 'ccp03a':false, 'ccp04a':false } 
	},
	'openable_inbrowser' : {
		'menuid' : 'thumbOptionsA',
		'defaultOption' : 'optOpenFile',
		'hideOptions' : { 'optOpenFolder':true, 'optPictureViewer':true, 'optPictureEditor':true, 'ccp03a':true, 'ccp04a':true, 'optRotateRight':true, 'optRotateLeft':true } 
	}
};

//-------------------------------------------------------------------------

function hi2(node, multi, evt, event, grp){
	if(typeof event == "undefined")	event = window.event;
	if(cxl_hlt==1){cxl_hlt=0;return false;}
	if(typeof grp=='undefined')grp=1;
	if(typeof multi=='undefined')multi=0;
	if(typeof evt=='undefined')evt='';
	try{document.selection.empty();} catch(e){
		if(e.description){ alert('File: dataobjects_i1_v200.js\nLine: 26\n'+e.description);}
	}
	var eck=false;
	//solves a problem: JS says event is an object, but event.ctrlKey in some cases kills the script
	try{ var eck=event.ctrlKey; }catch(e2){ if(e2.description){ eck=false; }	}
	if(multi && !cbOverrideMulti && ((typeof event !=='undefined' && eck)) ){
		//control key pressed
		if(in_array(node, hl_grp, 11)){
			//the item is already in the array, revert its color and unset it
			if(g('view').value=='details'){
				g('node_'+node).className=g('node_'+node).className.replace(/\s*on/,'');
			}else if(g('view')=='thumbnails'){
				g('node_'+node).firstChild.className=g('view').value+'Box';
				g('name_'+node).className=g('view').value+'Box2';
			}else{
				g('node_'+node).firstChild.nextSibling.className=g('view').value+'Box';
				g('name_'+node).className=g('view').value+'Box2';
			}
			unset(node, 'hl_grp');
		}else{
			//change its color, add to the array and indicate the "last selected" item
			hl_grp[node]=node;
			if(g('node_'+node)) {
				if(g('view').value=='details'){
					var cn=g('node_'+node).className;
					cn=cn.replace(/\s*on/,'');
					g('node_'+node).className='on'+cn;		
				}else if(g('view').value=='thumbnails'){
					g('node_'+node).firstChild.className=g('view').value+'BoxHL';
					g('name_'+node).className=g('view').value+'Box2HL';
				}else{
					g('node_'+node).firstChild.nextSibling.className=g('view').value+'BoxHL';
					g('name_'+node).className=g('view').value+'Box2HL';
				}
			}
		}
	}else{
		//return if right-clicking over an existing hightlighted item
		for(j in hl_grp){
			if(j==node && evt==1)return;
		}
		//revert all elements in the array
		try{
		for(var i in hl_grp){
			if(g('view').value=='details'){
				g('node_'+i).className=g('node_'+i).className.replace(/\s*on/,'');
			}else if(g('view').value=='thumbnails'){
				g('node_'+i).firstChild.className=g('view').value+'Box';
				g('name_'+i).className=g('view').value+'Box2';
			}else{
				g('node_'+i).firstChild.nextSibling.className=g('view').value+'Box';
				g('name_'+i).className=g('view').value+'Box2';
			}
		}
		} catch(e){ }
		//unset the array
		hl_grp=new Array;
		//reset this item in the array
		hl_grp[node]=node;
		//change color
		if(g('view').value=='details'){
			var cn=g('node_'+node).className;
			cn=cn.replace(/\s*on/,'');
			g('node_'+node).className='on'+cn;		
		}else if(g('view').value=='thumbnails'){
			g('node_'+node).firstChild.className=g('view').value+'BoxHL';
			g('name_'+node).className=g('view').value+'Box2HL';
		}else{
			g('node_'+node).firstChild.nextSibling.className=g('view').value+'BoxHL';
			g('name_'+node).className=g('view').value+'Box2HL';
		}
	}
	if(disposition=='selector'){
		var h=[]; var hext=[]; var str=''; var str2='';
		hc=0;
		for(i in hl_grp){
			if(typeof folders[i]!=='undefined')continue;
			hc++;
			h[hc]=g('name_'+i).innerHTML;
			if(ext=g('node_'+i).getAttribute('extension'))hext[hc]=ext;
		}
		if(hc==1){
			g('cbSelector').value=h[1];	
			g('cbSelectorExt').value=hext[1];
		}else if(hc>1){
			var str='"'+h.join('" "')+'"';
			var str2='"'+hext.join('" "')+'"';
			var reg=/^"" /
			g('cbSelector').value=str.replace(reg,'');
			g('cbSelectorExt').value=str2.replace(reg,'');
		}
	}
}

function thumbOptionsACalc(){
	//precalcs the thumb options menu
}
var defaultCommand='';
function preContext(id){
	/* this is called on the double click of an item now also because the context determines the default dblclick action */
	var ext=[];
	var menuParams
	//get all extensions in the selected objects
	for(var i in hl_grp){
		if(g('node_'+i).getAttribute('folder')){
			//this is a folder
			ext[ext.length]='{node:folder}';
		}else if(str=g('node_'+i).getAttribute('extension')){
			ext[ext.length]=str.toLowerCase();
		}else{
			ext[ext.length]='{node:generic_or_unknown}';
		}
	}
	/*
	2009-02-14
	NOTE: the behavior of the menu could be quite varied based on the basket of selected items; we have the selected items and the lastSelectedItem as well.  For now we are using the lastSelectedItem to configure what does what
	
	homogenous|non-homogenous (gif & jpg's together are still homogenous)
	
	what I want to have happen is have several "engines" in the context menu and the desired engine shows, others hid, and even the label changed temporarily
	*/
	lastext=ext[ext.length-1];
	if(!docExtensionMapping[lastext]){
		//I NEED TO GET AN EMAIL ON THIS
		window.open('file_manager_01_exe.php?uid='+uid+'&mode=emailEmergency&folder='+g('folder').value+'&msg=unmapped_extension&extension='+lastext,'w4');
		lastext='{node:generic_or_unknown}';
	}
	menuParams=menuDisposition[ docExtensionMapping[lastext] ];
	
	for(var i in menuRegistry[id]){ //id !! here it should be dynamic!!
		//alert(i + ':' + (typeof menuParams['hideOptions'][i]));
		if(typeof menuParams['hideOptions'][i]!=='undefined'){
			try{
				g(i).style.display='none';
			}catch(e){
				//if(e.message)alert(i+':'+e.message);	
			}
		}else{
			g(i).style.display='block';	
			if(menuParams['defaultOption']==i){
				g(i).className='menuitems menudefault';
				defaultCommand=g(i).getAttribute('command');
			}else{
				g(i).className='menuitems';
			}
		}
	}


	var c=(copyStatus ? 'menuitems' : 'menuitems mndis');
	switch(id){
		case 'menubarEdit_m':
			//g('ccp03b').className=c;
			//g('ccp04b').className=c;
			g('ccp05b').className=c;
		break;
		case 'thumbOptionsA':
			//g('ccp03a').className=c;
			//g('ccp04a').className=c;
			g('ccp05a').className=c;
		break;
		case 'FSFocusOptionsA':
			g('ccp05c').className=c;
	}
}
function obj_edit(n){
	window.open('file_manager_01_exe.php?uid='+uid+'&mode=emailEmergency&folder='+g('folder').value+'&msg=called_image_editor','w4');
	alert('Not developed');
}
function edc(node,method){
	//execute default command

	//there must be a more elegant way to handle this
	preContext('thumbOptionsA');
	eval(defaultCommand);
}
function obj_open(n){
	var fileName='';
	for(node in hl_grp) fileName=g('name_'+node).innerHTML;
	var reg=/(jpg|gif|png)$/i;
	var f=g('folder').value;
	ext=g('node_'+node).getAttribute('extension');
	if((!ext && fileName.match(reg)) || (ext && ext.match(reg))){
		var str=HTTPRootFolderName+(f ? '/'+f : '')+'/'+fileName+(ext?'.'+ext:'');
		ow(str,'view_pictures','600,550');
	}else{
		var str=HTTPRootFolderName+(f ? '/'+f : '')+'/'+fileName+(ext?'.'+ext:'');
		ow(str,'view_'+n,'700,500');
	}
}
function obj_download(){
	var filename,ext,node
	for(node in hl_grp){
		ext=g('node_'+node).getAttribute('extension');
		filename=g('name_'+node).innerHTML+(ext?'.'+ext:'');
	}
	window.open('file_manager_01_exe.php?uid='+uid+'&mode=downloadToComputer&suppressPrintEnv=1&folder='+g('folder').value+'&node='+node+'&filename='+filename,'w3');
}
function obj_send(evt,mode){
	//handles: copy, cut, copymore, cutmore, download file, download zip
	switch(mode){
		case 'ccp_copy':
			msg='Copying selected files and folders';
		break;
		case 'ccp_cut':
			msg='Cutting selected files and folders';
		break;
		case 'ccp_copymore':
			msg='Adding selected files and folders to clipboard';
		break;
		case 'ccp_cutmore':
			msg='Cutting selected files and folders for the clipboard';
		break;
		case 'ccp_download':
			msg='Downloading selected file';
		break;
		case 'dlZip':
			msg='Creating a zip file from selected files and folders';
		break;
		case 'deleteObjects':
			if(!confirm('Delete the selected files? This action cannot be reversed.'))return;
			msg='Deleting selected files and folders';
			g('dels').value='';
			g('selNodes').value='';
			for(i in hl_grp){
				//2007-12-23 v0.9.37 - added remove vestige of html form
				txt=(g('name_'+i).innerHTML.indexOf('method="')!==-1 ? g('name_'+i).firstChild.innerHTML : g('name_'+i).innerHTML);
				g('dels').value+=txt;
				if(ext=g('node_'+i).getAttribute('extension'))g('dels').value+='.'+ext;
				g('dels').value+='/';
				g('selNodes').value+=i+',';
			}
		break;
		case 'ccp_paste':
			msg='Pasting items on the clipboard to this location';
			g('pasteOption').value=GetSourceElement(evt).getAttribute('id');
		break;
	}
	var sizes;
	g('sels').value='';
	if(mode!=='deleteObjects'){
		g('selNodes').value='';
		for(i in hl_grp){
			g('sels').value+=g('name_'+i).innerHTML
			if(ext=g('node_'+i).getAttribute('extension'))g('sels').value+='.'+ext;
			g('sels').value+='/';
			sizes+=parseFloat(g('name_'+i).getAttribute("size"));
			g('selNodes').value+=i+',';
		}
	}
	var buffer=g('mode').value;
	var buffer2=g('RootToolsForm').target;
	g('mode').value=mode;
	//todo: vary target according to action type
	g('RootToolsForm').target='w4';
	skipSubmitFail_02=true;
	beginSubmit(precallfunction);
	g('RootToolsForm').submit();
	g('mode').value=buffer;
	g('RootToolsForm').target=buffer2;
}
function obj_properties(){
	alert('Feature not developed as of version '+feVersion);
}
function obj_newfolder(){
	window.open('file_manager_01_exe.php?mode=newFolder&folder='+g('folder').value+'&currentFolder='+currentFolder+'&uid='+g('uid').value,'w1');
}
function gethelp_1(){
	ow('http://dev.compasspoint-sw.com/mediawiki-1.13.2/index.php?title=File_Explorer_Help','l2_help','width=600,height=500,scrollbars,resizable');
}
function obj_preprename(){
	for(i in hl_grp)idx=i;
	window.open('file_manager_01_exe.php?mode=preprename&folder='+g('folder').value+'&currentFolder='+currentFolder+'&uid='+g('uid').value+'&idx='+i,'w1');
}
function obj_rename(mode,n,event){
	if(mode=='blur' || mode=='submit'){
		var isfolder=g('node_'+n).getAttribute('folder');
		if(g('iname_'+n).value==g('iname_'+n).nextSibling.value){
			//file name unchanged
			g('name_'+n).innerHTML=g('iname_'+n).value;
			return false;
		}
		//check against recognized characterset is handled in exe file
		
		//check against extension change when called for
		if(!isfolder){
			if(o=g('origObjExt')){
				//parse file
				var ext=o.value.toLowerCase();
				var newext=g('iname_'+n).value.toLowerCase();
				if(newext.match(/\./)){
					newext=newext.split(/\./);
					newext=newext[newext.length-1];
					if(newext==ext){
						if(!confirm('You are renaming the file as "'+g('iname_'+n).value+'.'+o.value+'"\n\nAre you sure you want to do this?\n(NOTE: the file extension is added to the file automatically)\nClick OK to Continue, otherwise Cancel')){
							g('iname_'+n).focus();
							return false;
						}
					}
				}
			}else{
				var newext=g('iname_'+n).value.toLowerCase();
				if(!newext.match(/\./) && !confirm('You do not have an extension for the file your are renaming.  This may cause it to not work correctly.  Are you sure you want to continue? (OK to continue, otherwise Cancel)')){
					g('iname_'+n).focus();
					g('iname_'+n).select();
					return false;
				}
				var ext=g('origObjName').value.toLowerCase();
				if(ext.match(/\./) && newext.match(/\./)){
					ext=ext.split(/\./);
					ext=ext[ext.length-1];
					newext=newext.split(/\./);
					newext=newext[newext.length-1];
					if(newext!==ext && !confirm('You are changing the extension for the file you are renaming from "'+ext+'" to "'+newext+'"\n\nThis may cause it to not work correctly.  Are you sure you want to continue? (OK to continue, otherwise Cancel)')){
						g('iname_'+n).focus();
						g('iname_'+n).select();
						return false;
					}
				}
			}
		}
		if(mode=='submit'){
			return true;
		}else{
			g('iname_'+n).parentNode.submit();
		}
	}else if(mode=='keypress'){
		if(event.keyCode==27 /* escape */){
			g('name_'+n).innerHTML=g('origObjName').value;
			return false;
		}
	}
}
function ooF(node,method){
	if(typeof node=='undefined' || !node){
		for(var last in hl_grp){ } //get last one	
		node=last
	}
	typeof method=='undefined'? method=0 : '';
	if(method==6){
		//added 2009-03-31
		string=g('folder').value;
		msg='reloading current folder..';
	}else if(method==5){
		if(typeof node=='undefined')node='';
		string=node;
		msg='switching view..';
	}else if(method==3){
		string=g('currentLocation').value;
		msg='Loading typed-in folder..';
	}else if(method==2){
		var i=0;
		var string='';
		var x=g('folder').value+'';
		if(!x){
			alert('You are at the root folder');
			return;
		}
		var a = x.split('/');
		for(b in a){
			i++;
			if(i==a.length)break;
			string+=a[b]+'/';
		}
		string=string.substr(0,string.length-1);
		msg='Moving one folder higher..';
	}else if((cb=='select' || cb=='saveas') && method!==1 /* folder */){
		g('cbSelector').value=g('name_'+node).innerHTML;
		cbReply();
		return;
	}else if(method==1){
		//2007-12-23 v0.9.37 - added remove vestige of html form
		addFolder=(g('name_'+node).innerHTML.indexOf('method="')!==-1 ? g('name_'+node).firstChild.innerHTML : g('name_'+node).innerHTML);
		if(addFolder=='.thumbs.dbr'){
			alert('The folder .thumbs.dbr is a system folder and cannot be opened');
			return;
		}
		string=g('folder').value+(g('folder').value=='' ? '' : '/')+addFolder;
		msg='Loading folder..';
	}else if(method==4){
		//open file
		var fileName='';
		for(i in hl_grp) fileName=g('name_'+i).innerHTML;
		var reg=/(jpg|gif|png)$/i;
		ext=g('node_'+node).getAttribute('extension');
		if((!ext && fileName.match(reg)) || (ext && ext.match(reg))){
			obj_open();
			return;
		}
	}else{
		return;
	}
	window.open('file_manager_01_exe.php?uid='+g('uid').value+'&mode=loadFolder&method='+method+'&view='+g('view').value+'&folder='+string+'&disposition='+disposition,'w1');
	//beginSubmit(precallfunction);
	return;
}
function openFE(objName){
	var typeFilter, folder, srcField, multiple,t;
	this.setval=setval;
	this.doit=doit;
	this.closeit=closeit;
	function setval(varname, value, t){
		if(typeof t=='undefined')t='string';
		eval(varname+'='+(t=='string'?"'":'')+value+(t=='string'?"'":'')+';');
	}
	var wname;
	function doit(){
		if(!wname){
			wname=window.open('','getFile','width=600,height=600,resizable,statusbar,menubar');
		}
		wname.focus();
		wname.location='index.php?typeFilter='+typeFilter+'&folder='+g('folder').value+'&srcField='+srcField+'&multiple='+multiple;
	}
	function closeit(){
		wname.document.body.style.backgroundColor='RED';
		setTimeout('wname.window.close()',2000);
	}
}
function cbResponse(){
	/*
	function added 2007-03-07
	todo:
	make sure that field value is in the file listing for this folder - not developed
	remember to clear the cbSelector when we change folders
	*/
	var reg=/\*/;
	var i=g('cbSelector').value
	var j=g('cbSelectorExt').value
	if(i.match(reg)){
		alert('Wildcards not developed');
	}else if(!i){
		return false;
		//was this: window.close();
	}else{
		//we close window first so it's not hanging around when parent function executes
		if(cbTarget){
			try{
			window.opener.g(cbTarget).value=i;
			}catch(e){if(e.message || e.error){}}
		}
		if(cbTargetExt){
			try{
			window.opener.g(cbTargetExt).value=j;
			}catch(e){if(e.message || e.error){}}
		}
		if(cbTargetNode){
			try{
			window.opener.g(cbTargetNode).value=g('folder').value;
			}catch(e){if(e.message || e.error){}}
		}
		if(cbFunction){
			try{
			var str=('window.opener.'+cbFunction+'(\''+i.replace("'","\'")+'\',\''+g('folder').value.replace("'","\'")+'\',\''+j.replace("'","\'")+'\');');
			eval(str);
			}catch(e){
				if(e.message || e.error){
					alert(str);
					alert('Callback function call failed');
				}
			}
		}
		window.close();
	}
}
function cbReply(){
}
function cbFill(){
}
function contextUpdater(){
	//polices for system changes and makes updates
	g('foldersStats1').innerHTML=folderCount + ' folder'+(folderCount==1?'':'s');
	g('filesStats1').innerHTML=fileCount + ' file'+(fileCount==1?'':'s');
}
function obj_refresh(){
	window.open('file_manager_01_exe.php?mode=loadFolder&method=3&view='+g('view').value+'&uid='+uid+'&folder='+g('folder').value+'&disposition='+disposition,'w1');
	msg='Refreshing folder view..';
	beginSubmit(precallfunction);
	return;
}
function returntrue(){
	return true;
}



/* --------- view options gadget JS ------------- */
var vgS=false; var vgF=false;
function viewGadget(){
	g('viewMain').style.visibility='visible';
	setTimeout('hideviewMain()',1000);
	return false;
}
function hideviewMain(force){
	if(typeof force=='undefined')force=false;
	(vgS || vgF) && !force ? setTimeout('hideviewMain()',1000) : g('viewMain').style.visibility='hidden';
}
function hlView(o,out){
	if(typeof out=='undefined')out=1;
	o.className=(out?'vg viewOptionsOn':'vg viewOptionsOff');
}
function switchView(toggleview){
	if(toggleview==g('view').value)return;
	g('view').value=toggleview;
	g('viewGadgetIcon').src='i/view-'+toggleview+'.png';
	g('viewGadgetIcon').setAttribute('title','Switch folder view (currently '+toggleview+')');
	ooF(g('folder').value,5);
}
function clearBox(){
	if(!confirm('This will clear the bounding box from this folder.  Continue?'))return false;
	window.open('file_manager_01_exe.php?mode=clearBox&uid='+uid+'&folder='+g('folder').value,'w2');
}
function oF(node,method,string){
	typeof method=='undefined'? method=0 : '';
	if(method==4){
		string=string;
		msg='Loading folder..';
	}else if(method==3){
		string=g('currentLocation').value;
		msg='Loading typed-in folder..';
	}else if(method==2){
		var i=0;
		var string='';
		var x=g('folder').value+'';
		if(!x){
			alert('You are at the root folder');
			return;
		}
		var a = x.split('/');
		for(b in a){
			i++;
			if(i==a.length)break;
			string+=a[b]+'/';
		}
		string=string.substr(0,string.length-1);
		msg='Moving one folder higher..';
	}else if((cb=='select' || cb=='saveas') && method!==1 /* folder */){
		g('cbSelector').value=g('name_'+node).innerHTML;
		cbReply();
		return false;
	}else if(method==1){
		addFolder=(g('name_'+node).innerHTML);
		if(addFolder=='.thumbs.dbr'){
			alert('The folder .thumbs.dbr is a system folder and cannot be opened');
			return false;
		}
		string=g('folder').value+(g('folder').value=='' ? '' : '/')+addFolder;
		msg='Loading folder..';
	}else if(method==4){
		//open file
		var fileName='';
		for(i in hl_grp) fileName=g('name_'+i).innerHTML;
		var reg=/(jpg|gif|png)$/i;
		if(fileName.match(reg)){
			obj_open();
			return false;
		}
	}else{
		return false;
	}
	window.open('file_manager_01_exe.php?uid='+g('uid').value+'&mode=loadFolder&method='+method+'&folder='+string+'&view='+g('view').value+'&disposition='+disposition,'w1');
	//return beginSubmit(precallfunction);
}
function openFE(objName){
	var typeFilter, folder, srcField, multiple,t;
	this.setval=setval;
	this.doit=doit;
	this.closeit=closeit;
	function setval(varname, value, t){
		if(typeof t=='undefined')t='string';
		eval(varname+'='+(t=='string'?"'":'')+value+(t=='string'?"'":'')+';');
	}
	var wname;
	function doit(){
		if(!wname){
			wname=window.open('','getFile','width=600,height=600,resizable,statusbar,menubar');
		}
		wname.focus();
		wname.location='index.php?typeFilter='+typeFilter+'&folder='+g('folder').value+'&srcField='+srcField+'&multiple='+multiple;
	}
	function closeit(){
		wname.document.body.style.backgroundColor='RED';
		setTimeout('wname.window.close()',2000);
	}
}
function cbReply(){
	/*
	* 2011-05-22: note that the params are passed back fixed to the cbFunction as file,folder,*ext* for legacy sake
	function added 2007-03-07
	todo:
	make sure that field value is in the file listing for this folder - not developed
	remember to clear the cbSelector when we change folders
	*/
	var reg=/\*/;
	var i=g('cbSelector').value
	if(i.match(reg)){
		alert('Wildcards not developed');
	}else if(!i){
		window.close();
	}else{
		//we close window first so it's not hanging around when parent function executes
		//window.close();
		if(cbTarget){
			try{
			window.opener.g(cbTarget).value=i;
			}catch(e){if(e.message || e.error)alert('Unable to insert file value into parent window.');}
		}
		if(cbTargetNode){
			try{
			window.opener.g(cbTargetNode).value=g('folder').value;
			}catch(e){if(e.message || e.error)alert('Unable to insert folder value into parent window.');}
		}
		if(cbFunction){
			try{
			eval('window.opener.'+cbFunction+'(\''+i+'\',\''+g('folder').value+'\',\''+j+'\');');
			}catch(e){if(e.message || e.error)alert('Callback function call failed');}
		}
	}
}
function cbFill(){
	
}
function menubar(evt){
	hm_cxlseq=2;
	showmenuie5(evt);
	return false;
}
function adjContext(){

}
function open_folder(){
	ow('full.php?HTTPRootFolderName='+escape(HTTPRootFolderName)+'&folder='+escape(g('folder').value),'l1_fullsize','800,450');
}
function showSession(){
	if(!(goodSessionKey=gCookie('goodSessionKey'))){
		if(!(sessionKey=prompt('Enter the security code to open session',''))){
			alert('you must enter a session key');
			return false;
		}
	}
	thisKey=(goodSessionKey ? goodSessionKey : sessionKey);
	var sessiondata=ow('components/session.'+thisKey+'.php?node='+uid,'l2_session','600,700');
}

/* ------------ RootTools toolbar javascript -------------- */
function toolContextUpdater(internal){
	g('iDelete').className=(hl_grp.length ? 'noGhost' : 'ghost');
	
	//if only cut items are selected, we can't cut the same items again, so cut icon stays ghosted; however a copied item can be promoted to cut.  Cut trumps copy; if an item does manage to show up in session as both copied and cut, it will be cut (cuts are processed first)
	var canCut=false;
	for(var i in hl_grp){
		if(g('node_'+i).className.match(/\scut/))continue;
		canCut=true;
		break;
	}
	g('iCut').className=(canCut ? 'noGhost' : 'ghost');
	g('iCopy').className=(hl_grp.length ? 'noGhost' : 'ghost');
	g('iPaste').className=(copyStatus ? 'noGhost' : 'ghost');
	g('iPaste').parentNode.setAttribute('title', g('iPaste').className=='noGhost' ? 'Paste the copied and cut item(s) into this folder' : 'Clipboard is currently empty (no items to paste)');
	setTimeout('toolContextUpdater(true);',75);
}
AddOnloadCommand('toolContextUpdater();');
