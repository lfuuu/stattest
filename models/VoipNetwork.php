<?php

class VoipNetwork extends ActiveRecord\Model
{
    static $connection = 'voip';
    static $db = 'voip';
    static $table_name = 'network';
    static $primary_key = array('id');
}
