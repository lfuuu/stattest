<?php

namespace app\models;

use app\classes\model\ActiveRecord;

class CodeOpf extends ActiveRecord
{

    const IP = 3;

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'code_opf';
    }
}
