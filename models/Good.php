<?php
class Good extends ActiveRecord\Model
{
	static $table_name = 'g_goods';

	/**
	 * @deprecated
	 */
	public static function GetPrice($goodId, $priceTypeId)
	{
		global $db;

		list($goodId, $descrId) = explode(":", $goodId);

		if(!$descrId)
			$descrId = "00000000-0000-0000-0000-000000000000";

		$r = $db->GetRow("
                select * from g_good_price where good_id = '".$goodId."'
                and descr_id = '".$descrId."'
                and price_type_id ='".$priceTypeId."'");

		return $r["price"];
	}

	/**
	 * @deprecated
	 */
	public static function GetName($goodId){
		global $db;

		list($goodId, $descrId) = explode(":", $goodId);

		if(!$descrId)
			$descrId = "00000000-0000-0000-0000-000000000000";

		$r = $db->GetRow("select concat(g.name,if(d.name is not null,concat(' **',d.name) ,'')) name from g_goods g
                left join g_good_description d on (g.id = d.good_id and d.id = '".$descrId."')
                where g.id='".$goodId."'");
		return $r["name"];
	}

	public static function search($query, $limit = 100) {
		if (!$query)
			return array();

		$goods = self::find(
			'all',
			array(
				'conditions' => array('name like concat("%", ? ,"%")', $query),
				'limit' => $limit,
			)
		);

		if ((int)$query > 0) {
			$good = self::find(
				'first',
				array(
					'conditions' => array('num_id = ?', (int)$query),
				)
			);
			if ($good) array_unshift($goods, $good);
		}
		return $goods;
	}
}
