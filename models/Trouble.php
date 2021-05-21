<?php

namespace app\models;

use app\classes\media\TroubleMedia;
use app\classes\model\ActiveRecord;
use app\dao\TroubleDao;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use yii\base\InvalidParamException;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Url;

/**
 * Class Trouble
 *
 * @property int $id
 * @property string $trouble_type
 * @property string $client
 * @property string $user_author
 * @property string $date_creation
 * @property string $problem
 * @property string $service
 * @property int $service_id
 * @property int $cur_stage_id
 * @property int $is_important
 * @property string $bill_no
 * @property string $bill_id
 * @property int $folder
 * @property string $doer_comment
 * @property int $all4geo_id
 * @property string $trouble_subtype
 * @property string $date_close
 * @property int $support_ticket_id
 * @property string $updated_at
 * @property int $is_closed
 *
 * @property-read TroubleRoistat $troubleRoistat
 * @property-read TroubleStage $currentStage
 * @property-read TroubleStage $stage
 * @property-read TroubleStage[] $stages
 * @property-read Lead $lead
 * @property-read ClientAccount $account
 * @property-read \app\classes\media\TroubleMedia mediaManager
 */
class Trouble extends ActiveRecord
{

    const EVENT_AFTER_SAVE = 'afterSave';

    const DEFAULT_SUPPORT_USER = 'nick'; // Михайлов Николай
    const DEFAULT_SUPPORT_SALES = User::USER_KIM;
    const DEFAULT_SUPPORT_ACCOUNTING = 'istomina'; // Истомина Ирина
    const DEFAULT_SUPPORT_TECHNICAL =  User::USER_KOSHELEV;
    const DEFAULT_VPS_SUPPORT =  User::USER_VOSTROKNUTOV;
    const DEFAULT_API_AUTHOR = 'AutoLK';
    const DEFAULT_ADD_ACCOUNT_TARIFF_USER = 'virt_user';
    const DEFAULT_SUPPORT_FOLDER = 257;
    const DEFAULT_SUPPORT_STATE = 1;
    const DEFAULT_CONNECT_FOLDER = 137438953473;
    const DEFAULT_CONNECT_STATE = 41;

    const TYPE_TROUBLE = 'trouble';
    const TYPE_TASK = 'task';
    const TYPE_CONNECT = 'connect';
    const TYPE_INCOMEGOODS = 'incomegoods';
    const TYPE_MOUNTING_ORDERS = 'mounting_orders';
    const TYPE_OUT = 'out';
    const TYPE_SHOP_ORDERS = 'shop_orders';
    const TYPE_SUPPORT_WELLTIME = 'support_welltime';

    const SUBTYPE_TROUBLE = 'trouble';
    const SUBTYPE_CONNECT = 'connect';
    const SUBTYPE_MONITORING = 'monitoring';
    const SUBTYPE_INCOMEGOODS = 'incomegoods';
    const SUBTYPE_SHOP = 'shop';
    const SUBTYPE_REMINDER = 'reminder';
    const SUBTYPE_TASK = 'task';
    const SUBTYPE_CONSULTATION = 'consultation';

    const OPTION_IS_FROM_LK_MCN_RU = 'is_from_lk.mcn.ru';
    const OPTION_IS_CHECK_SAVED_ROISTAT_VISIT = 'is_check_saved_roistat_visit';

    public $client_name = '';

    public static $types = [
        self::TYPE_CONNECT => 'Подключение',
        self::TYPE_INCOMEGOODS => 'Заказ поставщику',
        self::TYPE_MOUNTING_ORDERS => '',
        self::TYPE_OUT => '',
        self::TYPE_SHOP_ORDERS => 'Заказ',
        self::TYPE_SUPPORT_WELLTIME => '',
        self::TYPE_TASK => 'Задание',
        self::TYPE_TROUBLE => 'Трабл',
    ];

    public static $subTypes = [
        self::SUBTYPE_CONNECT => 'Подключение',
        self::SUBTYPE_INCOMEGOODS => 'Заказ поставщику',
        self::SUBTYPE_MONITORING => 'Мониторинг',
        '' => '',
        self::SUBTYPE_SHOP => 'Заказ',
        self::SUBTYPE_REMINDER => 'Напоминание',
        self::SUBTYPE_TASK => 'Задание',
        self::SUBTYPE_TROUBLE => 'Трабл',
        self::SUBTYPE_CONSULTATION => 'Консультация',
    ];

    public $tt_files = [];

