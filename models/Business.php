<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * Class Business
 *
 * @property int $id
 * @property string $name
 * @property int $sort
 *
 * @property BusinessProcess $BusinessProcesses
 */
class Business extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const TELEKOM = 2;
    const OPERATOR = 3;
    const PROVIDER = 4;
    const INTERNET_SHOP = 5;
    const INTERNAL_OFFICE = 6;
    const PARTNER = 7;
    const WELLTIME = 8;
    const ITOUTSOURSING = 9;
    const OTT = 10;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_contract_business';
    }

    /**
     * @return ActiveQuery
     */
    public static function find()
    {
        return parent::find()->orderBy(['sort' => SORT_ASC]);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['sort' => SORT_ASC],
            $where = []
        );
    }

    /**
     * Связанные бизнес процессы
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBusinessProcesses()
    {
        return $this->hasMany(BusinessProcess::className(), ['business_id' => 'id']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
