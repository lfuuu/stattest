<?php

namespace app\models\important_events;

use app\classes\model\ActiveRecord;

/**
 * Class ImportantEventsGroups
 *
 * @property string $title
 */
class ImportantEventsGroups extends ActiveRecord
{
    const ID_FINANCIAL = 6;
    const ID_ACCOUNT = 8;

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'important_events_groups';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['title',], 'required'],
            [['title',], 'trim'],
        ];
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
            $select = 'title',
            $orderBy = ['id' => SORT_ASC],
            $where = []
        );
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'title' => 'Название',
        ];
    }
}