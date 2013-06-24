<?php
function smarty_modifier_div($what,$on,$len=false){
	if(!$len)
		return (int)($what / $on);
	else
		return round($what/$on,$len);
}
?>