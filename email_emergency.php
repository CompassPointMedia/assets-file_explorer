<?php
/* image upload manager v0.9 -- see readme.txt file */
$bufferDocument=false;
$relatebase=false;// this is a standalone object, set to true to integrate with a login system

$localSys['scriptID']='email for abnormal shutdown';

$configPathReplace='file_manager_00_includes.php';
require(str_replace($configPathReplace,'',__FILE__).'config.php');

echo 'emailing problem';
?>