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

    public static function getList($businessProcessId = null)
    {
        $arr = self::find()
            ->andFilterWhere(['business_process_id' => $businessProcessId])
            ->all();
        return array_merge([0 => 'Не задано'], ArrayHelper::map($arr, 'id', 'name'));
    }
}