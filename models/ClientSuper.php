<?php

namespace app\models;

use app\classes\api\ApiCore;
use app\classes\behaviors\EventQueueAddEvent;
use app\classes\model\ActiveRecord;
use app\dao\ClientSuperDao;

/**
 * Class ClientSuper
 *
 * @property int $id
 * @property string $name
 * @property int $financial_manager_id
 * @property bool $is_lk_exists
 * @property int $entry_point_id
 * @property string $utm
 * @property-read ClientContragent[] $contragents
 * @property-read ClientContract[] $contracts
 * @property-read ClientAccount[] $accounts
 * @property-read EntryPoint $entryPoint
 * @property-read Country $country
 */
class ClientSuper extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_super';
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'financial_manager_id' => 'Финансовый менеджер',
            'entry_point_id' => 'Точка входа',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'EventQueueAddEvent' => [
                'class' => EventQueueAddEvent::class,
                'insertEvent' => EventQueue::ADD_SUPER_CLIENT
            ],

            'CheckCreateCoreAdmin' => [
                'class' => EventQueueAddEvent::class,
                'insertEvent' => EventQueue::CHECK_CREATE_CORE_OWNER,
                'isWithIndicator' => true
            ]
        ];
    }

    /**
     * DAO
     *
     * @return ClientSuperDao
     */
    public static function dao()
    {
        return ClientSuperDao::me();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContragents()
    {
        return $this->hasMany(ClientContragent::class, ['super_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContracts()
    {
        return $this->hasMany(ClientContract::class, ['super_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccounts()
    {
        return $this->hasMany(ClientAccount::class, ['super_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntryPoint()
    {
        return $this->hasOne(EntryPoint::class, ['id' => 'entry_point_id']);
    }

    /**
     * Показывать ли ссылку перехода в ЛК
     *
     * @return bool|null
     */
    public function isShowLkLink()
    {
        if ($this->is_lk_exists) {
            return true;
        }

        try {
            $isLkExists = ApiCore::isLkExists($this->id);

            if ($isLkExists) {
                $this->is_lk_exists = 1;
                $this->save();
            }

            return $isLkExists;

        } catch (\Exception $e) {
            // возможно, не настроено API
            return null;
        }
    }

    /**
     * Получение первого ЛС у клиента
     *
     * @return ClientAccount
     */
    public function getFirstAccount()
    {
        return $this
            ->getAccounts()
            ->orderBy([
                'id' => SORT_ASC
            ])
            ->one();

    }

    /**
     * Получение списка емайлов для установки администратором в ЛК
     */
    public function getAdminEmails()
    {
        $account = $this->getFirstAccount();

        if (!$account) {
            return;
        }

        return $account
            ->getContacts()
            ->where([
                'type' => ClientContact::TYPE_EMAIL,
                'is_official' => 1,
                'is_validate' => 1
            ])
            ->indexBy('id')
            ->select(['id', 'data'])
            ->all();
    }

    public function getCountry()
    {
        /** @var ClientAccount $account */
        $account = $this->getAccounts()->one();

        $countryId = $account->getUuCountryId();

        if ($countryId) {
            return Country::find()->where(['code' => $countryId])->one();
        }

        $country = new Country();
        $country->name = '<не установленно>';

        return $country;
    }
}
