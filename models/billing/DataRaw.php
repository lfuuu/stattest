<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\DataRawDao;
use app\modules\nnp\models\AccountTariffLight;
use Yii;

/**
 * @property int id
 * @property bool orig
 * @property int server_id
 * @property string charge_time
 * @property int account_id
 * @property int number_service_id
 * @property float rate
 * @property float cost
 * @property int quantity
 * @property string msisdn
 * @property string imsi
 * @property string mcc
 * @property string mnc
 * @property int account_tariff_light_id
 * @property int nnp_package_data_id
 * @property int cdr_id
 */
class DataRaw extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'data_raw.data_raw';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['mcc', 'integer']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'charge_time' => 'Время подключения',
            'number_service_id' => 'Услуга',
            'rate' => 'Ставка',
            'cost' => 'Стоимость',
            'quantity' => 'Количество',
            'mnc' => 'Оператор',
            'mcc' => 'Страна',
        ];
    }

    /**
     * @return DataRawDao
     */
    public static function dao()
    {
        return DataRawDao::me();
    }

    /**
     * Связка с оператором
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMncModel()
    {
        return $this->hasOne(MNC::className(), ['mnc' => 'mnc', 'mcc' => 'mcc']);
    }

    /**
     * Связка со страной
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMccModel()
    {
        return $this->hasOne(MCC::className(), ['mcc' => 'mcc']);
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

    public function getAccountTariffLight()
    {
        return $this->hasOne(AccountTariffLight::class, ['id' => 'account_tariff_light_id']);
    }

    /**
     * Получить количество в виде отформатированной строки
     * @return string
     */
    public function getFormattedQuantity()
    {
        return static::formatQuantity($this->quantity);
    }

    /**
     * Отформатировать количество
     * @param int $quantity
     * @param int $counter
     * @param array $names
     * @return string
     */
    public static function formatQuantity($quantity, $counter = 0, $names = [' Б', ' КБ', ' МБ', ' ГБ', ' ТБ'])
    {
        return ($quantity > 1024) ?
            static::formatQuantity($quantity / 1024, ++$counter) :
            round($quantity, 2) . $names[$counter];
    }
}
