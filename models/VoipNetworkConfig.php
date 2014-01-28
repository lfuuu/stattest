<?php

class VoipNetworkConfig extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'voip';
    static $table_name = 'network_config';
    static $primary_key = array('id');
}
