<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\classes\behaviors\HistoryVersion;
use app\classes\behaviors\HistoryChanges;

/**
 * @property int $id
 * @property
 */
class ClientContragent extends ActiveRecord
{
    public $cPerson = null;

    public static function tableName()
    {
        return 'client_contragent';
    }

    public function attributeLabels()
    {
        return [
            "name" => "Краткое наименование",
            "name_full" => "Полное наименование",
            "address_jur" => "Адрес юридический",
            "address_post" => "Адрес почтовый",
            "legal_type" => "Тип",
            "inn" => "ИНН",
            "inn_euro" => "ЕвроИНН",
            "kpp" => "КПП",
            "position" => "Должность Исполнительного органа",
            "fio" => "ФИО Исполнительного органа",
            "tax_regime" => "Налоговый режим",
            "ogrn" => "Код ОГРН",
            "opf" => "Код ОПФ",
            "okpo" => "Код ОКПО",
            "okvd" => "Код ОКВЭД",
        ];
    }
    
    public function getAccounts()
    {
      return $this->hasMany(ClientAccount::className(), ['contragent_id' => 'id']);
    }

    public function getPerson()
    {
      return $this->hasOne(ClientPerson::className(), ['contragent_id' => 'id']);
    }

    public function behaviors()
    {
        return [
            HistoryVersion::className(),
            HistoryChanges::className(),
        ];
    }
}