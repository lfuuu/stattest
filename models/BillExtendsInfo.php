<?php

namespace app\models;

use app\classes\model\ActiveRecord;

class BillExtendsInfo extends ActiveRecord
{

    public static function tableName()
    {
        return 'newbills_add_info';
    }

}