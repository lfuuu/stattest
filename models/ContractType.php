<?php

namespace app\models;

use app\classes\model\ActiveRecord;

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
     * @param bool $isWithEmpty
     * @return \string[]
     */
    public static function getList($businessProcessId = null, $isWithEmpty = false)
    {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = ['business_process_id' => $businessProcessId]
        );
    }
}