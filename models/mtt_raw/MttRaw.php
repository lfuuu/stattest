<?php

namespace app\models\mtt_raw;

use app\classes\traits\GetListTrait;
use app\models\ClientAccount;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\traits\AccountTariffListTrait;
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
 * @property integer $usedqty
 * @property integer $chargedamount
 *
 * @property-read ClientAccount $clientAccount
 * @property-read AccountTariff $accountTariff
 */
class MttRaw extends ActiveRecord
{

    // Определяет getList (список для selectbox)
    use GetListTrait {
        getList as getListTrait;
    }
    use AccountTariffListTrait;

    const SERVICE_ID_CALL_IN_HOMENETWORK = 281;
    const SERVICE_ID_CALL_IN_ROAMING = 282;
    const SERVICE_ID_SMS_IN_HOMENETWORK = 283;
    const SERVICE_ID_SMS_IN_ROAMING = 284;
    const SERVICE_ID_INET_IN_HOMENETWORK = 285;
    const SERVICE_ID_INET_IN_ROAMING = 286;
    
    const SERVICE_ID_SMS = [
        self::SERVICE_ID_SMS_IN_HOMENETWORK,
        self::SERVICE_ID_SMS_IN_ROAMING,
    ];

    const SERVICE_ID_INET = [
        self::SERVICE_ID_INET_IN_HOMENETWORK,
        self::SERVICE_ID_INET_IN_ROAMING,
    ];

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
        return $this->hasOne(ClientAccount::class, ['id' => 'account_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::class, ['id' => 'account_id']);
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
     * @param bool $isWithEmpty
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

    /**
     * Красивое отображение объема использованной информации пользователем в килобайтах, мегабайтах и т.д.
     *
     * @param string|float|integer $value
     * @param int $decimals
     * @return string
     */
    public static function getBeautyFormattedValue($value, $decimals = 2)
    {
        $formatter = Yii::$app->formatter;
        $sizeFormatBase = $formatter->sizeFormatBase;

        // Устанавливаем базовый формат - делитель, что бы результат отображался с
        // приставками: kilo, mega, giga, tera, peta
        $formatter->sizeFormatBase = 1000;
        $usedqty = $formatter->asShortSize($value, $decimals);
        $formatter->sizeFormatBase = $sizeFormatBase;
        return $usedqty;
    }

    /**
     * Исходное значение уже представлено в килобайтах.
     * Переводим в байты, что бы Formatter установил правильные единицы измерения.
     *
     * @param bool $isForcibly
     * @return int|mixed|string
     */
    public function getBeautyChargedQty($isForcibly = false)
    {
        if (!$isForcibly && !in_array($this->serviceid, self::SERVICE_ID_INET)) {
            return $this->chargedqty;
        }
        return self::getBeautyFormattedValue($this->chargedqty * 1024, $decimals = 2);
    }

    /**
     * @param bool $isForcibly
     * @return int|string
     */
    public function getBeautyUsedQty($isForcibly = false)
    {
        if (!$isForcibly && !in_array($this->serviceid, self::SERVICE_ID_INET)) {
            return $this->usedqty;
        }
        return self::getBeautyFormattedValue($this->usedqty * 1);
    }
}