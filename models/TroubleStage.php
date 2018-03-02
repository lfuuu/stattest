<?php

namespace app\models;

use app\classes\model\ActiveRecord;

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
 *
 * @property-read TroubleState $state
 * @property-read Trouble $trouble
 * @property-read User $user
 */
class TroubleStage extends ActiveRecord
{

    const SEARCH_ITEMS = 100;

    public $dif_time = '00:00';

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\TroubleStages::className(),
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tt_stages';
    }

    public function attributeLabels()
    {
        return [
            'stage_id' => '№ этапа',
            'trouble_id' => '№ заявки',
            'comment' => 'Комментарий к этапу',
            'date_start' => 'Дата этапа',
            'user_main' => 'Создатель этапа',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(TroubleState::className(), ['id' => 'state_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrouble()
    {
        return $this->hasOne(Trouble::className(), ['id' => 'trouble_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user' => 'user_main']);
    }

    /**
     * @return $this
     */
    public static function find()
    {
        return parent::find()->select([
            '*',
            'FROM_UNIXTIME(TIMESTAMPDIFF(SECOND, `' . self::tableName() . '`.`date_start`, IF(`state_id`=2,`date_edit`,NOW())), "%dd %H:%i") AS `dif_time`'
        ]);
    }

}