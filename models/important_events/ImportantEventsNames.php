<?php

namespace app\models\important_events;

use app\classes\model\ActiveRecord;
use app\classes\traits\TagsTrait;
use yii\data\ActiveDataProvider;

/**
 * @property int $id
 * @property string $code
 * @property string $value
 * @property int $group_id
 * @property string $comment
 */
class ImportantEventsNames extends ActiveRecord
{

    use TagsTrait;

    // Custom
    const ADD_PAY_NOTIF = 'add_pay_notif';
    const DAY_LIMIT = 'day_limit';
    const UNSET_DAY_LIMIT = 'unset_day_limit';
    const DAY_LIMIT_MN = 'day_limit_mn';
    const UNSET_DAY_LIMIT_MN = 'unset_day_limit_mn';
    const MIN_DAY_LIMIT = 'min_day_limit';
    const UNSET_MIN_DAY_LIMIT = 'unset_min_day_limit';
    const MIN_BALANCE = 'min_balance';
    const UNSET_MIN_BALANCE = 'unset_min_balance';
    const ZERO_BALANCE = 'zero_balance';
    const UNSET_ZERO_BALANCE = 'unset_zero_balance';
    const CR_TOKEN = 'cr_token';
    const CHANGE_MIN_DAY_LIMIT = 'change_min_day_limit';
    const SET_LOCAL_BLOCK = 'set_local_block';
    const UNSET_LOCAL_BLOCK = 'unset_local_block';

    const FORECASTING_7DAY = 'forecasting_7day';
    const FORECASTING_3DAY = 'forecasting_3day';
    const FORECASTING_1DAY = 'forecasting_1day';

    // Payment
    const PAYMENT_ADD = 'payment_add';
    const PAYMENT_DELETE = 'payment_del';

    // redirects
    const REDIRECT_ADD = 'redirect_add';
    const REDIRECT_DELETE = 'redirect_del';

    // ClientAccount
    const NEW_ACCOUNT = 'new_account';
    const ACCOUNT_CHANGED = 'account_changed';
    const EXTEND_ACCOUNT_CONTRACT = 'extend_account_contract';
    const CONTRACT_TRANSFER = 'contract_transfer';
    const ACCOUNT_CONTRACT_CHANGED = 'account_contract_changed';
    const TRANSFER_CONTRAGENT = 'transfer_contragent';
    const CHANGE_CREDIT_LIMIT = 'change_credit_limit';
    const CLIENT_LOGGED_IN = 'client_logged_in';

    // Troubles
    const CREATED_TROUBLE = 'created_trouble';
    const CLOSED_TROUBLE = 'closed_trouble';
    const SET_STATE_TROUBLE = 'set_state_trouble';
    const SET_RESPONSIBLE_TROUBLE = 'set_responsible_trouble';
    const NEW_COMMENT_TROUBLE = 'new_comment_trouble';

    // Usages
    const ENABLED_USAGE = 'enabled_usage';
    const DISABLED_USAGE = 'disabled_usage';
    const CREATED_USAGE = 'created_usage';
    const UPDATED_USAGE = 'updated_usage';
    const DELETED_USAGE = 'deleted_usage';
    const TRANSFER_USAGE = 'transfer_usage';

    // Flags
    const NOTIFIED_7DAYS = 'notified_7days';
    const RESET_NOTIFIED_7DAYS = 'reset_notified_7days';
    const NOTIFIED_3DAYS = 'notified_3days';
    const RESET_NOTIFIED_3DAYS = 'reset_notified_3days';
    const NOTIFIED_1DAYS = 'notified_1days';
    const RESET_NOTIFIED_1DAYS = 'reset_notified_1days';

    // Overdue
    const INVOCE_PAYMENT_DELAY = 'invoce_payment_delay';
    const INVOICE_PAYMENT_DONE = 'invoice_payment_done';

    // УУ
    const UU_CREATED = 'uu_created'; // создана
    const UU_SWITCHED_ON = 'uu_switched_on'; // включена
    const UU_UPDATED = 'uu_updated'; // изменен тариф с одного на другой
    const UU_SWITCHED_OFF = 'uu_switched_off'; // выключена
    const UU_DELETED = 'uu_deleted'; // удалена, если она еще не начала действовать

    // VPS
    const VPS_USER_CREATE = 'vps_user_create';
    const VPS_USER_ENABLE = 'vps_user_enable';
    const VPS_USER_DISABLE = 'vps_user_disable';
    const VPS_CREATE = 'vps_create';
    const VPS_UPDATE = 'vps_update';
    const VPS_DROP = 'vps_drop';
    const VPS_STOP = 'vps_stop';
    const VPS_START = 'vps_start';

    // SBIS
    const SBIS_DRAFT_CREATED = 'sbis_draft_created';
    const SBIS_DOCUMENT_CREATED = 'sbis_document_created';
    const SBIS_DOCUMENT_SENT = 'sbis_document_sent';
    const SBIS_DOCUMENT_ACCEPTED = 'sbis_document_accepted';
    const SBIS_DOCUMENT_EVENT = 'sbis_document_event';

    // Porting
    const PORTING_FROM_MCN = 'porting_from_mcn';
    const PORTING_TO_MCN = 'porting_to_mcn';

    const DOCUMENT_UPLOADED_LK = 'document_uploaded_lk';

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
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['code', 'value', 'group_id'], 'required'],
            [['code', 'value', 'comment',], 'trim'],
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
        return $this->hasOne(ImportantEventsGroups::class, ['id' => 'group_id']);
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