<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\ActualCallChatDao;

/**
 * Class ActualCallChat
 * @package app\models
 *
 * @property int $client_id
 * @property int $usage_id
 * @property int $tarif_id
 */
class ActualCallChat extends ActiveRecord
{

    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\ActualCallChat::className()
        ];
    }

    public static function tableName()
    {
        return 'actual_call_chat';
    }

    public static function dao()
    {
        return ActualCallChatDao::me();
    }

}
