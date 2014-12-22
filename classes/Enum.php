<?php
namespace app\classes;

use yii\base\Object;

class Enum extends Object
{
    protected static $names = [];

    public static function getNames()
    {
        return static::$names;
    }

    public static function getKeys()
    {
        return array_keys(static::$names);
    }

    public static function hasKey($key)
    {
        return isset(static::$names[$key]);
    }

    public static function getName($key)
    {
        return isset(static::$names[$key]) ? static::$names[$key] : $key;
    }
}