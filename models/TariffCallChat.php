<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 */
class TariffCallChat extends ActiveRecord
{

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const CALL_CHAT_TARIFF_STATUS_PUBLIC = 'public';
    const CALL_CHAT_TARIFF_STATUS_SPECIAL = 'special';
    const CALL_CHAT_TARIFF_STATUS_ARCHIVE = 'archive';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tarifs_call_chat';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param string $currencyId
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @return string[]
     */
    public static function getList(
        $currencyId,
        $isWithEmpty = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'description',
            $orderBy = ['description' => SORT_ASC],
            $where = ['currency_id' => $currencyId]
        );
    }
}