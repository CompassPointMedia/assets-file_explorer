<html>
<head>
<title>Get a File from File Explorer</title>
<meta name="Description" content="File System Management" />
<meta name="Keywords" content="Compass Point Media, Advanced Graphics and Database development/integration" />
<link href="Library/simple_100.css" rel="stylesheet" type="text/css" />
<link href="Library/contextmenu_v400.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="javascript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script src="Library/general_v100.js"></script>
<script language="javascript" type="text/javascript">
function example(file, node){
	alert('This is a callback function.  You selected the file '+file+', in folder '+node);
}
function cb(cbMode,cbTarget,cbTargetNode,cbMultiple,cbFolder,cbFunction){
	//we open a connection between this window and file explorer.
	//should each window have the same name?
	var wName='callback';
	var cb=window.open('index.php?uid=callback&disposition='+cbMode+'&cbTarget='+cbTarget+'&cbTargetNode='+cbTargetNode+'&cbMultiple='+(cbMultiple?1:0)+'&folder='+(typeof cbFolder!=='undefined' ? cbFolder : '')+'&cbFunction='+cbFunction,wName,'width=500,height=500,resizable,status,menubar');
	cb.focus();
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<input name="iwantit" type="text" id="iwantit">
<input name="folder" type="text" id="folder">
<input type="submit" name="Submit" value="Select Image" onClick="cb('selector','iwantit','folder',true,'','example');">
</body>
</html>
