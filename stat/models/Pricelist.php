<?php

class Pricelist extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'voip';
    static $table_name = 'pricelist';
    static $sequence = 'voip.pricelist_id_seq';
    static $primary_key = 'id';

    static $cachedList;

    public function __construct(array $attributes=array(), $guard_attributes=true, $instantiating_via_find=false, $new_record=true)
    {
        parent::__construct($attributes, $guard_attributes, $instantiating_via_find, $new_record);

        if ($new_record) {
            $this->assign_attribute('id', null);
            $this->assign_attribute('region', null);
            $this->assign_attribute('name', null);
            $this->assign_attribute('tariffication_by_minutes', null);
            $this->assign_attribute('tariffication_full_first_minute', null);
            $this->assign_attribute('initiate_mgmn_cost', null);
            $this->assign_attribute('initiate_zona_cost', null);
            $this->assign_attribute('type', null);
        }
    }

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
}