    public function rules()
    {
        return [
            [['trouble_type', 'trouble_subtype', 'client',], 'required'],
            [
                [
                    'trouble_type',
                    'trouble_subtype',
                    'client',
                    'user_author',
                    'problem',
                    'service',
                    'bill_no',
                    'bill_id',
                    'doer_comment',
                    'folder',
                ],
                'trim'
            ],
            [
                [
                    'service_id',
                    'cur_stage_id',
                    'is_important',
                    'all4geo_id',
                    'support_ticket_id',
                    'server_id'
                ],
                'integer'
            ],
            [['date_creation', 'date_close', 'updated_at'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\Troubles::class,
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => date(DateTimeZoneHelper::DATETIME_FORMAT),
            ],
        ];
    }

    public static function tableName()
    {
        return 'tt_troubles';
    }

    public static function dao()
    {
        return TroubleDao::me();
    }

    public function addStage($stateId, $comment, $newUserId = null, $editUserId = null)
    {
        if (!$this->isTransferAllowed($stateId)) {
            throw new InvalidParamException('Невозможно перевести из стадии ' . $this->currentStage->state_id . ' в ' . $stateId);
        }

        return TroubleDao::me()->addStage($this, $stateId, $comment, $newUserId, $editUserId);
    }

    /**
     * @return TroubleStage
     */
    public function getCurrentStage()
    {
        return TroubleStage::findOne(["stage_id" => $this->cur_stage_id, "trouble_id" => $this->id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStage()
    {
        return $this->hasOne(TroubleStage::class, ['stage_id' => 'cur_stage_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStages()
    {
        return $this->hasMany(TroubleStage::class, ['trouble_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(ClientAccount::class, ['client' => 'client']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLead()
    {
        return $this->hasOne(Lead::class, ['trouble_id' => 'id']);
    }

    public function getLastNotEmptyComment()
    {
        $model = TroubleStage::find()
            ->andWhere(['trouble_id' => $this->id])
            ->andWhere(['!=', 'comment', ''])
            ->orderBy(['stage_id' => SORT_DESC])
            ->one();
        return ($model) ? $model->comment : '';
    }

    public function getUsage()
    {
        if ($this->server_id) {
            $server = ServerPbx::findOne($this->server_id);
            return "Сервер: {$server->name},<br>Регион: {$server->datacenter->datacenterRegion->name}";
        } elseif ($this->service) {
            if ($this->service != 'usage_voip') {
                return str_replace('usage_', '', $this->service) . '-' . $this->service_id;
            }
            return (null !== $m = UsageVoip::findOne($this->service_id)) ? $m->E164 : '';
        } elseif ($this->bill_no) {
            return $this->bill_no;
        }

        return '';
    }

    public function getMediaManager()
    {
        return new TroubleMedia($this);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTroubleRoistat()
    {
        return $this->hasOne(TroubleRoistat::class, ['trouble_id' => 'id']);
    }

    /**
     * Можем ли мы перевести заявку на новый этап
     *
     * @param integer $newStateId
     * @return bool
     */
    public function isTransferAllowed($newStateId)
    {
        $currentState = $this->currentStage->state;

        /** @var TroubleType $stateType */
        $stateType = TroubleType::find()
            ->where(['&', 'states', $currentState->pk])
            ->one();

        if (!$stateType) {
            throw new \LogicException('Тип заявки не найден');
        }

        return TroubleState::find()
            ->where(['&', 'pk', $stateType->states])
            ->andWhere(['not', ['&', 'pk', $currentState->deny]])
            ->andWhere(['id' => $newStateId])
            ->exists();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to([
            '/',
            'module' => 'tt',
            'action' => 'view',
            'id' => $this->id
        ]);
    }

    /**
     * @throws ModelValidationException
     */
    public function setIsChanged()
    {
        $this->updated_at = date(DateTimeZoneHelper::DATETIME_FORMAT);
        if (!$this->save()) {
            throw new ModelValidationException($this);
        }
    }

    /**
     * @return int|null
     */
    public function getState_id()
    {
        return ($this->stage) ? $this->stage->state_id : null;
    }

    /**
     * @return string|null
     */
    public function getUser_main()
    {
        return ($this->stage) ? $this->stage->user_main : null;
    }

    /**
     * @return bool
     */
    public function getIs_editableByMe()
    {
        return TroubleStage::find()
            ->where(['trouble_id' => $this->id])
            ->andWhere(['user_main' => \Yii::$app->user->identity->user])
            ->exists();
    }
}
