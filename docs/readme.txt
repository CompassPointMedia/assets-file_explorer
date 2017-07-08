
Image Upload Manager
--------------------
version 0.9.35 - 
I have a paper folder on this feature in my files.  for ghosting folders I can make semitransparent the object.  Also would like to be able to print the list (need media;print then) with overflow, and ability to spawn a new window (if not in cb mode) which is something I have wanted to do in windows.
The layout needs rearranged ASAP per the paper drawing.

CALLBACK
this is similar to Windows where when you select file > open you get the system file opener which you can control as far as which folder opens, if they can select multiple and what types of files they'll see.  I started a simple version of this on 3/7/2007, with FE returning the string for the file(s) and the folder path.

version 0.9.1
2006-06-25: the navigation seems solid and the session object for the visible files looks good.  Next step will be some basic right-click options, then forward-back, then cut and paste. A next step will be displaying icons properly, and then having folders be ghosted or hidden.  

2006-06-23: ability to hi2() folders and files, see todo list for the many things yet to complete
Any questions or project requests please contact sam-git@compasspointmedia.com

This started as an upload manager in the shopping cart module, 0.9 makes it generic for any folder.

Make sure to set variables shown in image_manager_00_config.php.  You will then need to set write permissions on the folder which will contain the files.




Change Log
-------------------------------
2006-06-15:	boy this is old! now adding a feature where we get all images from relatebase.

Big changes today - basically just as easy as setting a systemRoot folder, and it will navigate all folders from there.  This will be my answer to "windows explorer" on the web - no db required, and you've got thumbnails!
	right now I can navigate and view images' thumbs but that's it.
	
So I have the opportunity to do this correctly, with the following standards
	1. any action will get the pending gif - never know how long
	2. all will have timeouts
	3. DOCS on what's changed by any action


2004-09-17	v0.9.1 -- changed system to first generate an array, in a new function array_file_list_i1(), and then use that to generate the file table, with appropriate sort.  Added varirable $initialSortColumn (set to appropriate column returned from this new function).  Also developed array_multisort_2d(), a way to sort a 2d array based on the value of a specific column, which will eventually grow to multiple columns.
