<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $stage_id
 * @property int $trouble_id
 * @property int $state_id
 * @property string $user_main
 * @property string $date_edit
 * @property string $user_edit
 * @property string $comment
 * @property string $uspd
 * @property string $date_start
 * @property string $date_finish_desired
 * @property int $rating
 * @property string $user_rating
 * @property
 */
class TroubleStage extends ActiveRecord
{
    public $dif_time = '00:00';

    public static function tableName()
    {
        return 'tt_stages';
    }

    public function getState()
    {
        return $this->hasOne(TroubleState::className(), ['id' => 'state_id']);
    }

    public static function find()
    {
        return parent::find()->select(['*', 'FROM_UNIXTIME(TIMESTAMPDIFF(SECOND, `'.self::tableName().'`.`date_start`, IF(`state_id`=2,`date_edit`,NOW())), "%dd %H:%i") AS `dif_time`']);
    }

}