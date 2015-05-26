<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\classes\behaviors\HistoryVersion;
use app\classes\behaviors\HistoryChanges;
use app\dao\ClientContragentDao;

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
            "legal_type" => "Тип",
            "inn" => "ИНН",
            "inn_euro" => "ЕвроИНН",
            "kpp" => "КПП",
            "position" => "Должность Исполнительного органа",
            "fio" => "ФИО Исполнительного органа",
            "positionV" => "Должность Исполнительного органа",
            "fioV" => "ФИО Исполнительного органа",
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
        return $this->hasOne(ClientContragentPerson::className(), ['contragent_id' => 'id']);
    }

    public function getContracts()
    {
        return $this->hasMany(ClientContract::className(), ['contragent_id' => 'id']);
    }

    public function behaviors()
    {
        return [
            HistoryVersion::className(),
            HistoryChanges::className(),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($this->name) {
            $super = ClientSuper::findOne($this->super_id);
            $super->name = $this->name;
            $super->save();
        }
    }
}
