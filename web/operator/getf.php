<?php

if (isset($_GET["file"]))
{

	if(file_exists($_GET["file"]))
	{
		header("Content-type: xxx/xxx");
		exec("cat ".$_GET["file"], $out);
	/*	
		echo "<pre>";
		print_r($out);
		echo "</pre>";
	*/
		echo implode("\n", $out);
	}
}





