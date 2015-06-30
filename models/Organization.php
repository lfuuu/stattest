<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\queries\OrganizationQuery;
use app\dao\OrganizationDao;

/**
 * @property int $id
 * @property string actual_from                 Дата с которой фирма начинает действовать
 * @property string actual_to                   Дата до которой фирма действует
 * @property int $firma                         Ключ для связи с clients*
 * @property int country_id                     Код страны
 * @property string lang_code                   Код языка
 * @property array tax_system                   Вариант налогообложения (ОСНО, УСН)
 * @property int vat_rate                       Ставка налога
 * @property string name                        Название
 * @property string full_name                   Полное название
 * @property string legal_address               Юр. адрес
 * @property string post_address                Почтовый адрес
 * @property string registration_id             Регистрационный номер (ОГРН)
 * @property string tax_registration_id         Идентификационный номер налогоплательщика (ИНН)
 * @property string tax_registration_reason     Код причины постановки (КПП)
 * @property string bank_account                Расчетный счет
 * @property string bank_name                   Название банка
 * @property string bank_correspondent_account  Кор. счет
 * @property string bank_bik                    БИК
 * @property string bank_swift                  SWIFT
 * @property string contact_phone               Телефон
 * @property string contact_fax                 Факс
 * @property string contact_email               E-mail
 * @property string contact_site                URL сайта
 * @property string logo_file_name              Название файла с логотипом
 * @property string stamp_file_name             Название файла с печатью
 * @property int director_id                    ID записи персон на должность директора
 * @property int accountant_id                  ID записи персон на должность бухгалтера

 */
class Organization extends ActiveRecord
{
    public static function tableName()
    {
        return 'organization';
    }

    public function beforeSave($query)
    {
        $this->logo_file_name = basename($this->logo_file_name);
        $this->stamp_file_name = basename($this->stamp_file_name);

        $this->getDb()
            ->createCommand(
                "
                UPDATE `organization` SET
                    `actual_to` = :actual_to
                WHERE
                    `id` = :id AND
                    `actual_from` < :date
                ORDER BY `actual_from` DESC
                LIMIT 1
                ", [
                    ':id' => $this->id,
                    ':date' => $this->actual_from,
                    ':actual_to' => (new \DateTime($this->actual_from))->modify('-1 day')->format('Y-m-d')
                ]
            )
                ->execute();

        $next_record = $this
            ->find()
            ->where(['id' => $this->id])
            ->andWhere(['>', 'actual_from', $this->actual_from])
            ->orderBy('actual_from asc')
            ->one();
        if ($next_record instanceof Organization) {
            $this->actual_to = (new \DateTime($next_record->actual_from))->modify('-1 day')->format('Y-m-d');
        }

        return parent::beforeSave($query);
    }

    public static function dao()
    {
        return OrganizationDao::me();
    }

    public static function find()
    {
        return new OrganizationQuery(get_called_class());
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    public function getDirector()
    {
        return $this->hasOne(Person::className(), ['id' => 'director_id']);
    }

    public function getAccountant()
    {
        return $this->hasOne(Person::className(), ['id' => 'accountant_id']);
    }

}