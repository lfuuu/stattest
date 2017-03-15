<?php
namespace app\models;

use app\dao\CourierDao;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $name
 * @property string $enabled
 */
class Courier extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'courier';
    }

    /**
     * @return CourierDao
     */
    public static function dao()
    {
        return CourierDao::me();
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $depart
     * @return string[]
     * @internal param bool $isWithNullAndNotNull
     */
    public static function getList(
        $isWithEmpty = false,
        $depart = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = [
                'AND',
                ['enabled' => 'yes'],
                $depart ? ['depart' => 'Курьер'] : []
            ]
        );
    }
}