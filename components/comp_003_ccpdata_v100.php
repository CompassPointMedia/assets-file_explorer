<script id="ccpjs" type="text/javascript" language="javascript"><?php 
/*
2008-02-23
work with the last object in ccp history
this is taken from kjv3 - we have removed copyObject and copyParentObject as not relevant, however we have added copy count.  With the new "copy more" protocol, copy mode can be mixed:
	1 = copy (multiple) objects single folder
	2 = copy object(s) multiple folders
	4 = cut object(s) single folder
	8 = cut objects(s) multiple folders
with copy more protocol you can both copy and cut simultaneously


*/
$ccpIdx=count($_SESSION['file_explorer'][$uid]['history']['ccp']);
if($ccpIdx && !$_SESSION['file_explorer'][$uid]['history']['ccp'][$ccpIdx]['completed']){
	$copyStatus='true';
	$ccpObj=$_SESSION['file_explorer'][$uid]['history']['ccp'][$ccpIdx];
	$copyMode=$ccpObj['copyMode'];
	$copyCount=count($ccpObj['objects']);
}else{
	$copyStatus='false';
	$copyMode=0;
}
?>
//initial cut-copy status
var copyStatus=<?php echo $copyStatus?>;
var copyMode=<?php echo $copyMode>0 ? $copyMode : 0?>;
var copyCount=<?php echo $copyCount ? $copyCount : 0?>;
</script>