<?php
function fs_dir_size($dir = false){
	/*** originally made for the simple image upload manager	***/
	global $fs_dir_size;
	//if dir present and it is a directory
	if($dir && is_dir($dir)){
		//add trailing slash
		if(substr($dir,-1) != "/"){ $dir .= "/";}
		// open directory
		if($dir_ID = opendir($dir)){
			// loop through contents dir
			while (($item = readdir($dir_ID)) !== false){
				//exclude . and ..
				if($item != "." && $item != ".."){
					if(is_dir($dir . $item)){
						//recurse function
						$size += fs_dir_size($dir . $item);
					} else {
						$size += filesize($dir . $item);
					}
				}
			}
			closedir($dir_ID);
		}
	}else{
		$fs_dir_size['err']=='invalid directory name';
		return -1;
	}
	return $size;
}
?>