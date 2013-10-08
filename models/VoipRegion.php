<?php

class VoipRegion extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'geo';
    static $table_name = 'region';
}
