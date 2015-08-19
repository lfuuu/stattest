<?php
namespace app\models;

use yii\db\ActiveRecord;

class CodeOpf extends ActiveRecord
{

    public static function tableName()
    {
        return 'code_opf';
    }

    public static function getList()
    {
        $list = [0 => 'Не выбрано'];
        foreach(self::find()->all() as $model)
            $list[$model->id] = /*$model->code.' '.*/ $model->name;
        return $list;
    }

}
