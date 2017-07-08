<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>FE Layout</title>
<link href="Library/simple_100.css" rel="stylesheet" type="text/css" />
<link href="Library/contextmenu_v400.css" rel="stylesheet" type="text/css" />
<script id="jsglobal" type="text/javascript" src="Library/js/global_04_i1.js"></script>
<script id="jscommon" type="text/javascript" src="Library/js/common_04_i1.js"></script>
<script id="jsforms" type="text/javascript" src="Library/js/forms_04_i1.js"></script>
<script id="jsloader" type="text/javascript" src="Library/js/loader_04_i1.js"></script>
<script id="jsdataobjects" type="text/javascript" src="Library/js/dataobjects_04_i1.js"></script>
<script id="jscontextmenus" type="text/javascript" src="Library/js/contextmenus_04_i1.js"></script>
<script id="jsgeneral" src="Library/general_v100.js"></script>
<?php 
require($FEX_ROOT.'/components/comp_003_ccpdata_v100.php');
?>
<script language="javascript" type="text/javascript">
var feVersion='<?php echo $feVersion?>';
var thispage = 'index.php';
var thisfolder = '';
var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime=time();?>';
var folder ='<?php echo $folder?>';
var cb = '<?php echo $cb?>';
var cbTarget='<?php echo $cbTarget?>';
var cbTargetNode='<?php echo $cbTargetNode?>';
var cbFunction='<?php echo $cbFunction?>';
var cbOverrideMulti=<?php echo isset($cbMultiple) && $cbMultiple=='0'?'true':'false'?>;
var view='<?php echo $view?>';
var HTTPRootFolderName='<?php echo $HTTPRootFolderName?>';
var currentFolder='<?php echo $currentFolder?>';
var uid='<?php echo $uid?>';
var folders=new Array();
var testModeC=<?php echo $testModeC?$testModeC:0?>;
<?php
if(count($folders)){
	$i=0;
	foreach($folders as $n=>$v){
		/***
		We can safely increment i and be sure the node numbers will be the same as the session nodes which will later be assigned
		***/
		$i++;
		?>folders[<?php echo $i?>]=<?php echo $i?><?php echo ";\n";
	}
}
?>
var fileCount=<?php echo $fileCount ? $fileCount : '0';?>;
var folderCount=<?php echo $folderCount ? $folderCount : '0';?>;

function oB(o){
	ow('popup.php?testModeC='+testModeC+'&HTTPRootFolderName='+HTTPRootFolderName+'&folder='+folder+'&layout=box&uid='+uid,'l2_popup','600,300');
}
function oF(node,method){
	typeof method=='undefined'? method=0 : '';
	if(method==3){
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
		addFolder=(g('name_'+node).innerHTML);
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
		if(fileName.match(reg)){
			obj_open();
			return;
		}
	}else{
		return;
	}
	window.open('file_manager_01_exe.php?mode=loadFolder&method='+method+'&uid='+g('uid').value+'&folder='+string,'w1');
	return beginSubmit(precallfunction);
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
		wname.location='index.php?typeFilter='+typeFilter+'&folder='+folder+'&srcField='+srcField+'&multiple='+multiple;
	}
	function closeit(){
		wname.document.body.style.backgroundColor='RED';
		setTimeout('wname.window.close()',2000);
	}
}
function cbReply(){
		/*
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
		window.close();
		if(cbTarget){
			try{
			window.opener.g(cbTarget).value=i;
			}catch(e){if(e.message || e.error)alert('Unable to insert file value into parent window.');}
		}
		if(cbTargetNode){
			try{
			window.opener.g(cbTargetNode).value=folder;
			}catch(e){if(e.message || e.error)alert('Unable to insert folder value into parent window.');}
		}
		if(cbFunction){
			try{
			eval('window.opener.'+cbFunction+'(\''+i+'\',\''+folder+'\');');
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
	ow('full.php?HTTPRootFolderName='+escape(HTTPRootFolderName)+'&folder='+escape(folder),'l1_fullsize','800,450');
}
</script>

</head>

<body>
<br />
<br />
<br />
<br />
<br />
<br />

<div id="menubar">
	<ul>
		<li><a href="javascript:;" id="menubarEdit" accesskey="e" onmouseover="hm_cxlseq=2;showmenuie5(event)"><u>E</u>dit</a></li>
		<li><a href="javascript:;" id="menubarTools" accesskey="t" onmouseover="hm_cxlseq=2;showmenuie5(event)"><u>T</u>ools</a></li>
	</ul>
	<div id="menubarEdit_m" class="menuskin1" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onclick="executemenuie5(event)" precalculated="preContext('menubarEdit_m')">
		<div id="" class="menuitems" command="open_folder()" style="font-weight:900;" status="See full-size of this folder">Open full-size</div>
		<hr class="mhr"/>
		<div id="ccp01b" class="menuitems" command="obj_send(event,'ccp_cut')" status="Cut this file or folder to the clipboard">Cut</div>
		<div id="ccp02b" class="menuitems" command="obj_send(event,'ccp_copy')" status="Copy this file or folder to the clipboard">Copy</div>
		<div id="ccp03b" class="menuitems" command="obj_send(event,'ccp_cutmore')" status="Cut More">Cut More</div>
		<div id="ccp04b" class="menuitems" command="obj_send(event,'ccp_copymore')" status="Copy More">Copy More</div>
		<div id="ccp05b" class="menuitems" command="obj_send(event,'ccp_paste')" status="Paste">Paste</div>
		<hr class="mhr"/>
		<div id="mrename" class="menuitems" command="obj_preprename()" status="Rename">Rename</div>
		<div id="mdelete" class="menuitems" command="obj_send(event,'deleteObjects')" status="Delete">Delete</div>
		<hr class="mhr"/>
		<div id="mprops" class="menuitems" command="obj_properties()" status="Properties">Properties</div>
	</div>
	<div id="menubarTools_m" class="menuskin1" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onclick="executemenuie5(event)" precalculated="preContext('menubarTools_m')">
		<div id="mbox" class="menuitems" command="obj_box(event)" status="">Box this folder</div>
		<hr class="mhr"/>
		<div id="mthumb" class="menuitems" command="obj_thumb(event)" status="">Make Thumbnails</div>
		<div id="mscrunch" class="menuitems" command="obj_scrunch(event)" status="">Scrunch this folder</div>
	</div>
	<script language="javascript" type="text/javascript">
	AssignMenu('menubarEdit', 'menubarEdit_m');
	AssignMenu('menubarTools', 'menubarTools_m');
	menuAlign['menubarEdit']='bottomleftalign';
	menuAlign['menubarTools']='bottomleftalign';
	</script>

</div>


<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />

</body>
</html>
