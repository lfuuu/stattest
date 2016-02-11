<?php

namespace app\models;

use app\classes\media\TroubleMedia;
use app\dao\TroubleDao;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
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
 * @property
 */
class Trouble extends ActiveRecord
{

    const EVENT_AFTER_SAVE = 'afterSave';

    const DEFAULT_SUPPORT_USER = 'nick';     // Михайлов Николай
    const DEFAULT_SUPPORT_SALES = 'ava';      // Ан Владимир
    const DEFAULT_SUPPORT_ACCOUNTING = 'istomina'; // Истомина Ирина
    const DEFAULT_SUPPORT_TECHNICAL = 'nick';     // Михайлов Николай
    const DEDAULT_API_AUTHOR = 'AutoLK';
    const DEFAULT_SUPPORT_FOLDER = 257;
    const DEFAULT_SUPPORT_STATE = 1;
    const TYPE_TROUBLE = 'trouble';
    const TYPE_TASK = 'task';
    const TYPE_CONSULTATION = 'consultation';
    const TYPE_MONITORING = 'monitoring';
    const SUBTYPE_TROUBLE = 'trouble';

    public $client_name = '';

    public static $types = [
        'connect' => 'Подключение',
        'incomegoods' => 'Заказ поставщику',
        'mounting_orders' => '',
        'out' => '',
        'shop_orders' => 'Заказ',
        'support_welltime' => '',
        'task' => 'Задание',
        'trouble' => 'Трабл',
    ];

    public static $subTypes = [
        'connect' => 'Подключение',
        'incomegoods' => 'Заказ поставщику',
        'monitoring' => 'Мониторинг',
        '' => '',
        'shop' => 'Заказ',
        'reminder' => 'Напоминание',
        'task' => 'Задание',
        'trouble' => 'Трабл',
        'consultation' => 'Консультация',
    ];

    public $tt_files = [];

    public function rules()
    {
        return [
            [['trouble_type', 'trouble_subtype', 'client', ], 'required'],
            [
                [
                    'trouble_type', 'trouble_subtype', 'client', 'user_author',
                    'problem', 'service', 'bill_no', 'bill_id', 'doer_comment',
                    'folder',
                ],
                'trim'
            ],
            [
                [
                    'service_id', 'cur_stage_id', 'is_important',
                    'all4geo_id', 'support_ticket_id', 'server_id'
                ],
                'integer'
            ],
            [['date_creation', 'date_close'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\Troubles::className(),
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

    public function addStage($stateId, $comment, $userId = null)
    {
        return TroubleDao::me()->addStage($this, $stateId, $comment, $userId);
    }

    public function getCurrentStage()
    {
        return TroubleStage::findOne(["stage_id" => $this->cur_stage_id, "trouble_id" => $this->id]);
    }

    public function getStage()
    {
        return $this->hasOne(TroubleStage::className(), ['stage_id' => 'cur_stage_id']);
    }

    public function getAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
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
            if ($this->service != 'usage_voip')
                return str_replace('usage_', '', $this->service) . '-' . $this->service_id;
            return (null !== $m = UsageVoip::findOne($this->service_id)) ? $m->E164 : '';
        } elseif ($this->bill_no)
            return $this->bill_no;

        return '';
    }

    public function getMediaManager()
    {
        return new TroubleMedia($this);
    }

}
