<?php

class VoipCountry extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'geo';
    static $table_name = 'country';
}
