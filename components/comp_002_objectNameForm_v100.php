<?php

//------------------ code block 49039 ----------------------
//handle extension showing
unset($extension);
preg_match('/(.+)\.([a-z0-9-]+)$/i',$v['name'],$a);
if(!$v['folder'] && $hideKnownExtensions && $hideKnownExtensions && $knownFileExtensions[strtolower($a[2])][0]){
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

//2009-02-08 js function requires removal of whitespace - filenames cannot contain a \n or \t
ob_start();
?><form target="w2" method="post" action="file_manager_01_exe.php?mode=nameobject&idx=<?php echo $i?>" onsubmit="return obj_rename('submit',<?php echo $i?>);">
	<input type="text" id="iname_<?php echo $i?>" name="objName" value="<?php echo h($name)?>" class="i1" onblur="obj_rename('blur',<?php echo $i?>)" onkeypress="obj_rename('keypress',<?php echo $i?>,event);" />
	<input type="hidden" name="origObjName" id="origObjName" value="<?php echo h($name)?>" />
<?php
if($removeExtension){
	?><input type="hidden" name="origObjExt" id="origObjExt" value="<?php echo $extension;?>" /><?php
}
?>	<input type="hidden" name="formNode" value="<?php echo h($node)?>" />
	<input type="hidden" name="idx" value="<?php echo $i?>" />
	<input type="hidden" name="folder" value="<?php echo h($folder)?>" />
	<input type="hidden" name="uid" value="<?php echo $uid?>" />
</form><?php
$out=ob_get_contents();
ob_end_clean();
$out=str_replace("\n",'',$out);
$out=str_replace("\t",'',$out);
echo $out;
?>