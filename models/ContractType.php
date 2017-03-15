<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 */
class ContractType extends ActiveRecord
{

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_contract_type';
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param int $businessProcessId
     * @return \string[]
     */
    public static function getList($businessProcessId = null)
    {
        return self::getListTrait(
            $isWithEmpty = false,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = ['business_process_id' => $businessProcessId]
        );
    }
}