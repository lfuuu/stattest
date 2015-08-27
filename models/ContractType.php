<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property int $id
 * @property string $name
 * @property int $sort
 * @property
 */
class ContractType extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contract_type';
    }

    public static function getList()
    {
        $arr = self::find()->all();
        return array_merge(ArrayHelper::map($arr, 'id', 'name'), [0 => 'Не выбрано']);
    }
}