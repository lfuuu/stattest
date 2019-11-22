<?php

namespace app\modules\sbisTenzor\models;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * Таблица связи групп и форм первичных документов для обмена в системе СБИС
 *
 * @property integer $id
 * @property integer $group_id
 * @property integer $form_id
 *
 * @property-read SBISExchangeForm $form
 * @property-read SBISExchangeGroup $group
 */
class SBISExchangeGroupForm extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sbis_exchange_group_form';
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->db;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'form_id'], 'required'],
            [['group_id', 'form_id'], 'integer'],
            [['form_id'], 'exist', 'skipOnError' => true, 'targetClass' => SBISExchangeForm::class, 'targetAttribute' => ['form_id' => 'id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => SBISExchangeGroup::class, 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForm()
    {
        return $this->hasOne(SBISExchangeForm::class, ['id' => 'form_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(SBISExchangeGroup::class, ['id' => 'group_id']);
    }
}