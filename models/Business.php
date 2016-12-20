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
class Business extends ActiveRecord
{

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const TELEKOM = 2;
    const OPERATOR = 3;
    const PROVIDER = 4;
    const INTERNET_SHOP = 5;
    const INTERNAL_OFFICE = 6;
    const PARTNER = 7;
    const WELLTIME = 8;
    const ITOUTSOURSING = 9;

    public static function tableName()
    {
        return 'client_contract_business';
    }

    /**
     * По какому полю сортировать для getList()
     * @return array
     */
    public static function getListOrderBy()
    {
        return ['sort' => SORT_ASC];
    }

    public function getBusinessProcesses()
    {
        return $this->hasMany(BusinessProcess::className(), ['business_id' => 'id']);
    }
}
