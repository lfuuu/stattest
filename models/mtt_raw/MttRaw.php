<?php

namespace app\models\mtt_raw;

use app\models\ClientAccount;
use app\modules\uu\models\AccountTariff;
use Yii;
use app\classes\model\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * Class MttRaw
 * @package app\models\mtt_raw
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=24478100
 *
 * @property string $connect_time
 * @property integer $src_number
 * @property integer $dst_number
 * @property integer $chargedqty
 * @property integer $cchargedamount
 *
 * @property-read ClientAccount $clientAccount
 * @property-read AccountTariff $accountTariff
 */
class MttRaw extends ActiveRecord
{

    const SERVICE_ID_CALL_IN_HOMENETWORK = 281;
    const SERVICE_ID_CALL_IN_ROAMING = 282;
    const SERVICE_ID_SMS_IN_HOMENETWORK = 283;
    const SERVICE_ID_SMS_IN_ROAMING = 284;
    const SERVICE_ID_INET_IN_HOMENETWORK = 285;
    const SERVICE_ID_INET_IN_ROAMING = 286;

    /**
     * Вернуть имена полей
     *
     * @return array [поле_в_таблице => заголовок]
     */
    public function attributeLabels()
    {
        return [
            'server_id' => 'Сервер', // всегда Москва, 99
            'id' => 'ID',
            'mtt_cdr_id' => 'MTT CDR',
            'mcn_cdr_id' => 'MCN CDR', // Фейковый CDR при звонках в роуминге
            'account_id' => 'ЛС',
            'number_service_id' => 'Услуга', // Услуга телефонии, а не интернета / смс
            'serviceid' => 'Тип',
            'connect_time' => 'Время',
            'src_number' => 'Исходящий №', // Для СМС и Звонков
            'dst_number' => 'Входящий №',  // Для СМС и Звонков
            'chargedqty' => 'Протарифицированное количество',
            'usedqty' => 'Фактическое количество',
            'chargedamount' => 'Стоимость MTT',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'account_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::className(), ['id' => 'account_id']);
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'mtt_raw.mtt_raw';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    /**
     * @return array
     */
    public static function getServiceList($isWithEmpty = false)
    {
        $list = [
            self::SERVICE_ID_CALL_IN_HOMENETWORK => 'Звонки дома',
            self::SERVICE_ID_CALL_IN_ROAMING => 'Звонки в роуминге',
            self::SERVICE_ID_SMS_IN_HOMENETWORK => 'СМС дома',
            self::SERVICE_ID_SMS_IN_ROAMING => 'СМС в роуминге',
            self::SERVICE_ID_INET_IN_HOMENETWORK => 'Интернет дома',
            self::SERVICE_ID_INET_IN_ROAMING => 'Интернет в роуминге'
        ];

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }
}