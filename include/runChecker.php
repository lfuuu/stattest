<?php

class runChecker
{
	protected static $pidFile = "";//"/var/run//".$_SERVER["PHP_SELF"].".running";

	function isRun()
	{
		self::$pidFile = "/tmp/".$_SERVER["PHP_SELF"].".pid";

		clearstatcache();
		if(file_exists(self::$pidFile))
		{
			$a = file_get_contents(self::$pidFile);
			$a = explode(",",$a);
			if($a)
			{
				$out = array();
				exec("ps ax", $out);
				foreach($out as $l)
				{
					$l = trim($l); $l = explode(" ",$l); $l = $l[0]; 
					if(in_array($l, $a))
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	function run()
	{
		$out = array();

		exec("ps ax | grep ".$_SERVER["PHP_SELF"]." | grep -v grep", $out);

		$a = array();

		foreach($out as $l)
		{
			$l = trim($l);
			$l = explode(" ",$l);
			$a[] = $l[0];
		}

		file_put_contents(self::$pidFile, implode($a,","));
	}

	function stop()
	{
		unlink(self::$pidFile);
	}
}

