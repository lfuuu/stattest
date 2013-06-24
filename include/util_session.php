<?
function session_get($name) {
	global $_SESSION;	
	return $_SESSION[$name];	
}
function session_is_set($name) {
	global $_SESSION;	
	return isset($_SESSION[$name]);
}
function session_set($name, $val) {
	global $_SESSION,$SessionWriteClosed;
	if (isset($SessionWriteClosed)) {
		session_start();
		unset($SessionWriteClosed);
	}
	$_SESSION[$name]=$val;
}
	
?>
