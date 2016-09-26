<?php

namespace app\models\important_events;

use app\classes\traits\TagsTrait;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

/**
 * @property string $value
 * @package app\models\important_events
 */
class ImportantEventsNames extends ActiveRecord
{

    use TagsTrait;

    // Custom
    const IMPORTANT_EVENT_ADD_PAY_NOTIF = 'add_pay_notif';
    const IMPORTANT_EVENT_DAY_LIMIT = 'day_limit';
    const IMPORTANT_EVENT_UNSET_DAY_LIMIT = 'unset_day_limit';
    const IMPORTANT_EVENT_MIN_DAY_LIMIT = 'min_day_limit';
    const IMPORTANT_EVENT_UNSET_MIN_DAY_LIMIT = 'unset_min_day_limit';
    const IMPORTANT_EVENT_MIN_BALANCE = 'min_balance';
    const IMPORTANT_EVENT_UNSET_MIN_BALANCE = 'unset_min_balance';
    const IMPORTANT_EVENT_ZERO_BALANCE = 'zero_balance';
    const IMPORTANT_EVENT_UNSET_ZERO_BALANCE = 'unset_zero_balance';
    const IMPORTANT_EVENT_CR_TOKEN = 'cr_token';
    const IMPORTANT_EVENT_CHANGE_CREDIT_LIMIT = 'change_credit_limit';
    const IMPORTANT_EVENT_CHANGE_MIN_DAY_LIMIT = 'change_min_day_limit';

    //Payment
    const IMPORTANT_EVENT_PAYMENT_ADD = 'payment_add';
    const IMPORTANT_EVENT_PAYMENT_DELETE = 'payment_del';

    // ClientAccount
    const IMPORTANT_EVENT_NEW_ACCOUNT = 'new_account';
    const IMPORTANT_EVENT_ACCOUNT_CHANGED = 'account_changed';
    const IMPORTANT_EVENT_EXTEND_ACCOUNT_CONTRACT = 'extend_account_contract';
    const IMPORTANT_EVENT_CONTRACT_TRANSFER = 'contract_transfer';
    const IMPORTANT_EVENT_ACCOUNT_CONTRACT_CHANGED = 'account_contract_changed';
    const IMPORTANT_EVENT_TRANSFER_CONTRAGENT = 'transfer_contragent';

    // Troubles
    const IMPORTANT_EVENT_CREATED_TROUBLE = 'created_trouble';
    const IMPORTANT_EVENT_CLOSED_TROUBLE = 'closed_trouble';
    const IMPORTANT_EVENT_SET_STATE_TROUBLE = 'set_state_trouble';
    const IMPORTANT_EVENT_SET_RESPONSIBLE_TROUBLE = 'set_responsible_trouble';
    const IMPORTANT_EVENT_NEW_COMMENT_TROUBLE = 'new_comment_trouble';

    // USAGES
    const IMPORTANT_EVENT_ENABLED_USAGE = 'enabled_usage';
    const IMPORTANT_EVENT_DISABLED_USAGE = 'disabled_usage';
    const IMPORTANT_EVENT_CREATED_USAGE = 'created_usage';
    const IMPORTANT_EVENT_UPDATED_USAGE = 'updated_usage';
    const IMPORTANT_EVENT_DELETED_USAGE = 'deleted_usage';
    const IMPORTANT_EVENT_TRANSFER_USAGE = 'transfer_usage';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'important_events_names';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['code', 'value', 'group_id'], 'required'],
            [['code', 'value', 'comment', ], 'trim'],
            ['group_id', 'integer'],
            [['code', 'value', 'comment',], 'string'],
            [['code', 'group_id'], 'unique', 'targetAttribute' => ['code', 'group_id']],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Код',
            'value' => 'Название',
            'group_id' => 'Группа',
            'comment' => 'Комментарий',
            'tags' => 'Метки',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(ImportantEventsGroups::className(), ['id' => 'group_id']);
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => false,
        ]);

        if (!$this->load($params)) {
            return $dataProvider;
        }

        if ($this->group_id = (int)$this->group_id) {
            $query->andFilterWhere(['group_id' => $this->group_id]);
        }

        return $dataProvider;
    }


    /**
     * @param bool $isWithEmpty
     * @return self[]
     */
    public static function getList($isWithEmpty = false)
    {
        $list = [];

        foreach (self::find()->orderBy(['id' => SORT_ASC])->each() as $event) {
            $list[$event->group->title][$event->code] = $event->value;
        }

        if ($isWithEmpty) {
            $list = ['' => '-- Выбор события --'] + $list;
        }

        return $list;
    }

}