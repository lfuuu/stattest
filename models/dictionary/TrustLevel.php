<?php

namespace app\models\dictionary;

use app\classes\model\ActiveRecord;
use app\classes\validators\FormFieldValidator;

/**
 * @property int $id
 * @property int $name
 * @property int $order
 */
class TrustLevel extends ActiveRecord
{
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'dict_trust_level';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name',], 'required'],
            [['name', ], 'string'],
            [['name', ], FormFieldValidator::class],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'id' => 'ID',
            'order' => 'Сортировка',
        ];
    }

    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $indexBy = 'id',
        $select = 'name',
        $orderBy = ['order' => SORT_ASC],
        $where = []
    ) {
        return self::getListTrait($isWithEmpty, $isWithNullAndNotNull, $indexBy, $select, $orderBy);
    }
}
