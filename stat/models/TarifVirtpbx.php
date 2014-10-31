<?php

class TarifVirtpbx extends ActiveRecord\Model
{
    static $table_name = "tarifs_virtpbx";
    static $private_key = 'id';
    
    public static function getTarifByClient($client_id, $time)
    {
	$options = array();
	$options['select'] = 'T.*';
	$options['from'] = 'clients as C';
		
	$options['joins'] = 
		'LEFT JOIN usage_virtpbx as UV ON UV.client = C.client ' . 
		'LEFT JOIN log_tarif as LT ON UV.id = LT.id_service  ' . 
		'LEFT JOIN tarifs_virtpbx as T ON LT.id_tarif = T.id '
		;

	$condition_string = "
		LT.id = (
			SELECT id 
			FROM log_tarif as b
			WHERE
				date_activation = (
					SELECT MAX(date_activation)
					FROM log_tarif 
					WHERE 
						'" . date('Y-m-d', $time) . "' >= date_activation AND 
						service = 'usage_virtpbx' AND 
						id_service = b.id_service
					) AND 
				id_service = LT.id_service
			ORDER BY
					ts desc
			LIMIT 0,1
		) 
                AND C.id = ? 
                AND LT.service = ?
                AND '" . date('Y-m-d', $time) . "' BETWEEN UV.actual_from AND UV.actual_to";
		
	$condition_values = array(
		$client_id,
		'usage_virtpbx'
	);
	
	$options['conditions'] = array($condition_string);
	foreach ($condition_values as $v) 
	{
		$options['conditions'][] = $v;
	}
	$tarif_info = self::first($options);
	return $tarif_info;
    }
}
