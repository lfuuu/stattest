<?php
namespace app\models;

use app\classes\bill\VoipTrunkBiller;
use app\classes\transfer\TrunkServiceTransfer;
use app\dao\services\TrunkServiceDao;
use app\helpers\usages\UsageVoipTrunkHelper;
use app\models\billing\Trunk;
use app\models\usages\UsageInterface;
use DateTime;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $client_account_id
 * @property int $connection_point_id
 * @property int $trunk_id
 * @property string $actual_from
 * @property string $actual_to
 * @property string $activation_dt
 * @property string $expire_dt
 * @property int $orig_enabled
 * @property int $term_enabled
 * @property int $orig_min_payment
 * @property int $term_min_payment
 * @property string $description
 *
 * @property ClientAccount $clientAccount
 * @property Region $connectionPoint
 */
class UsageTrunk extends ActiveRecord implements UsageInterface
{
    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
        ];
    }

    public static function tableName()
    {
        return 'usage_trunk';
    }

    public static function dao()
    {
        return TrunkServiceDao::me();
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VoipTrunkBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return null;
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_TRUNK;
    }

    /** Заглушка, чтобы не падало из-за различий в client и client_account_id */
    public function getClient()
    {
        return $this->clientAccount->client;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_account_id']);
    }

    public function getConnectionPoint()
    {
        return $this->hasOne(Region::className(), ['id' => 'connection_point_id']);
    }

    public function getTrunk()
    {
        return $this->hasOne(Trunk::className(), ['id' => 'trunk_id']);
    }

    public function getSettings()
    {
        return $this->hasMany(UsageTrunkSettings::className(), ['usage_id' => 'id']);
    }

    public function isActive()
    {
        $now = new DateTime('now');

        $activationDt = new DateTime($this->activation_dt);
        $expireDt = new DateTime($this->expire_dt);

        return $activationDt <= $now and $expireDt >= $now;
    }

    /**
     * @param $usage
     * @return TrunkServiceTransfer
     */
    public static function getTransferHelper($usage)
    {
        return new TrunkServiceTransfer($usage);
    }

    /**
     * @return UsageVoipTrunkHelper
     */
    public function getHelper()
    {
        return new UsageVoipTrunkHelper($this);
    }

    /**
     * Вернуть список trunk_id => суперклиент
     * @param bool $isWithEmpty
     * @return string[]
     */
    public static function getSuperClientList($isWithEmpty = false)
    {
        // ORM не поддерживает многоуровневый join и indexBy по полю не из модели
        $list = (new \yii\db\Query())
            ->select(['usage_trunk.trunk_id', 'usage_trunk.client_account_id', 'client_contragent.name'])
            ->from('usage_trunk')
            ->innerJoin('clients', 'usage_trunk.client_account_id = clients.id')
            ->innerJoin('client_contract', 'clients.contract_id = client_contract.id')
            ->innerJoin('client_contragent', 'client_contract.contragent_id = client_contragent.id')
            ->orderBy('client_contragent.name')
            ->indexBy('trunk_id')
            ->all();

        $list = array_map(function ($row) {
            return $row['name'];
        }, $list);

        if ($isWithEmpty) {
            $list = ['' => ' ---- '] + $list;
        }

        return $list;
    }

    /**
     * Вернуть список всех доступных моделей
     * @param int $trunkId
     * @param bool $isWithEmpty
     * @return self[]
     */
    public static function getList($trunkId = null, $isWithEmpty = false)
    {
        $query = self::find();
        $trunkId && $query->where(['trunk_id' => $trunkId]);
        $list = $query->orderBy(['description' => SORT_ASC])
            ->indexBy('id')
            ->all();

        if ($isWithEmpty) {
            $list = ['' => ' ---- '] + $list;
        }

        return $list;
    }

    /**
     * Преобразовать объект в строку
     * @return string
     */
    public function __toString()
    {
        return $this->description ?: (string)$this->id;
    }
}