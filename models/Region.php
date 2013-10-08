<?php

class Region extends ActiveRecord\Model
{
    static $table_name = 'regions';

    static $cachedList;

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
}
