<?php

class VoipNetworkType extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'voip';
    static $table_name = 'network_type';
    static $primary_key = array('id');

    static $cachedList;

    static public function getListAssoc()
    {
        if (!self::$cachedList) {
            self::$cachedList = array();
            foreach(self::find('all', array('order' => 'id')) as $item) {
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
