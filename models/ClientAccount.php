<?php
namespace app\models;

use DateTimeZone;
use app\dao\ClientAccountDao;
use app\queries\ClientAccountQuery;
use yii\db\ActiveRecord;
use app\classes\behaviors\LogClientContractTypeChange;
use app\classes\behaviors\SetOldStatus;

/**
 * @property int $id
 * @property string $client
 * @property string $currency
 * @property string $nal
 * @property int $nds_zero

 * @property ClientSuper $superClient
 * @property ClientStatuses $lastComment
 * @property Country $country
 * @property Region $accountRegion
 * @property DateTimeZone $timezone
 * @property
 */
class ClientAccount extends ActiveRecord
{
    public static $statuses = array(
        'negotiations'        => array('name'=>'в стадии переговоров','color'=>'#C4DF9B'),
        'testing'             => array('name'=>'тестируемый','color'=>'#6DCFF6'),
        'connecting'          => array('name'=>'подключаемый','color'=>'#F49AC1'),
        'work'                => array('name'=>'включенный','color'=>''),
        'closed'              => array('name'=>'отключенный','color'=>'#FFFFCC'),
        'tech_deny'           => array('name'=>'тех. отказ','color'=>'#996666'),
        'telemarketing'       => array('name'=>'телемаркетинг','color'=>'#A0FFA0'),
        'income'              => array('name'=>'входящие','color'=>'#CCFFFF'),
        'deny'                => array('name'=>'отказ','color'=>'#A0A0A0'),
        'debt'                => array('name'=>'отключен за долги','color'=>'#C00000'),
        'double'              => array('name'=>'дубликат','color'=>'#60a0e0'),
        'trash'               => array('name'=>'мусор','color'=>'#a5e934'),
        'move'                => array('name'=>'переезд','color'=>'#f590f3'),
        'suspended'           => array('name'=>'приостановленные','color'=>'#C4a3C0'),
        'denial'              => array('name'=>'отказ/задаток','color'=>'#00C0C0'),
        'once'                => array('name'=>'Интернет Магазин','color'=>'silver'),
        'reserved'            => array('name'=>'резервирование канала','color'=>'silver'),
        'blocked'             => array('name'=>'временно заблокирован','color'=>'silver'),
        'distr'               => array('name'=>'Поставщик','color'=>'yellow'),
        'operator'            => array('name'=>'Оператор','color'=>'lightblue')
    );

    private $_lastComment = false;

    public static function tableName()
    {
        return 'clients';
    }

    public static function dao()
    {
        return ClientAccountDao::me();
    }

    public static function find()
    {
        return new ClientAccountQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            LogClientContractTypeChange::className(),
            SetOldStatus::className(),
        ];
    }

    public function getTaxRate()
    {
        return $this->nds_zero ? 0 : 0.18;
    }

    public function getSuperClient()
    {
        return $this->hasOne(ClientSuper::className(), ['id' => 'super_id']);
    }

    public function getContractType()
    {
        return $this->hasOne(ClientContractType::className(), ['id' => 'contract_type_id']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    public function getAccountRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    public function getUserManager()
    {
        return $this->hasOne(User::className(), ["user" => "manager"]);
    }

    public function getUserAccountManager()
    {
        return $this->hasOne(User::className(), ["user" => "account_manager"]);
    }

    public function getStatusBP()
    {
        return $this->hasOne(ClientGridSettings::className(), ["id" => "business_process_status_id"]);
    }

    public function getStatusName()
    {
        return
            isset(self::$statuses[$this->status])
                ? self::$statuses[$this->status]['name']
                : $this->status;
    }

    public function getStatusColor()
    {
        return
            isset(self::$statuses[$this->status])
                ? self::$statuses[$this->status]['color']
                : '';
    }

    public function getLastComment()
    {
        if ($this->_lastComment === false) {
            $this->_lastComment =
                ClientStatuses::find()
                    ->andWhere(['id_client' => $this->id])
                    ->andWhere(['is_publish' => 1])
                    ->orderBy('ts desc')
                    ->all();
        }
        return $this->_lastComment;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimezone()
    {
        return new DateTimeZone($this->timezone_name);
    }

    public function getDefaultTaxId()
    {
        if ($this->nds_zero) {
            return TaxType::TAX_0;
        } else {
            return TaxType::TAX_18;
        }
    }
}
