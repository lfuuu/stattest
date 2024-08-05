<?php

namespace app\models;

use app\classes\model\ActiveRecord;

class TechPort extends ActiveRecord
{
    public static $portTypes = [
        'backbone',
        'dedicated',
        'pppoe',
        'pptp',
        'hub',
        'adsl',
        'wimax',
        'cdma',
        'adsl_cards',
        'adsl_connect',
        'adsl_karta',
        'adsl_rabota',
        'adsl_terminal',
        'adsl_tranzit1',
        'yota',
        'GPON',
        'megafon_4G',
        'mts_4G',
    ];

    public static function tableName()
    {
        return 'tech_ports';
    }
}