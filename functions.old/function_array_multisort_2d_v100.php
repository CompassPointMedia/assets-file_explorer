<?php
function array_multisort_2d($array,$column,$sortorder='',$base=1){
	/*** shame this function isn't present with php, this sorts on a sub-column value ***/
	global $array_multisort_2d;
	if(!$array)return $array;

	foreach($array as $n=>$v){
		//develop link based on column
		$ref[]=strtolower($v[$column]);
		$refb[]=$n;
	}
	//need to develop to arsort if called
	asort($ref);
	$base=$base-1;
	foreach($ref as $n=>$v){
		$base++;
		$buffer[$base]=$array[$refb[$n]];
	}
	return $buffer;
}
/*********************
example:
$a[name]='haliburton';
$a[size]=58383;
$a[description]='what dick cheney worked for';
$fileList[]=$a;

$a[name]='Charles river';
$a[size]=9999;
$a[description]='what I work for';
$fileList[]=$a;

$a[name]='charles dodson';
$a[size]=25;
$a[description]='test';
$fileList[]=$a;

$a[name]='winston churchill';
$a[size]=68294;
$a[description]='test2';
$fileList[]=$a;

print_r(array_multisort_2d($fileList,'name'));
*********************/


?>