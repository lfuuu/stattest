<?php
namespace app\models;

use app\dao\TroubleDao;
use yii\db\ActiveRecord;
use app\models\TroubleStage;
use app\classes\media\TroubleMedia as MediaManager;

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
    const DEFAULT_SUPPORT_USER = 'nick';     // Михайлов Николай
    const DEFAULT_SUPPORT_SALES = 'ava';      // Ан Владимир
    const DEFAULT_SUPPORT_ACCOUNTING = 'istomina'; // Истомина Ирина
    const DEFAULT_SUPPORT_TECHNICAL = 'nick';     // Михайлов Николай
    const DEFAULT_SUPPORT_FOLDER = 257;
    const DEFAULT_SUPPORT_STATE = 1;
    const TYPE_TROUBLE = 'trouble';
    const TYPE_TASK = 'task';
    const SUBTYPE_TROUBLE = 'trouble';

    public $client_name = '';

    public $typeLabels = [
        'connect' => 'Подключение',
        'incomegoods' => 'Заказ поставщику',
        'mounting_orders' => '',
        'out' => '',
        'shop_orders' => 'Заказ',
        'support_welltime' => '',
        'task' => 'Задание',
        'trouble' => 'Трабл',
    ];

    public $subTypeLabels = [
        'connect' => 'Подключение',
        'incomegoods' => 'Заказ поставщику',
        'monitoring' => 'Мониторинг',
        '' => '',
        'shop' => 'Заказ',
        'reminder' => 'Напоминание',
        'task' => 'Задание',
        'trouble' => 'Трабл',
        'monitoring' => 'Мониторинг',
        'consultation' => 'Консультация',
    ];

    public $tt_files = [];

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
            return "Сервер: {$server->name},<br>Регион: {$server->datacenter->region->name}";
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
        return new MediaManager($this->id);
    }

}
