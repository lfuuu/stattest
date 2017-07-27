<?php

namespace app\models;

use app\classes\behaviors\NotifiedFlagToImportantEvent;
use app\classes\model\ActiveRecord;

/**
 * Class ClientFlag
 *
 * @property integer $account_id
 * @property integer $is_notified_7day
 * @property integer $is_notified_3day
 * @property integer $is_notified_1day
 */
class ClientFlag extends ActiveRecord
{
    public $isSetFlag = false;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_flag';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'NotifiedFlagToImportantEvent' => NotifiedFlagToImportantEvent::className()

        ];
    }

}
