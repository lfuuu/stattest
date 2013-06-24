<?php
class DefInfo
{
	var $country_zones;
	var $city_region_zones;
	var $mob_zones;
	
	function __construct() {
		global $pg_db;
		$this->country_zones = $pg_db->AllRecords(' SELECT gz.prefix, g.country FROM geo.geo g
                                                LEFT JOIN geo.geo_prefix gz on g.id=gz.geo_id
                                                WHERE g.prefix is not null and g.country is not null and g.country=g.id
                                                ORDER BY gz.prefix  ');
		$this->region_zones = $pg_db->AllRecords(' SELECT gz.prefix, g.region FROM geo.geo g
                                                    LEFT JOIN geo.geo_prefix gz on g.id=gz.geo_id
                                                    WHERE g.prefix is not null and g.region is not null  and g.region=g.id
                                                    ORDER BY gz.prefix  ');
		$this->mob_zones = $pg_db->AllRecords('SELECT prefix FROM geo.mob_prefix ORDER BY prefix');
	}
	
	function get_country($phone){
		$minmin = 0;
		$maxmax = sizeof($this->country_zones) - 1;
		$nnn = 0;
		$last_def = null;
		for ($i = 0; $i < strlen($phone); $i = $i + 1){
			$min = $minmin;
			$max = $maxmax;
			$str = substr($phone, 0, $i+1); 
			while($min < $max) {
				$pos = (($min + $max) - ($min + $max) % 2) / 2;
				if(strcmp($str,$this->country_zones[$pos]['prefix']) <= 0)
					$max = $pos;
				else
					$min = $pos + 1;
					
				$cmp = strcmp($str,substr($this->country_zones[$pos]['prefix'], 0, $i+1));
				if($cmp < 0) $maxmax = $pos - 1; 
				if($cmp > 0) $minmin = $pos + 1; 
				
				$nnn = $nnn + 1;
			}
			if ($this->country_zones[$max]['prefix'] == $str){
				$last_def = $this->country_zones[$max];
			}
		}
		if ($last_def != null)
			return $last_def['country'];
		else
			return null;		
	}

	function get_region($phone){
		$minmin = 0;
		$maxmax = sizeof($this->region_zones) - 1;
		$nnn = 0;
		$last_def = null;
		for ($i = 0; $i < strlen($phone); $i = $i + 1){
			$min = $minmin;
			$max = $maxmax;
			$str = substr($phone, 0, $i+1); 
			while($min < $max) {
				$pos = (($min + $max) - ($min + $max) % 2) / 2;
				if(strcmp($str,$this->region_zones[$pos]['prefix']) <= 0)
					$max = $pos;
				else
					$min = $pos + 1;
					
				$cmp = strcmp($str,substr($this->region_zones[$pos]['prefix'], 0, $i+1));
				if($cmp < 0) $maxmax = $pos - 1; 
				if($cmp > 0) $minmin = $pos + 1; 
				
				$nnn = $nnn + 1;
			}
			if ($this->region_zones[$max]['prefix'] == $str){
				$last_def = $this->region_zones[$max];
			}
		}
		if ($last_def != null)
			return $last_def['region'];
		else
			return null;		
	}			
	
	
	function get_mob($phone){
		$minmin = 0;
		$maxmax = sizeof($this->mob_zones) - 1;
		$nnn = 0;
		$last_def = null;
		for ($i = 0; $i < strlen($phone); $i = $i + 1){
			$min = $minmin;
			$max = $maxmax;
			$str = substr($phone, 0, $i+1); 
			while($min < $max) {
				$pos = (($min + $max) - ($min + $max) % 2) / 2;
				if(strcmp($str,$this->mob_zones[$pos]['prefix']) <= 0)
					$max = $pos;
				else
					$min = $pos + 1;
					
				$cmp = strcmp($str,substr($this->mob_zones[$pos]['prefix'], 0, $i+1));
				if($cmp < 0) $maxmax = $pos - 1; 
				if($cmp > 0) $minmin = $pos + 1; 
				
				$nnn = $nnn + 1;
			}
			if ($this->mob_zones[$max]['prefix'] == $str){
				$last_def = $this->mob_zones[$max];
			}
		}
		return ($last_def != null);		
	}
}