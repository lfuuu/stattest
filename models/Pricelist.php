<?php

class Pricelist extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'voip';
    static $table_name = 'pricelist';
    static $sequence = 'voip.pricelist_id_seq';

    static $cachedList;

    static $belongs_to = array(
        array('xxx', 'class_name' => 'Region', 'foreign_key' => 'region'),
        array('operator', 'class_name' => 'VoipOperator', 'foreign_key' => array('region', 'operator_id')),
    );

    static public function getListAssoc()
    {
        if (!self::$cachedList) {
            self::$cachedList = array();
            foreach(self::all() as $item) {
                self::$cachedList[$item->id] = $item;
            }
        }
        return self::$cachedList;
    }

    static public function getCachedById($id)
    {
        self::getListAssoc();

        if (isset(self::$cachedList[$id])) {
            return self::$cachedList[$id];
        }

        throw new \ActiveRecord\RecordNotFound;
    }

    public function getRegionName()
    {
        if ($this->region) {
            return Region::getCachedById($this->region)->name;
        } else {
            return null;
        }
    }

    public function getOperator()
    {
        $operator = VoipOperator::getByIdAndInstanceId($this->operator_id, $this->region);

        if ($operator === null) {
            $operator = VoipOperator::getByIdAndInstanceId($this->operator_id, 0);
        }

        return $operator;
    }

    public function xxx()
    {
        var_dump('ddd');
    }
}
