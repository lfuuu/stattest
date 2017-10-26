<?php

namespace app\modules\sim\models;

use app\classes\model\HistoryActiveRecord;
use app\models\ClientAccount;
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
 *
 * @property-read ClientAccount $clientAccount
 * @property-read Imsi[] $imsies
 * @property-read CardStatus $status
 *
 * @method static Card findOne($condition)
 * @method static Card[] findAll($condition)
 */
class Card extends HistoryActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'sim_card';
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
            [['iccid', 'imei', 'client_account_id', 'is_active', 'status_id'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return parent::behaviors() + [
                'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            ];
    }

    /**
     * @return ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_account_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getImsies()
    {
        return $this->hasMany(Imsi::className(), ['iccid' => 'iccid'])
            ->indexBy('imsi');
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(CardStatus::className(), ['id' => 'status_id']);
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
        return Url::to(['/sim/card/edit', 'iccid' => $iccid]);
    }
}
