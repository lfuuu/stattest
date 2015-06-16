<?php
namespace app\models;

use yii\db\ActiveRecord;

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

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            return parent::save($runValidation = true, $attributeNames = null);
        }
        else {
            if (substr(php_sapi_name(), 0, 3) == 'cli' || \Yii::$app->request->post('deferred-date') === date('Y-m-d')) {
                return parent::save($runValidation = true, $attributeNames = null);
            } else {
                $behaviors = $this->behaviors;
                unset($behaviors['HistoryVersion']);
                $behaviors = array_keys($behaviors);
                foreach ($behaviors as $behavior)
                    $this->detachBehavior($behavior);
                $this->beforeSave(false);
            }
            return true;
        }
    }

    public function behaviors()
    {
        return [
            'HistoryVersion' => \app\classes\behaviors\HistoryVersion::className(),
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->name) {
            $super = ClientSuper::findOne($this->super_id);
            $super->setAttribute('name', $this->name);
            $super->save();
        }
    }
}
