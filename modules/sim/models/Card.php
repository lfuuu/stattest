<?php

namespace app\modules\sim\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\dao\CardDao;
use app\models\ClientAccount;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * SIM-карта (болванка)
 * SIM = Subscriber Identification Module
 *
 * @property int $iccid ICCID = Integrated Circuit Card Id
 * @property int $imei IMEI = International Mobile Equipment Identity
 * @property int $client_account_id
 * @property int $is_active
 * @property int $status_id
 * @property int $region_id
 * @property int $sim_type_id
 *
 * @property-read ClientAccount $clientAccount
 * @property-read Imsi[] $imsies
 * @property-read CardStatus $status
 *
 * @method static Card findOne($condition)
 * @method static Card[] findAll($condition)
 */
class Card extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.sim_card';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }

    /**
     * @return string[]
     */
    public static function primaryKey()
    {
        return ['iccid'];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['iccid'], 'required'],
            [['iccid', 'imei', 'client_account_id', 'is_active', 'status_id', 'entry_point_id'], 'integer'],
            ['client_account_id', 'exist', 'skipOnError' => true, 'targetClass' => ClientAccount::class, 'targetAttribute' => ['client_account_id' => 'id'], 'filter' => ['account_version' => ClientAccount::VERSION_BILLER_UNIVERSAL]],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                \app\classes\behaviors\HistoryChanges::class,
                \app\modules\sim\behaviors\CardStatusBehavior::class,
            ]
        );
    }

    public static function dao()
    {
        return CardDao::me();
    }

    /**
     * @return ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'client_account_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getImsies()
    {
        return $this->hasMany(Imsi::class, ['iccid' => 'iccid'])
            ->indexBy('imsi');
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(CardStatus::class, ['id' => 'status_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(CardType::class, ['id' => 'sim_type_id']);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return self::getUrlById($this->iccid);
    }

    /**
     * @param int $iccid
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public static function getUrlById($iccid)
    {
        return Url::to(['/sim/card/edit', 'originIccid' => $iccid]);
    }

    /**
     * Вернуть html: имя + ссылка
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getLink()
    {
        return Html::a(Html::encode($this->iccid), $this->getUrl());
    }

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {

            case 'client_account_id':
                if ($clientAccount = ClientAccount::findOne(['id' => $value])) {
                    return $clientAccount->getLink();
                }
                break;

            case 'status_id':
                if ($cardStatus = CardStatus::findOne(['id' => $value])) {
                    return $cardStatus->getLink();
                }
                break;
        }

        return $value;
    }

    public function getEntry_point_id()
    {
        return $this->clientAccount->superClient->entry_point_id;
    }

    public function __toString()
    {
        return (string)$this->iccid;
    }
}
