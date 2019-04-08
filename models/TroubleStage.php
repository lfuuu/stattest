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
    const STATE_ENABLED = 48;
    const STATE_CROSS_SELL = 64;

    public $dif_time = '00:00';

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\TroubleStages::class,
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
        return $this->hasOne(TroubleState::class, ['id' => 'state_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrouble()
    {
        return $this->hasOne(Trouble::class, ['id' => 'trouble_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['user' => 'user_main']);
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

    /**
     * @return bool
     */
    public function isStateEnabled()
    {
        return $this->state_id === self::STATE_ENABLED;
    }

    /**
     * @return bool
     */
    public function isStateCrossSell()
    {
        return $this->state_id === self::STATE_CROSS_SELL;
    }
}