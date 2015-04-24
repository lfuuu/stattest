<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * @property int $id
 * @property
 */
class ClientBPStatuses extends ActiveRecord
{
    const TELEKOM__SUPPORT__WORK = 9; //Включенные
    const TELEKOM__SUPPORT__ORDER_OF_SERVICES = 19; //Заказ услуг
    const TELEKOM__SUPPORT__CONNECTED = 9; //Подключаемые

    public static function tableName()
    {
        return 'client_grid_statuses';
    }
}
