<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * @property int $id
 * @property
 */
class ClientBPStatuses// extends ActiveRecord
{
    const TELEKOM__SALE__INCOME = 31; //Телеком - Продажи - Входящие

    const TELEKOM__SUPPORT__ORDER_OF_SERVICES = 19; //Телеком - Сопровождение - Заказ услуг
    const TELEKOM__SUPPORT__CONNECTED = 8; //Телеком - Сопровождение - Подключаемые
    const TELEKOM__SUPPORT__WORK = 9; //Телеком - Сопровождение - Включенные

    const INTERNAL_OFFICE = 34; //Внутренний офис -> Внутренний офис -> Внутренний офис

/*
    public static function tableName()
    {
        return 'client_grid_statuses';
    }*/
}
